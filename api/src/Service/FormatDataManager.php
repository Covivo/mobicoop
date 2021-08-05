<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Service;

use App\Carpool\Entity\Criteria;

/**
 * Format Data Manager
 *
 * Its a utility service. Contains some format data functions.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class FormatDataManager
{
    // limit price to round at .5 cents
    const PRICE_LIMIT = 5;
    const PRICE_ROUND_TYPE_1 = .1;
    const PRICE_ROUND_TYPE_2 = .5;
    const PRICE_ROUND_TYPE_3 = 1;

    /**
     * Convert time given in seconds to a human readable format
     * hours minutes seconds
     * @param int $time : time in seconds
     * @return string
     */
    public function convertSecondsToHuman($time)
    {
        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        $seconds = $time % 60;

        $humanReturn = "";
        if ($hours != 0) {
            $humanReturn .= $hours." h ";
        }

        $humanReturn .= $minutes." m";

        if ($seconds != 0) {
            $humanReturn .= " ".$seconds." s";
        }


        return $humanReturn;
    }

    //
    /**
     * Round a price depending on a trip frequency.
     *
     * @param float $price          The price to be rounded
     * @param integer $frequency    The frequency
     * @return float
     */
    public function roundPrice(float $price, int $frequency)
    {
        switch ($frequency) {
            case Criteria::FREQUENCY_REGULAR:
                return self::roundNearest($price, self::PRICE_ROUND_TYPE_1);
                break;
            case Criteria::FREQUENCY_PUNCTUAL:
                if ($price<=self::PRICE_LIMIT) {
                    return self::roundNearest($price, self::PRICE_ROUND_TYPE_2);
                }
                return self::roundNearest($price, self::PRICE_ROUND_TYPE_3);
                break;
        }
    }

    // rounds to the nearest subdivision
    private static function roundNearest($num, $nearest = .5)
    {
        //return round($num / $nearest) * $nearest;
        return round((round($num / $nearest) * $nearest), 1);
    }

    // convert a file size to a human readable format
    public function convertFilesize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}
