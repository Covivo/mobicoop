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
use App\Gamification\Interfaces\GamificationRuleInterface;

/**
 *  Check that the requester is the author of the related Ad
 */
class CarpoolInEvent implements GamificationRuleInterface
{
    /**
     * Carpool In Event rule
     *
     * @param  $requester
     * @param  $log
     * @param  $sequenceItem
     * @return bool
     */
    public function execute($requester, $log, $sequenceItem)
    {
        // we check if the user has at least one proposal carpooled and published in an event
        $user = $log->getUser();
        // we get all user's proposals and for each proposal we check if he's associated with an event
        $proposals = $user->getProposals();
        foreach ($proposals as $proposal) {
            // for each proposal we check if he's carpooled
            $asks = $proposal->getAsks();
            $isCarpooled = false;
            foreach ($asks as $ask) {
                if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                    $isCarpooled = true;
                }
            }
            // if a proposal he's carpooled and associated to an event we return true
            if ($isCarpooled && $proposal->getEvent()) {
                return true;
            }
        }
        return false;
    }
}
