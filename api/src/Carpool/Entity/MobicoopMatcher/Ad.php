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

class Ad extends Search implements \JsonSerializable
{
    /**
     * @var null|int
     */
    private $identifier;

    public function __construct(?int $identifier = null)
    {
        parent::__construct();
        if (!is_null($identifier)) {
            $this->identifier = $identifier;
        }
    }

    public function setIdentifier(?int $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    public function jsonSerialize()
    {
        return
            [
                'identifier' => $this->getIdentifier(),
                'waypoints' => $this->getWaypoints(),
                'departure' => $this->getDeparture(),
                'from_date' => $this->getFromDate(),
                'schedule' => $this->getSchedule(),
                'driver' => $this->isDriver(),
                'passenger' => $this->isPassenger(),
                'seats_driver' => $this->getSeatsDriver(),
                'seats_passenger' => $this->getSeatsPassenger(),
                'margin_duration' => $this->getMarginDuration(),
                'margin_durations' => $this->getMarginSchedule(),
            ];
    }
}
