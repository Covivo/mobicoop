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

use App\Action\Entity\Action;
use App\Action\Entity\Log;
use App\Action\Repository\LogRepository;
use App\Gamification\Entity\GamificationAction;
use App\Gamification\Repository\SequenceItemRepository;
use App\User\Entity\User;
use App\Gamification\Entity\SequenceItem;
use App\Gamification\Entity\ValidationStep;

/**
 * Gamification Manager
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GamificationManager
{
    private $sequenceItemRepository;
    private $logRepository;

    public function __construct(SequenceItemRepository $sequenceItemRepository, LogRepository $logRepository)
    {
        $this->sequenceItemRepository = $sequenceItemRepository;
        $this->logRepository = $logRepository;
    }
    
    /**
     * When a new log entry is detected, we treat it to determine if there is something to do (i.e Gamification)
     *
     * @param Log $log          Event of the action
     * @return void
     */
    public function handleLog(Log $log)
    {
        // A new log has been recorded. We need to check if there is a gamification action to take
        $gamificationActions = $log->getAction()->getGamificationActions();
        if (is_array($gamificationActions) && count($gamificationActions)>1) {
            // This action has gamification action, we need to treat it
            foreach ($gamificationActions as $gamificationAction) {
                $validationSteps = $this->treatGamificationAction($gamificationAction, $log->getUser());
                //var_dump($validationSteps);
            }
        }
    }

    /**
     * Treatment and evaluation of a GamificationAction
     *
     * @param GamificationAction $gamificationAction
     * @param User $user
     * @return ValidationStep[]
     */
    private function treatGamificationAction(GamificationAction $gamificationAction, User $user): array
    {
        // We check if this action is in a sequenceItem
        $validationSteps = [];

        $sequenceItems = $this->sequenceItemRepository->findBy(['gamificationAction'=>$gamificationAction]);
        if (is_array($sequenceItems) && count($sequenceItems)>1) {
            // This action has gamification action, we need to treat it
            foreach ($sequenceItems as $sequenceItem) {
                
                /**
                 * @var SequenceItem $sequenceItem
                 */

                $validationStep = new ValidationStep();
                $validationStep->setValid(true); // By default, the sequenceItem is valid

                // This related action needs to be made a minimum amount of time
                if (!is_null($sequenceItem->getMinCount()) && $sequenceItem->getMinCount()>0) {
                    $validationStep->setValid($validationStep->isValid() && $this->checkMinCount($gamificationAction->getAction(), $user, $sequenceItem->getMinCount()));
                }
            }
        }

        return $validationSteps;
    }

    /**
     * Check if the MinCount criteria is verified
     *
     * @param Action $action    The action to count
     * @param User $user        The User we count for
     * @param int $minCount     The min count to be valid
     * @return boolean  True for valid
     */
    private function checkMinCount(Action $action, User $user, int $minCount): bool
    {
        // We get in the log table all the Action $action made by this User $user
        $logs = $this->logRepository->findBy(['action'=>$action, 'user'=>$user]);
        if (is_array($logs) && count($logs)>=$minCount) {
            return true;
        }

        return false;
    }
}
