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

use App\Gamification\Entity\Badge;
use App\User\Entity\User;
use App\Gamification\Repository\BadgeRepository;
use App\Gamification\Entity\BadgeProgression;
use App\Gamification\Entity\BadgeSummary;
use App\Gamification\Entity\SequenceStatus;
use App\Gamification\Resource\BadgesBoard;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Gamification Manager
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgesBoardManager
{
    private $badgeRepository;
    private $badgeImageUri;

    public function __construct(
        BadgeRepository $badgeRepository,
        EntityManagerInterface $entityManager,
        string $badgeImageUri
    ) {
        $this->badgeRepository = $badgeRepository;
        $this->entityManager = $entityManager;
        $this->badgeImageUri = $badgeImageUri;
    }
    
    /**
     * Get all the Badges of the instance
     * @param int $status  Get only the Badges of this status (default : null, every badges are returned)
     * @return Badges[]|null
     */
    public function getBadges(int $status=null): ?array
    {
        if (is_null($status)) {
            return $this->badgeRepository->findAll();
        } else {
            return $this->badgeRepository->findBy(["status"=>$status]);
        }
    }
    
    /**
     * Get the Badges board of a User
     *
     * @param User $user    The User
     * @return BadgesBoard
     */
    public function getBadgesBoard(User $user): BadgesBoard
    {
        $badgesBoard = new BadgesBoard();
        
        // Set if the user accept Gamification tracking
        $badgesBoard->setAcceptGamification($user->hasGamification());
        
        // Get all the active badges of the platform
        $activeBadges = $this->getBadges(Badge::STATUS_ACTIVE);
        $badges = [];

        /**
         * @var Badge $activeBadge
         */
        foreach ($activeBadges as $activeBadge) {
            $badgeProgression = new BadgeProgression();
            
            // Determine if the badge is already earned
            $badgeProgression->setEarned(false);
            foreach ($activeBadge->getRewards() as $reward) {
                if ($reward->getUser()->getId() == $user->getId()) {
                    $badgeProgression->setEarned(true);
                    break;
                }
            }

            // Minimum data about the current badge
            $badgeSummary = new BadgeSummary();
            $badgeSummary->setBadgeId($activeBadge->getId());
            $badgeSummary->setBadgeName($activeBadge->getName());
            $badgeSummary->setBadgeTitle($activeBadge->getTitle());

            // images
            $badgeSummary->setIcon((!is_null($activeBadge->getIcon())) ? $this->badgeImageUri.$activeBadge->getIcon()->getFileName() : null);
            $badgeSummary->setDecoratedIcon((!is_null($activeBadge->getDecoratedIcon())) ? $this->badgeImageUri.$activeBadge->getDecoratedIcon()->getFileName() : null);
            $badgeSummary->setImage((!is_null($activeBadge->getImage())) ? $this->badgeImageUri.$activeBadge->getImage()->getFileName() : null);
            $badgeSummary->setImageLight((!is_null($activeBadge->getImageLight())) ? $this->badgeImageUri.$activeBadge->getImageLight()->getFileName() : null);

            // We get the sequence and check if the current user validated it
            $sequences = [];
            $nbValidatedSequences = 0;
            foreach ($activeBadge->getSequenceItems() as $sequenceItem) {
                $sequenceStatus = new SequenceStatus();
                $sequenceStatus->setSequenceItemId($sequenceItem->getId());
                $sequenceStatus->setTitle($sequenceItem->getGamificationAction()->getTitle());
                
                
                // We look into the rewardSteps previously existing for this SequenceItem
                // If there is one for the current User, we know that it has already been validated
                $sequenceStatus->setValidated(false);
                foreach ($sequenceItem->getRewardSteps() as $rewardStep) {
                    if ($rewardStep->getUser()->getId() == $user->getId()) {
                        $sequenceStatus->setValidated(true);
                        $nbValidatedSequences++;
                        break;
                    }
                }
                $sequences[] = $sequenceStatus;
            }
            $badgeSummary->setSequences($sequences);

            $badgeProgression->setBadgeSummary($badgeSummary);

            // Compute the earned percentage
            $badgeProgression->setEarningPercentage(0);
            if ($nbValidatedSequences==0) {
                $badgeProgression->setEarningPercentage(0);
            } else {
                $badgeProgression->setEarningPercentage($nbValidatedSequences/count($activeBadge->getSequenceItems())*100);
            }

            $badges[] = $badgeProgression;
        }
        
        $badgesBoard->setBadges($badges);

        return $badgesBoard;
    }
}
