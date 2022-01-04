<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Gamification\Service;

use App\Action\Entity\Log;
use App\Gamification\Entity\Reward;
use App\Gamification\Entity\RewardStep;
use App\Gamification\Entity\SequenceItem;
use App\Gamification\Entity\ValidationStep;
use App\User\Repository\UserRepository;
use App\Gamification\Repository\BadgeRepository;
use App\User\Entity\User;
use App\Gamification\Service\BadgesBoardManager;
use App\Gamification\Repository\SequenceItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class RetroactivelyRewardService
{
    const GAMIFICATION_ACTION_DONE = [
        1 => "hasEmailValidated",
        2 => "hasPhoneValidated",
        3 => "hasAvatar",
        4 => "hasHomeAddress",
        5 => "hasPublishedAnAd",
        6 => "hasNpublishedAds",
        7 => "hasJoinedCommunity",
        8 => "hasPublishedAnAdInCommunity",
        9 =>  "hasAnAcceptedCarpool",
        10 => "hasAnAcceptedCarpoolInCommunity",
        11 => "hasPublishedASolidaryExclusiveAd",
        12 => "hasCarpooledNkm",
        13 => "hasSavedNkgOfCO2",
        14 => "hasAnsweredAMessage",
        15 => "hasRepublishedAnExpiredAd",
        16 => "hasPublishAnAdWithRelayPoint",
        17 => "hasPublishedAnAdInEvent",
        18 => "hasValidatedBankIdentity",
        19 => "hasRealizedAnOnlinePayment",
        20 => "hasPhoneNumber",
        21 => "hasCreatedAnEvent",
        22 => "hasCreatedACommunity",
        23 => "hasAnAcceptedCarpoolInEvent"
    ];

    private $userRepository;
    private $badgeRepository;
    private $badgesBoardManager;
    private $sequenceItemRepository;
    private $entityManager;
    private $logger;


    public function __construct(
        UserRepository $userRepository,
        BadgeRepository $badgeRepository,
        BadgesBoardManager $badgesBoardManager,
        SequenceItemRepository $sequenceItemRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->badgeRepository = $badgeRepository;
        $this->badgesBoardManager = $badgesBoardManager;
        $this->sequenceItemRepository = $sequenceItemRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function retroactivelyRewardUsers()
    {
        $this->logger->info("start retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        $limit = 10;
        // $limit = 100;
        // $limit = 1000;
        // $limit = 10000;
        // $limit = 100000;
        // $limit = 1000000;
        $this->entityManager->getConnection()->prepare('
        CREATE TEMPORARY TABLE tuser (
            id int not null,
            validated_date datetime, 
            phone_validated_date datetime, 
            telephone varchar(255), 
            nb_images int not null default 0, 
            nb_events int not null default 0, 
            nb_community_users int not null default 0, 
            nb_communities int not null default 0, 
            nb_asks int not null default 0, 
            nb_asks_related int not null default 0, 
            nb_asks_community int not null default 0, 
            nb_asks_related_community int not null default 0, 
            nb_asks_event int not null default 0, 
            nb_asks_related_event int not null default 0, 
            nb_proposals int not null default 0,
            nb_proposals_community int not null default 0,
            nb_proposals_event int not null default 0,
            nb_proposals_solidary_exclusive int not null default 0,
            nb_carpool_items int not null default 0, 
            nb_addresses int not null default 0, 
            nb_payment_profiles int not null default 0, 
            nb_messages int not null default 0
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            INSERT INTO tuser (id, validated_date, phone_validated_date, telephone)
            (select u.id, u.validated_date, u.phone_validated_date, u.telephone from user u);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_images = (select count(distinct i.id) from image i where i.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_events = (select count(distinct e.id) from event e where e.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_communities = (select count(distinct c.id) from community c where c.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_community_users = (select count(distinct cu.id) from community_user cu where cu.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals = (select count(distinct p.id) from proposal p where p.user_id = t.id and p.private = 0);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_community = (select count(distinct p.id) FROM proposal p join proposal_community pc on p.id = pc.proposal_id where p.private = 0 and p.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_event = (select count(distinct p.id) from proposal p where p.user_id = t.id and p.private = 0 and p.event_id is not null);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_solidary_exclusive = (select count(distinct p.id) from proposal p join criteria c on p.criteria_id = c.id where p.user_id = t.id and p.private = 0 and c.solidary_exclusive = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks = (select count(distinct a.id) from ask a where a.user_id = t.id and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related = (select count(distinct a.id) from ask a where a.user_related_id = t.id and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_community = (select count(distinct a.id) from ask a join matching m on a.matching_id = m.id join proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id where a.user_id = t.id and p.event_id is not null and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related_community = (select count(distinct a.id) from ask a join matching m on a.matching_id = m.id join proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id where a.user_related_id = t.id and p.event_id is not null and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_event = (select count(distinct a.id) from ask a join matching m on a.matching_id = m.id join proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id where a.user_id = t.id and p.event_id is not null and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related_event = (select count(distinct a.id) from ask a join matching m on a.matching_id = m.id join proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id where a.user_related_id = t.id and p.event_id is not null and a.status in (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_carpool_items = (select count(distinct ci.id) from carpool_item ci where ci.debtor_user_id = t.id and ci.debtor_status = 3);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_addresses = (select count(distinct a.id) from address a where a.user_id = t.id and a.home = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_payment_profiles = (select count(distinct pp.id) from payment_profile pp where pp.user_id = t.id and pp.validation_status = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_messages = (select count(distinct m.id) from message m where m.user_id = t.id);
        );')->execute();

        $stmt = $this->entityManager->getConnection()->prepare(
            "select * from tuser
            limit $limit;"
        );
        $stmt->execute();
        $resultsUsers = $stmt->fetchAll();
        $users = [];
        foreach ($resultsUsers as $user) {
            if (array_key_exists($user['id'], $users)) {
                array_push(
                    $users[$user['id']],
                    [
                        'user_id' => $user['id'],
                        'validated_date' => $user['validated_date'],
                        'phone_validated_date' => $user['phone_validated_date'],
                        'telephone' => $user['telephone'],
                        'nb_images' => $user['nb_images'],
                        'nb_events' => $user['nb_events'],
                        'nb_community_users' => $user['nb_community_users'],
                        'nb_communities' => $user['nb_communities'],
                        'nb_asks' => $user['nb_asks'],
                        'nb_asks_related' => $user['nb_asks_related'],
                        'nb_asks_community' => $user['nb_asks_community'],
                        'nb_asks_related_community' => $user['nb_asks_related_community'],
                        'nb_asks_event' => $user['nb_asks_event'],
                        'nb_asks_related_event' => $user['nb_asks_related_event'],
                        'nb_proposals' => $user['nb_proposals'],
                        'nb_proposals_community' => $user['nb_proposals_community'],
                        'nb_proposals_event' => $user['nb_proposals_event'],
                        'nb_proposals_solidary_exclusive' => $user['nb_proposals_solidary_exclusive'],
                        'nb_carpool_items' => $user['nb_carpool_items'],
                        'nb_address' => $user['nb_address'],
                        'nb_payment_profiles' => $user['nb_payment_profiles'],
                        'nb_messages' => $user['nb_messages'],
                        'sequence_item_ids' => [],
                        'badge_ids' => []

                    ]
                );
            } else {
                $users[$user['id']] = [
                        [
                            'user_id' => $user['id'],
                            'validated_date' => $user['validated_date'],
                            'phone_validated_date' => $user['phone_validated_date'],
                            'telephone' => $user['telephone'],
                            'nb_images' => $user['nb_images'],
                            'nb_events' => $user['nb_events'],
                            'nb_community_users' => $user['nb_community_users'],
                            'nb_communities' => $user['nb_communities'],
                            'nb_asks' => $user['nb_asks'],
                            'nb_asks_related' => $user['nb_asks_related'],
                            'nb_asks_community' => $user['nb_asks_community'],
                            'nb_asks_related_community' => $user['nb_asks_related_community'],
                            'nb_asks_event' => $user['nb_asks_event'],
                            'nb_asks_related_event' => $user['nb_asks_related_event'],
                            'nb_proposals' => $user['nb_proposals'],
                            'nb_proposals_community' => $user['nb_proposals_community'],
                            'nb_proposals_event' => $user['nb_proposals_event'],
                            'nb_proposals_solidary_exclusive' => $user['nb_proposals_solidary_exclusive'],
                            'nb_carpool_items' => $user['nb_carpool_items'],
                            'nb_addresses' => $user['nb_addresses'],
                            'nb_payment_profiles' => $user['nb_payment_profiles'],
                            'nb_messages' => $user['nb_messages'],
                            'sequence_item_ids' => [],
                            'badge_ids' => []

                        ]
                ];
            }
        }

        $stmt = $this->entityManager->getConnection()->prepare(
            "select reward_step.user_id, reward_step.sequence_item_id
            from reward_step  
            order by `reward_step`.`user_id` asc;"
        );
        $stmt->execute();
        $sequenceItems = $stmt->fetchAll();
        foreach ($sequenceItems as $sequenceItem) {
            if (array_key_exists($sequenceItem['user_id'], $users)) {
                array_push($users[$sequenceItem['user_id']][0]['sequence_item_ids'], $sequenceItem['sequence_item_id']);
            }
        }

        $stmt = $this->entityManager->getConnection()->prepare(
            "select reward.user_id, reward.badge_id
            from reward  
            order by `reward`.`user_id` asc;"
        );
        $stmt->execute();
        $badges = $stmt->fetchAll();
        foreach ($badges as $badge) {
            if (array_key_exists($badge['user_id'], $users)) {
                array_push($users[$badge['user_id']][0]['badge_ids'], $badge['badge_id']);
            }
        }
     
        // $this->logger->info("end retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // // var_dump($users);
        // die;

        $stmt = $this->entityManager->getConnection()->prepare(
            "select b.id as badge_id, si.id as sequence_item_id, ga.id as gamification_action_id, gar.name as rule_name, si.min_count, si.min_unique_count, si.in_date_range, si.value
            from badge b 
            left join sequence_item si on b.id = si.badge_id
            left join gamification_action ga on ga.id = si.gamification_action_id
            left join gamification_action_rule gar on gar.id = ga.gamification_action_rule_id;"
        );
        $stmt->execute();
        $resultBadges = $stmt->fetchAll();

        $badges = [];
        foreach ($resultBadges as $badge) {
            if (array_key_exists($badge['badge_id'], $badges)) {
                array_push(
                    $badges[$badge['badge_id']],
                    [
                        'si_id' => $badge['sequence_item_id'],
                        'ga_id' => $badge['gamification_action_id'],
                        'rule_name' => $badge['rule_name'],
                        'si_min_count' => $badge['min_count'],
                        'si_min_unique_count' => $badge['min_unique_count'],
                        'si_in_date_range' => $badge['in_date_range'],
                        'si_value' => $badge['value']
                    ]
                );
            } else {
                $badges[$badge['badge_id']] = [
                        [
                            'si_id' => $badge['sequence_item_id'],
                            'ga_id' => $badge['gamification_action_id'],
                            'rule_name' => $badge['rule_name'],
                            'si_min_count' => $badge['min_count'],
                            'si_min_unique_count' => $badge['min_unique_count'],
                            'si_in_date_range' => $badge['in_date_range'],
                            'si_value' => $badge['value']
                        ]
                ];
            }
        }
        
        foreach ($users as $user) {
            $sequenceItemsIds = (!is_null($user[0]['sequence_item_ids'])) ?  $user[0]['sequence_item_ids'] : [];
            $badgeIds = (!is_null($user[0]['badge_ids'])) ?  $user[0]['badge_ids'] : [];
            $this->retroactivelyRewardUser($user, $sequenceItemsIds, $badgeIds, $badges);
        }
        $this->logger->info("end retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    private function retroactivelyRewardUser(array $user, ?array $sequenceItemsIds, ?array $badgeIds, array $badges)
    {
        foreach ($badges as $key => $badge) {
            if (in_array($key, $badgeIds)) {
                continue;
            }
            foreach ($badge as $sequenceItem) {
                if (in_array($sequenceItem['si_id'], $sequenceItemsIds)) {
                    continue;
                }
                $method = Self::GAMIFICATION_ACTION_DONE[$sequenceItem['ga_id']];
                if ($this->$method($user, $sequenceItem)) {
                    continue;
                    // $this->handleRetroactivelyRewards($user, $sequenceItem['si_id']);
                }
                continue;
            }
        }
    }
   
    // public function handleRetroactivelyRewards($userToUse, int $sequenceItemId)
    // {
    //     if (!($userToUse instanceof User)) {
    //         $user = new User;
    //         $user->setId($userToUse[0]["user_id"]);
    //     // $user = $this->userRepository->find($userToUse[0]["user_id"]);
    //     } else {
    //         $user = $userToUse;
    //     }

    //     $sequenceItem = $this->sequenceItemRepository->find($sequenceItemId);
    //     $badgesBoard = $this->badgesBoardManager->getBadgesBoard($user);

    //     foreach ($badgesBoard->getBadges() as $badgeProgression) {
    //         $badgeSummary = $badgeProgression->getBadgeSummary();
    //         $currentSequenceValidation = []; // We will store the status of every SequenceItem
    //         $newValidation = false;
    //         foreach ($badgeSummary->getSequences() as $sequenceStatus) {
    //             // We found the right sequence
    //             if ($sequenceStatus->getSequenceItemId() == $sequenceItem->getId()) {
    //                 // If it's a new validation, We store it be inserting a line in RewardStep for the User
    //                 if (!$sequenceStatus->isValidated()) {
    //                     $newValidation = true;
    //                     $rewardStep = new RewardStep();
    //                     $rewardStep->setUser($user);
    //                     $rewardStep->setCreatedDate(new \DateTime('now'));
    //                     $rewardStep->setNotifiedDate(new \DateTime('now'));
    //                     $sequenceItem->addRewardStep($rewardStep);
    //                     $this->entityManager->persist($sequenceItem);
    //                     // We also update the current SequenceStatus to evaluate further it this is enough to earn badge
    //                     $sequenceStatus->setValidated(true);
    //                 }
    //             }
    //             // We store the status of the current SequenceItem. If all validated, maybe the user earned a Badge
    //             $currentSequenceValidation[] = $sequenceStatus->isValidated();
    //         }
    //         if (!in_array(false, $currentSequenceValidation)) {
    //             // All steps are valid !
    //             if ($newValidation) {
    //                 // There was a new validation, a new Badge is earned !
    //                 // We get the badge involved and add a User owning this Badge (add a line in Reward table)
    //                 $badge = $this->badgeRepository->find($badgeSummary->getBadgeId());
    //                 $reward = new Reward();
    //                 $reward->setCreatedDate(new \DateTime('now'));
    //                 $reward->setNotifiedDate(new \DateTime('now'));
    //                 $reward->setUser($user);
    //                 $badge->addReward($reward);
    //                 $this->entityManager->persist($badge);
    //             }
    //         }
    //     }
    //     $this->entityManager->flush();
    // }

    // public function checkRule(User $user, $sequenceItem)
    // {
    //     $gamificationActionRuleName = "\\App\\Gamification\Rule\\" . $sequenceItem['rule_name'];
    //     /**
    //      * @var GamificationRuleInterface $gamificationActionRule
    //      */
    //     $gamificationActionRule = new $gamificationActionRuleName;
    //     $log = new Log;
    //     $log->setUser($user);
    //     return $gamificationActionRule->execute($log, $sequenceItem);
    // }

    private function hasEmailValidated($user, $sequenceItem)
    {
        return (!is_null($user[0]["validated_date"]));
    }

    private function hasPhoneValidated($user, $sequenceItem)
    {
        return (!is_null($user[0]["phone_validated_date"]));
    }

    private function hasAvatar($user, $sequenceItem)
    {
        return ($user[0]["nb_images"] > 0);
    }

    private function hasHomeAddress($user, $sequenceItem)
    {
        return ($user[0]["nb_addresses"] > 0);
    }

    private function hasPublishedAnAd($user, $sequenceItem)
    {
        return ($user[0]["nb_proposals"] >= 1);
    }

    private function hasNpublishedAds($user, $sequenceItem)
    {
        return ($user[0]["nb_proposals"] >= $sequenceItem["si_min_count"]);
    }

    private function hasJoinedCommunity($user, $sequenceItem)
    {
        return $user[0]["nb_community_users"] >= 1;
    }

    private function hasPublishedAnAdInCommunity($user, $sequenceItem)
    {
        return ($user[0]["nb_proposals_community"] >= 1);
    }

    private function hasAnAcceptedCarpool($user, $sequenceItem)
    {
        return ($user[0]["nb_asks"] >= 1 || $user[0]["nb_asks_related"] >= 1);
    }

    private function hasAnAcceptedCarpoolInCommunity($user, $sequenceItem)
    {
        return ($user[0]["nb_asks_community"] >= 1 || $user[0]["nb_asks_related_community"] >= 1);
    }

    private function hasPublishedASolidaryExclusiveAd($user, $sequenceItem)
    {
        return ($user[0]["nb_proposals_solidary_exclusive"] >= 1);
    }

    private function hasCarpooledNkm($user, $sequenceItem)
    {
        // return $this->checkRule($user, $sequenceItem);
        return false;
    }

    private function hasSavedNkgOfCO2($user, $sequenceItem)
    {
        // return $this->checkRule($user, $sequenceItem);
        return false;
    }

    private function hasAnsweredAMessage($user, $sequenceItem)
    {
        return ($user[0]["nb_messages"] >= 1);
    }

    private function hasPublishAnAdWithRelayPoint($user, $sequenceItem)
    {
        return false;
    }

    private function hasPublishedAnAdInEvent($user, $sequenceItem)
    {
        return ($user[0]["nb_proposals_event"] >= 1);
    }

    private function hasValidatedBankIdentity($user, $sequenceItem)
    {
        return ($user[0]["nb_payment_profiles"] >= 1);
    }

    private function hasRealizedAnOnlinePayment($user, $sequenceItem)
    {
        return ($user[0]["nb_carpool_items"] >= 1);
    }

    private function hasPhoneNumber($user, $sequenceItem)
    {
        return (!is_null($user[0]["telephone"]));
    }

    private function hasCreatedAnEvent($user, $sequenceItem)
    {
        return ($user[0]["nb_events"] >= 1);
    }

    private function hasCreatedACommunity($user, $sequenceItem)
    {
        return ($user[0]["nb_communities"] >= 1);
    }

    private function hasAnAcceptedCarpoolInEvent($user, $sequenceItem)
    {
        return ($user[0]["nb_asks_event"] >= 1 || $user[0]["nb_asks_related_event"] >= 1);
    }
}
