<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL AND proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it AND/or modify
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
 */

namespace App\Gamification\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class RetroactivelyRewardService
{
    public const GAMIFICATION_ACTION_DONE = [
        1 => 'hasEmailValidated',
        2 => 'hasPhoneValidated',
        3 => 'hasAvatar',
        4 => 'hasHomeAddress',
        5 => 'hasPublishedAnAd',
        6 => 'hasNpublishedAds',
        7 => 'hasJoinedCommunity',
        8 => 'hasPublishedAnAdInCommunity',
        9 => 'hasAnAcceptedCarpool',
        10 => 'hasAnAcceptedCarpoolInCommunity',
        11 => 'hasPublishedASolidaryExclusiveAd',
        12 => 'hasCarpooledNkm',
        13 => 'hasSavedNkgOfCO2',
        14 => 'hasAnsweredAMessage',
        15 => 'hasRepublishedAnExpiredAd',
        16 => 'hasPublishAnAdWithRelayPoint',
        17 => 'hasPublishedAnAdInEvent',
        18 => 'hasValidatedBankIdentity',
        19 => 'hasRealizedAnOnlinePayment',
        20 => 'hasPhoneNumber',
        21 => 'hasCreatedAnEvent',
        22 => 'hasCreatedACommunity',
        23 => 'hasAnAcceptedCarpoolInEvent',
    ];

    public const BADGES = [1, 2, 3, 4, 5, 6, 7];

    private $entityManager;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function retroactivelyRewardUsers()
    {
        $this->logger->info('start retroactivelyRewardUsers | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // get all users with all needed informations
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
            nb_messages int not null default 0,
            nb_km_carpooled int default 0,
            nb_km_carpooled_related int default 0
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            INSERT INTO tuser (id, validated_date, phone_validated_date, telephone)
            (SELECT u.id, u.validated_date, u.phone_validated_date, u.telephone FROM user u);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_images = (SELECT COUNT(distinct i.id) FROM image i 
            WHERE i.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_events = (SELECT COUNT(distinct e.id) FROM event e 
            WHERE e.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_communities = (SELECT COUNT(distinct c.id) FROM community c WHERE c.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_community_users = (SELECT COUNT(distinct cu.id) FROM community_user cu 
            WHERE cu.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals = (SELECT COUNT(distinct p.id) FROM proposal p 
            WHERE p.user_id = t.id AND p.private = 0);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_community = (SELECT COUNT(distinct p.id) FROM proposal p 
            INNER JOIN proposal_community pc on p.id = pc.proposal_id 
            WHERE p.private = 0 AND p.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_event = (SELECT COUNT(distinct p.id) FROM proposal p 
            WHERE p.user_id = t.id AND p.private = 0 AND p.event_id is not null);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_proposals_solidary_exclusive = (SELECT COUNT(distinct p.id) FROM proposal p 
            INNER JOIN criteria c on p.criteria_id = c.id 
            WHERE p.user_id = t.id AND p.private = 0 AND c.solidary_exclusive = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks = (SELECT COUNT(distinct a.id) FROM ask a 
            WHERE a.user_id = t.id AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related = (SELECT COUNT(distinct a.id) FROM ask a 
            WHERE a.user_related_id = t.id AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_community = (SELECT COUNT(distinct a.id) FROM ask a 
            INNER JOIN matching m on a.matching_id = m.id JOIN proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id 
            WHERE a.user_id = t.id AND p.event_id is not null AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related_community = (SELECT COUNT(distinct a.id) FROM ask a 
            INNER JOIN matching m on a.matching_id = m.id JOIN proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id 
            WHERE a.user_related_id = t.id AND p.event_id is not null AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_event = (SELECT COUNT(distinct a.id) FROM ask a 
            INNER JOIN matching m on a.matching_id = m.id 
            INNER JOIN proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id 
            WHERE a.user_id = t.id AND p.event_id is not null AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_asks_related_event = (SELECT COUNT(distinct a.id) FROM ask a 
            INNER JOIN matching m on a.matching_id = m.id JOIN proposal p on m.proposal_offer_id = p.id or m.proposal_request_id = p.id 
            WHERE a.user_related_id = t.id AND p.event_id is not null AND a.status IN (4,5));
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_carpool_items = (SELECT COUNT(distinct ci.id) FROM carpool_item ci 
            WHERE ci.debtor_user_id = t.id AND ci.debtor_status = 3);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_addresses = (SELECT COUNT(distinct a.id) FROM address a 
            WHERE a.user_id = t.id AND a.home = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_payment_profiles = (SELECT COUNT(distinct pp.id) FROM payment_profile pp 
            WHERE pp.user_id = t.id AND pp.validation_status = 1);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_messages = (SELECT COUNT(distinct m.id) FROM message m 
            WHERE m.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_km_carpooled = (SELECT SUM(m.common_distance)/1000 FROM carpool_item ci
            INNER JOIN ask a on a.id = ci.ask_id
            INNER JOIN matching m on m.id = a.matching_id
            WHERE ci.item_status = 1 AND a.user_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare('
            UPDATE tuser t
            SET t.nb_km_carpooled_related = (SELECT SUM(m.common_distance)/1000 FROM carpool_item ci
            INNER JOIN ask a on a.id = ci.ask_id
            INNER JOIN matching m on m.id = a.matching_id
            WHERE ci.item_status = 1 AND a.user_related_id = t.id);
        );')->execute();
        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT * FROM tuser;'
        );
        $stmt->execute();
        $resultsUsers = $stmt->fetchAll();

        // format users
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
                        'nb_km_carpooled' => $user['nb_km_carpooled'],
                        'nb_km_carpooled_related' => $user['nb_km_carpooled_related'],
                        'sequence_item_ids' => [],
                        'badge_ids' => [],
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
                        'nb_km_carpooled' => $user['nb_km_carpooled'],
                        'nb_km_carpooled_related' => $user['nb_km_carpooled_related'],
                        'sequence_item_ids' => [],
                        'badge_ids' => [],
                    ],
                ];
            }
        }

        // get all sequences_items already earned of each user
        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT reward_step.user_id, reward_step.sequence_item_id
            FROM reward_step  
            ORDER BY `reward_step`.`user_id` ASC;'
        );
        $stmt->execute();
        $sequenceItems = $stmt->fetchAll();
        foreach ($sequenceItems as $sequenceItem) {
            if (array_key_exists($sequenceItem['user_id'], $users)) {
                array_push($users[$sequenceItem['user_id']][0]['sequence_item_ids'], $sequenceItem['sequence_item_id']);
            }
        }

        // get all badges  already earned of each user
        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT reward.user_id, reward.badge_id
            FROM reward  
            ORDER BY `reward`.`user_id` ASC;'
        );
        $stmt->execute();
        $badges = $stmt->fetchAll();
        foreach ($badges as $badge) {
            if (array_key_exists($badge['user_id'], $users)) {
                array_push($users[$badge['user_id']][0]['badge_ids'], $badge['badge_id']);
            }
        }

        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT b.id as badge_id, si.id as sequence_item_id, ga.id as gamification_action_id, gar.name as rule_name, si.min_count, si.min_unique_count, si.in_date_range, si.value
            FROM badge b 
            LEFT JOIN sequence_item si on b.id = si.badge_id
            LEFT JOIN gamification_action ga on ga.id = si.gamification_action_id
            LEFT JOIN gamification_action_rule gar on gar.id = ga.gamification_action_rule_id;'
        );
        $stmt->execute();
        $resultBadges = $stmt->fetchAll();

        // get badges and sequence_items infos
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
                        'si_value' => $badge['value'],
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
                        'si_value' => $badge['value'],
                    ],
                ];
            }
        }

        foreach ($users as $user) {
            $sequenceItemsIds = (!is_null($user[0]['sequence_item_ids'])) ? $user[0]['sequence_item_ids'] : [];
            $badgeIds = (!is_null($user[0]['badge_ids'])) ? $user[0]['badge_ids'] : [];

            $newSequenceItems = $this->checkSequenceItemsEarned($user, $sequenceItemsIds, $badgeIds, $badges);
            if (count($newSequenceItems) > 0) {
                $this->persistRewardStep($newSequenceItems, $user[0]['user_id']);

                $sequenceItems = array_merge($sequenceItemsIds, $newSequenceItems);
                $this->persistReward($sequenceItems, $user[0]['user_id'], $badgeIds);
            }
        }
        $this->logger->info('end retroactivelyRewardUsers | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * add sequence_item newly earned.
     *
     * @param array      $user             concerned
     * @param null|array $sequenceItemsIds already earned sequence_items ids
     * @param null|array $badgeIds         already earned badges ids
     * @param array      $badges           badges
     *
     * @return array new sequence_items earned
     */
    private function checkSequenceItemsEarned(array $user, ?array $sequenceItemsIds, ?array $badgeIds, array $badges)
    {
        $newSequenceItemsIds = [];
        foreach ($badges as $id => $badge) {
            if (in_array($id, $badgeIds)) {
                continue;
            }
            foreach ($badge as $sequenceItem) {
                if (in_array($sequenceItem['si_id'], $sequenceItemsIds)) {
                    continue;
                }
                $method = self::GAMIFICATION_ACTION_DONE[$sequenceItem['ga_id']];
                if ($this->{$method}($user, $sequenceItem)) {
                    $newSequenceItemsIds[] = $sequenceItem['si_id'];
                }

                continue;
            }
        }

        return $newSequenceItemsIds;
    }

    /**
     * persist all earned reward_steps.
     *
     * @param null|array $sequenceItems sequence_items earned
     * @param int        $userId        id of the concerned user
     */
    private function persistRewardStep(?array $sequenceItems, int $userId)
    {
        $string = '';
        $date = (new \DateTime('now'))->format('Y-m-d');
        foreach ($sequenceItems as $sequenceItem) {
            $string = $string.'('.$sequenceItem.",'".$date."',".$userId.",'".$date."'),";
        }
        $string = substr($string, 0, -1);
        $this->entityManager->getConnection()->prepare('
            INSERT INTO reward_step (sequence_item_id,created_date,user_id,notified_date)
            VALUE '.$string.'
        ;')->execute();
    }

    /**
     * persist all earned rewards.
     *
     * @param null|array $sequenceItems all earned sequence_items ids
     * @param int        $userId        id of the concerned user
     * @param null|array $badgeIds      already earned badges ids
     */
    private function persistReward(?array $sequenceItems, int $userId, ?array $badgeIds)
    {
        $string = '';
        $date = (new \DateTime('now'))->format('Y-m-d');
        foreach (self::BADGES as $badgeId) {
            if (in_array($badgeId, $badgeIds)) {
                continue;
            }

            switch ($badgeId) {
                // badge 1 needed sequences 1,2,3,4 to be validated
                case 1:
                   if (in_array(1, $sequenceItems) && in_array(2, $sequenceItems) && in_array(3, $sequenceItems) && in_array(4, $sequenceItems)) {
                       $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                   }

                    break;
                // badge 2 needed sequence 5 to be validated
                case 2:
                    if (in_array(5, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                    break;
                // badge 3 needed sequences 6,7 to be validated
                case 3:
                    if (in_array(6, $sequenceItems) && in_array(7, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                    break;
                // badge 4 needed sequences 8,9,10 to be validated
                case 4:
                    if (in_array(8, $sequenceItems) && in_array(9, $sequenceItems) && in_array(10, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                break;
                // badge 5 needed sequence 11 to be validated
                case 5:
                    if (in_array(11, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                    break;
                // badge 6 needed sequence 12 to be validated
                case 6:
                    if (in_array(12, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                    break;
                // badge 7 needed sequence 13 to be validated
                case 7:
                    if (in_array(13, $sequenceItems)) {
                        $string = $string.'('.$badgeId.','.$userId.",'".$date."','".$date."'),";
                    }

                    break;
            }
        }
        if (strlen($string) > 0) {
            $string = substr($string, 0, -1);
            $this->entityManager->getConnection()->prepare('
                INSERT INTO reward (badge_id,user_id,created_date,notified_date)
                VALUE '.$string.'
            ;')->execute();
        }
    }

    private function hasEmailValidated($user, $sequenceItem)
    {
        return !is_null($user[0]['validated_date']);
    }

    private function hasPhoneValidated($user, $sequenceItem)
    {
        return !is_null($user[0]['phone_validated_date']);
    }

    private function hasAvatar($user, $sequenceItem)
    {
        return $user[0]['nb_images'] > 0;
    }

    private function hasHomeAddress($user, $sequenceItem)
    {
        return $user[0]['nb_addresses'] > 0;
    }

    private function hasPublishedAnAd($user, $sequenceItem)
    {
        return $user[0]['nb_proposals'] >= 1;
    }

    private function hasNpublishedAds($user, $sequenceItem)
    {
        return $user[0]['nb_proposals'] >= $sequenceItem['si_min_count'];
    }

    private function hasJoinedCommunity($user, $sequenceItem)
    {
        return $user[0]['nb_community_users'] >= 1;
    }

    private function hasPublishedAnAdInCommunity($user, $sequenceItem)
    {
        return $user[0]['nb_proposals_community'] >= 1;
    }

    private function hasAnAcceptedCarpool($user, $sequenceItem)
    {
        return $user[0]['nb_asks'] >= 1 || $user[0]['nb_asks_related'] >= 1;
    }

    private function hasAnAcceptedCarpoolInCommunity($user, $sequenceItem)
    {
        return $user[0]['nb_asks_community'] >= 1 || $user[0]['nb_asks_related_community'] >= 1;
    }

    private function hasPublishedASolidaryExclusiveAd($user, $sequenceItem)
    {
        return $user[0]['nb_proposals_solidary_exclusive'] >= 1;
    }

    private function hasCarpooledNkm($user, $sequenceItem)
    {
        $kmCarpool = 0;
        if (!is_null($user[0]['nb_km_carpooled'])) {
            $kmCarpool = $user[0]['nb_km_carpooled'];
        }
        if (!is_null($user[0]['nb_km_carpooled_related'])) {
            $kmCarpool += $user[0]['nb_km_carpooled'];
        }

        return $kmCarpool >= $sequenceItem['si_value'];
    }

    private function hasSavedNkgOfCO2($user, $sequenceItem)
    {
        return false;
    }

    private function hasAnsweredAMessage($user, $sequenceItem)
    {
        return $user[0]['nb_messages'] >= 1;
    }

    private function hasPublishAnAdWithRelayPoint($user, $sequenceItem)
    {
        return false;
    }

    private function hasPublishedAnAdInEvent($user, $sequenceItem)
    {
        return $user[0]['nb_proposals_event'] >= 1;
    }

    private function hasValidatedBankIdentity($user, $sequenceItem)
    {
        return $user[0]['nb_payment_profiles'] >= 1;
    }

    private function hasRealizedAnOnlinePayment($user, $sequenceItem)
    {
        return $user[0]['nb_carpool_items'] >= 1;
    }

    private function hasPhoneNumber($user, $sequenceItem)
    {
        return !is_null($user[0]['telephone']);
    }

    private function hasCreatedAnEvent($user, $sequenceItem)
    {
        return $user[0]['nb_events'] >= 1;
    }

    private function hasCreatedACommunity($user, $sequenceItem)
    {
        return $user[0]['nb_communities'] >= 1;
    }

    private function hasAnAcceptedCarpoolInEvent($user, $sequenceItem)
    {
        return $user[0]['nb_asks_event'] >= 1 || $user[0]['nb_asks_related_event'] >= 1;
    }
}
