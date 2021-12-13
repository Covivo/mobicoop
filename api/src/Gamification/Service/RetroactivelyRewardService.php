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
use App\Gamification\Repository\RewardStepRepository;
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
    const SIMPLE_GAMIFICATION_ACTION = [1,2,3,4,5,6,7,20,21,22];
    const GAMIFICATION_ACTION_WITH_PROPOSAL = [8,17,10,23, ];
    const GAMIFICATION_ACTION_WITH_ASK = [9,12];
    const GAMIFICATION_ACTION_WITH_MESSAGE = [14];
    const GAMIFICATION_ACTION_WITH_USER = [13];
    const GAMIFICATION_ACTION_WITH_CARPOOLITEM = [19];


    private $userRepository;
    private $badgeRepository;
    private $rewardStepRepository;
    private $badgesBoardManager;
    private $sequenceItemRepository;
    private $entityManager;
    private $logger;


    public function __construct(
        UserRepository $userRepository,
        BadgeRepository $badgeRepository,
        RewardStepRepository $rewardStepRepository,
        BadgesBoardManager $badgesBoardManager,
        SequenceItemRepository $sequenceItemRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->badgeRepository = $badgeRepository;
        $this->rewardStepRepository = $rewardStepRepository;
        $this->badgesBoardManager = $badgesBoardManager;
        $this->sequenceItemRepository = $sequenceItemRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function retroactivelyRewardUsers()
    {
        $this->logger->info("start retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        $limit = 20;
        $limit = 10000;
        // $limit = 100000;
        $stmt = $this->entityManager->getConnection()->prepare(
            "select u.id, reward_step.sequence_item_id, u.validated_date, u.phone_validated_date, u.telephone, count(i.id) as nb_images, count(e.id) as nb_events, count(cu.id) as nb_community_users, count(c.id) as nb_communities, count(a.id) as nb_asks, count(p.id) as nb_proposals
            from user u
            left join reward_step on reward_step.user_id = u.id
            left join image i on i.user_id = u.id 
            left join event e on e.user_id = u.id
            left join community_user cu on cu.user_id = u.id
            left join community c on c.user_id = u.id
            left join proposal p on p.user_id = u.id and p.private=0
            left join ask a on a.user_id = u.id or a.user_related_id = u.id
            group by u.id, reward_step.sequence_item_id, u.validated_date, u.phone_validated_date, u.telephone
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
                        'sequence_item_id' => $user['sequence_item_id'],
                        'validated_date' => $user['validated_date'],
                        'phone_validated_date' => $user['phone_validated_date'],
                        'telephone' => $user['telephone'],
                        'nb_images' => $user['nb_images'],
                        'nb_events' => $user['nb_events'],
                        'nb_community_users' => $user['nb_community_users'],
                        'nb_communities' => $user['nb_communities'],
                        'nb_asks' => $user['nb_asks'],
                        'nb_proposals' => $user['nb_proposals']
                    ]
                );
            } else {
                $users[$user['id']] = [
                        [
                            'user_id' => $user['id'],
                            'sequence_item_id' => $user['sequence_item_id'],
                            'validated_date' => $user['validated_date'],
                            'phone_validated_date' => $user['phone_validated_date'],
                            'telephone' => $user['telephone'],
                            'nb_images' => $user['nb_images'],
                            'nb_events' => $user['nb_events'],
                            'nb_community_users' => $user['nb_community_users'],
                            'nb_communities' => $user['nb_communities'],
                            'nb_asks' => $user['nb_asks'],
                            'nb_proposals' => $user['nb_proposals']
                        ]    
                ];
            }
        }
        
        $this->logger->info("end retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        var_dump(count($users));die;
       
        $stmt = $this->entityManager->getConnection()->prepare(
            "select b.id, si.id as sequence_item_id, ga.id as gamification_action_id, gar.name as rule_name
            from badge b 
            left join sequence_item si on b.id = si.badge_id
            left join gamification_action ga on ga.id = si.gamification_action_id
            left join gamification_action_rule gar on gar.id = ga.gamification_action_rule_id;"
        );
        $stmt->execute();
        $resultBadges = $stmt->fetchAll();

        $badges = [];
        foreach ($resultBadges as $badge) {
            if (array_key_exists($badge['id'], $badges)) {
                array_push(
                    $badges[$badge['id']], 
                    [
                        'si_id' => $badge['sequence_item_id'],
                        'ga_id' => $badge['gamification_action_id'],
                        'rule_name' => $badge['rule_name']
                    ]
                );
            } else {
                $badges[$badge['id']] = [
                        [
                            'si_id' => $badge['sequence_item_id'],
                            'ga_id' => $badge['gamification_action_id'],
                            'rule_name' => $badge['rule_name']
                        ]    
                ];
            }
        }

        foreach ($users as $user) {
            $this->retroactivelyRewardUser($user, $sequenceItemsIds, $badges);
        }
        $this->logger->info("end retroactivelyRewardUsers | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        return count($users);




        // $count = 0;
        // foreach ($this->userRepository->findAll() as $user) {
        //     $this->retroactivelyRewardUser($user);
        //     $count++;
        // }
        //return $count;
    }

    private function retroactivelyRewardUser(int $id, array $sequenceItemsIds, array $badges)
    {
        // $this->logger->info("start retroactivelyRewardUser | " . $id . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        foreach ($badges as $badge) {
            // $this->logger->info("checkbadge | " . $badge->getId() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

            foreach ($badge as $sequenceItem) {
                // $this->logger->info("checkSequenceItem | " . $sequenceItem->getId() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

                if (in_array($sequenceItem['si_id'], $sequenceItemsIds)) continue;

                $method = Self::GAMIFICATION_ACTION_DONE[$sequenceItem['ga_id']];

                // faire un tableau avec les id de action ne necessitant pas le user complet si dans tableau exécuter 
                if (in_array($sequenceItem['ga_id'], self::SIMPLE_GAMIFICATION_ACTION)) {
                    if ($this->$method($user, $sequenceItem)) {
                            $this->handleRetroactivelyRewards($user, $sequenceItem->getId());
                    }
                } elseif(in_array($sequenceItem['ga_id'], self::GAMIFICATION_ACTION_WITH_PROPOSAL)) {
                    if ($user['nb_proposal'] == 0) continue;
                    $user = $this->userRepository->find($user['user_id']);
                    if ($this->$method($user, $sequenceItem)) {
                        $this->handleRetroactivelyRewards($user, $sequenceItem->getId());
                    }
                }
                
                
                
                // if ($this->$method($user, $sequenceItem)) {
                //     $this->handleRetroactivelyRewards($user, $sequenceItem->getId());
                // }
            }
        }
    }

    // public function hasAlreadyRewardStep(int $id, SequenceItem $sequenceItem)
    // {
    //     return $this->entityManager->getConnection()
    //     ->fetchColumn('SELECT id from reward_step WHERE sequence_item_id = ' . $sequenceItem->getId() . ' AND user_id = ' . $id);
    //     // if (count($this->rewardStepRepository->findRewardStepByUserAndSequenceItem($id, $sequenceItem)) >= 1) {
    //         // return true;
    //     // }
    //     // return false;
    // }
   
    public function handleRetroactivelyRewards(User $user, int $sequenceItemId)
    {
        $user = new User;
        $user->setId($user["user_id"]);

        $validationStep = new ValidationStep;
        $validationStep->setSequenceItem($this->sequenceItemRepository->find($sequenceItemId));
        $validationStep->setUser($user);
        $badgesBoard = $this->badgesBoardManager->getBadgesBoard($user);
        foreach ($badgesBoard->getBadges() as $badgeProgression) {
            $badgeSummary = $badgeProgression->getBadgeSummary();
            $currentSequenceValidation = []; // We will store the status of every SequenceItem
            $newValidation = false;
            foreach ($badgeSummary->getSequences() as $sequenceStatus) {
                // We found the right sequence
                if ($sequenceStatus->getSequenceItemId() == $validationStep->getSequenceItem()->getId()) {
                    // If it's a new validation, We store it be inserting a line in RewardStep for the User
                    if (!$sequenceStatus->isValidated()) {
                        $newValidation = true;
                        $rewardStep = new RewardStep();
                        $rewardStep->setUser($validationStep->getUser());
                        $rewardStep->setCreatedDate(new \DateTime('now'));
                        $rewardStep->setNotifiedDate(new \DateTime('now'));
                        $validationStep->getSequenceItem()->addRewardStep($rewardStep);
                        $this->entityManager->persist($validationStep->getSequenceItem());
                        // We also update the current SequenceStatus to evaluate further it this is enough to earn badge
                        $sequenceStatus->setValidated(true);
                    }
                }
                // We store the status of the current SequenceItem. If all validated, maybe the user earned a Badge
                $currentSequenceValidation[] = $sequenceStatus->isValidated();
            }
            if (!in_array(false, $currentSequenceValidation)) {
                // All steps are valid !
                if ($newValidation) {
                    // There was a new validation, a new Badge is earned !
                    // We get the badge involved and add a User owning this Badge (add a line in Reward table)
                    $badge = $this->badgeRepository->find($badgeSummary->getBadgeId());
                    $reward = new Reward();
                    $reward->setCreatedDate(new \DateTime('now'));
                    $reward->setNotifiedDate(new \DateTime('now'));
                    $reward->setUser($validationStep->getUser());
                    $badge->addReward($reward);
                    $this->entityManager->persist($badge);
                }
            }
        }
        $this->entityManager->flush();
    }

    public function checkRule(User $user, SequenceItem $sequenceItem)
    {
        $gamificationActionRuleName = "\\App\\Gamification\Rule\\" . $sequenceItem->getGamificationAction()->getGamificationActionRule()->getName();
        /**
         * @var GamificationRuleInterface $gamificationActionRule
         */
        $gamificationActionRule = new $gamificationActionRuleName;
        $log = new Log;
        $log->setUser($user);
        return $gamificationActionRule->execute($log, $sequenceItem);
    }

    private function hasEmailValidated(User $user, SequenceItem $sequenceItem)
    {
        if (!is_null($user->getValidatedDate())) {
            return true;
        }
        return false;
    }

    private function hasPhoneValidated(User $user, SequenceItem $sequenceItem)
    {
        if (!is_null($user->getPhoneValidatedDate())) {
            return true;
        }
        return false;
    }

    private function hasAvatar(User $user, SequenceItem $sequenceItem)
    {
        if (!is_null($user->getImages()) && count($user->getImages()) > 0) {
            return true;
        }
        return false;
    }

    private function hasHomeAddress(User $user, SequenceItem $sequenceItem)
    {
        $hasHomeAddress = false;
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $hasHomeAddress = true;
            }
        }
        return $hasHomeAddress;
    }

    private function hasPublishedAnAd(User $user, SequenceItem $sequenceItem)
    {
        $nbAds = 0;
        foreach ($user->getProposals() as $proposal) {
            if (!$proposal->isPrivate()) {
                $nbAds++;
            }
        }
        return ($nbAds >= 1);
    }

    private function hasNpublishedAds(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasJoinedCommunity(User $user, SequenceItem $sequenceItem)
    {
        return (count($user->getCommunityUsers()) >= 1);
    }

    private function hasPublishedAnAdInCommunity(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasAnAcceptedCarpool(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasAnAcceptedCarpoolInCommunity(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasPublishedASolidaryExclusiveAd(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasCarpooledNkm(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasSavedNkgOfCO2(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasAnsweredAMessage(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasPublishAnAdWithRelayPoint(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasPublishedAnAdInEvent(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasValidatedBankIdentity(User $user, SequenceItem $sequenceItem)
    {
        return (!is_null($user->getPaymentProfileId()));
    }

    private function hasRealizedAnOnlinePayment(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }

    private function hasPhoneNumber(User $user, SequenceItem $sequenceItem)
    {
        return (!is_null($user->getTelephone()));
    }

    private function hasCreatedAnEvent(User $user, SequenceItem $sequenceItem)
    {
        return (count($user->getEvents()) >= 1);
    }

    private function hasCreatedACommunity(User $user, SequenceItem $sequenceItem)
    {
        return (count($user->getCommunities()) >= 1);
    }

    private function hasAnAcceptedCarpoolInEvent(User $user, SequenceItem $sequenceItem)
    {
        return $this->checkRule($user, $sequenceItem);
    }
}
