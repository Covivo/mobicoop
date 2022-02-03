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

namespace App\Gamification\Rule;

use App\Carpool\Entity\Ask;
use App\Gamification\Entity\SequenceItem;
use App\Gamification\Interfaces\GamificationRuleInterface;
use App\Payment\Entity\CarpoolItem;

/**
 * Check if the user has at least carpooled N km
 */
class HasAtLeastNCarpooledKm implements GamificationRuleInterface
{
    /**
     * has at least N carpooled Km rule
     *
     * @param $log
     * @param $sequenceItem
     * @return bool
     */
    public function execute($log, $sequenceItem)
    {
        // we check if the user has carpool at least N Km
        $user = $log->getUser();
        // we get all user's asks
        $asks = array_merge($user->getAsks(), $user->getAsksRelated());
        $carpooledKm = null;
        foreach ($asks as $ask) {
            if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                $carpoolItems = $ask->getCarpoolItems();
                $numberOfTravel = null;
                foreach ($carpoolItems as $carpoolItem) {
                    if ($carpoolItem->getItemStatus() == CarpoolItem::STATUS_REALIZED) {
                        $numberOfTravel = + 1;
                    }
                }
                $carpooledKm = $carpooledKm + ($ask->getMatching()->getCommonDistance() * $numberOfTravel);
            }
        }
        // if a proposal he's carpooled and associated to a community we return true
        if ($sequenceItem instanceof SequenceItem) {
            if (($carpooledKm / 1000) >= $sequenceItem->getValue()) {
                return true;
            }
            return false;
        }
        if (($carpooledKm / 1000) >= $sequenceItem['si_value']) {
            return true;
        }
        return false;
    }
}
