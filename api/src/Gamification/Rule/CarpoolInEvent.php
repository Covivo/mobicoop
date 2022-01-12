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
 * Check if the user carpooled for an event
 */
class CarpoolInEvent implements GamificationRuleInterface
{
    /**
     * Carpool In Event rule
     *
     * @param  $log
     * @param  $sequenceItem
     * @return bool
     */
    public function execute($log, $sequenceItem)
    {
        $user = $log->getUser();
        // we check if the user has at least one proposal published for an event
        // we get all user's proposals and for each proposal we check if he's associated with an event
        $proposals = $user->getProposals();
        foreach ($proposals as $proposal) {
            $matchings=[];
            $matchings[]=$proposal->getMatchingOffers();
            $matchings[]=$proposal->getMatchingRequests();
            foreach ($matchings as $matching) {
                if ($matching->getAsk()->getStatus() === Ask::STATUS_ACCEPTED_AS_DRIVER || $matching->getAsk()->getStatus() === Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                    return true;
                }
            }
        }

        return false;
    }
}
