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
    private $rewardStepRepository;
    private $badgesBoardManager;
    private $sequenceItemRepository;
    private $entityManager;


    public function __construct(
        UserRepository $userRepository,
        BadgeRepository $badgeRepository,
        RewardStepRepository $rewardStepRepository,
        BadgesBoardManager $badgesBoardManager,
        SequenceItemRepository $sequenceItemRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->badgeRepository = $badgeRepository;
        $this->rewardStepRepository = $rewardStepRepository;
        $this->badgesBoardManager = $badgesBoardManager;
        $this->sequenceItemRepository = $sequenceItemRepository;
        $this->entityManager = $entityManager;
    }

    public function retroactivelyRewardUsers()
    {
        $count = 0;
        foreach ($this->userRepository->findAll() as $user) {
            $this->retroactivelyRewardUser($user);
            $count++;
        }
        return $count;
    }

    private function retroactivelyRewardUser($user)
    {
        foreach ($this->badgeRepository->findAll() as $badge) {
            foreach ($badge->getSequenceItems() as $sequenceItem) {
                if ($this->hasAlreadyRewardStep($user, $sequenceItem)) {
                    continue;
                }
                $method = Self::GAMIFICATION_ACTION_DONE[$sequenceItem->getGamificationAction()->getId()];
                if ($this->$method($user, $sequenceItem)) {
                    $this->handleRetroactivelyRewards($user, $sequenceItem->getId());
                }
            }
        }
    }

    public function hasAlreadyRewardStep(User $user, SequenceItem $sequenceItem)
    {
        if (count($this->rewardStepRepository->findRewardStepByUserAndSequenceItem($user, $sequenceItem)) >= 1) {
            return true;
        }
        return false;
    }
   
    public function handleRetroactivelyRewards(User $user, int $sequenceItemId)
    {
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

    private function hasRepublishedAnExpiredAd(User $user, SequenceItem $sequenceItem)
    {
        // Todo check this gamaification action
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
