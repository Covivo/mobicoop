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

namespace App\Carpool\Entity\MobicoopMatcher;

class Waypoint implements \JsonSerializable
{
    public const ROLE_DRIVER = 'driver';
    public const ROLE_PASSENGER = 'passenger';

    public const STEP_START = 'start';
    public const STEP_FINISH = 'finish';
    public const STEP_NEUTRAL = 'neutral';

    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lon;

    public function setLat(?float $lat)
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLon(?float $lon)
    {
        $this->lon = $lon;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function jsonSerialize()
    {
        return
            [
                'lat' => $this->getLat(),
                'lon' => $this->getLon(),
            ];
    }
}
