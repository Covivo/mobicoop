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

use App\Gamification\Interfaces\GamificationRuleInterface;

/**
 * Check if the user has at least N ad
 */
class HasAtLeastNAd implements GamificationRuleInterface
{
    /**
     * Has at least N proposal
     *
     * @param  $log
     * @param  $sequenceItem
     * @return bool
     */
    public function execute($log, $sequenceItem)
    {
        // we check if the user has at least N proposals published
        $user = $log->getUser();
        $proposals = $user->getProposals();
        $publishedProposals = [];
        // we check that the proposal is a published proposal and not a search
        foreach ($proposals as $proposal) {
            if (!$proposal->isPrivate()) {
                $publishedProposals[] = $proposal;
            }
        }
        if (count($publishedProposals) >= $sequenceItem->getMinCount()) {
            return true;
        }
        return false;
    }
}
