<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Geography\Service;

use App\Geography\Ressource\Point;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PointGeoFixer
{
    private $_data;

    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    public function fix(array $points): array
    {
        foreach ($points as $key => $point) {
            $points[$key] = $this->_treatPoint($point);
        }

        return $points;
    }

    private function _treatPoint(Point $point): Point
    {
        foreach ($this->_data as $fix) {
            $point = $this->_applyFixToPoint($point, $fix);
        }

        return $point;
    }

    private function _applyFixToPoint(Point $point, array $fix): Point
    {
        if ($this->_checkCriteria($point, $fix)) {
            $this->_replaceData($point, $fix);
        }

        return $point;
    }

    private function _replaceData(Point $point, array $fix): Point
    {
        foreach (array_keys($fix['replacement']) as $replacement) {
            if (method_exists($point, 'set'.ucfirst($replacement))) {
                $point->{'set'.ucfirst($replacement)}($fix['replacement'][$replacement]);
            }
        }

        return $point;
    }

    private function _checkCriteria(Point $point, array $fix): bool
    {
        $replace = true;
        foreach (array_keys($fix['criteria']) as $criteria) {
            if (
                method_exists($point, 'get'.ucfirst($criteria))
                && method_exists($point, 'set'.ucfirst($criteria))
                && $point->{'get'.$criteria}() == $fix['criteria'][$criteria]
            ) {
                $replace &= true;
            } else {
                $replace &= false;
            }
        }

        return (bool) $replace;
    }
}
