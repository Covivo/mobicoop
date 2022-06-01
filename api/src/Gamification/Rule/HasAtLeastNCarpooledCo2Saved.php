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
 */

namespace App\Gamification\Rule;

use App\Gamification\Entity\SequenceItem;
use App\Gamification\Interfaces\GamificationRuleInterface;

/**
 * Check if the user has at least saved N CO².
 */
class HasAtLeastNCarpooledCo2Saved implements GamificationRuleInterface
{
    /**
     * Has at least N saved CO².
     *
     * @param $log
     * @param $sequenceItem
     */
    public function execute($log, $sequenceItem): bool
    {
        // we check if the user has at least saved N CO²
        // we divide it by 1000 since the amount of savedCo2 is calculated in grams and we want kg)
        $savedCo2 = ($log->getUser()->getSavedCo2() / 1000);
        if ($sequenceItem instanceof SequenceItem) {
            if ($savedCo2 >= $sequenceItem->getValue()) {
                return true;
            }

            return false;
        }
        if ($savedCo2 >= $sequenceItem['si_value']) {
            return true;
        }

        return false;
    }
}
