<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\ExternalService\Interfaces\DTO;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofDto
{
    public const TYPE_LOW = 'A';
    public const TYPE_MID = 'B';
    public const TYPE_HIGH = 'C';

    public const TYPES = [
        self::TYPE_LOW,
        self::TYPE_MID,
        self::TYPE_HIGH,
    ];

    /**
     * @var int
     */
    private $_journeyId;

    /**
     * @var string register system proof type : see TYPES
     */
    private $_operatorClass;

    /**
     * @var PassengerDto
     */
    private $_passenger;

    /**
     * @var DriverDto
     */
    private $_driver;

    /**
     * @var array
     */
    private $_incentives;

    /**
     * @var array
     */
    private $_incentiveCounterparts;

    /**
     * @var WaypointDto
     */
    private $_start;

    /**
     * @var WaypointDto
     */
    private $_end;

    /**
     * @var int
     */
    private $_distance;

    public function getJourneyId(): ?int
    {
        return $this->_journeyId;
    }

    public function setJourneyId(?int $journeyId): self
    {
        $this->_journeyId = $journeyId;

        return $this;
    }

    public function getOperatorClass(): ?string
    {
        return $this->_operatorClass;
    }

    public function setOperatorClass(?string $operatorClass): self
    {
        $this->_operatorClass = $operatorClass;

        return $this;
    }

    public function getPassenger(): ?PassengerDto
    {
        return $this->_passenger;
    }

    public function setPassenger(?PassengerDto $passenger): self
    {
        $this->_passenger = $passenger;

        return $this;
    }

    public function getDriver(): ?DriverDto
    {
        return $this->_driver;
    }

    public function setDriver(?DriverDto $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }

    public function getIncentives(): ?array
    {
        return $this->_incentives;
    }

    public function setIncentives(?array $incentives): self
    {
        $this->_incentives = $incentives;

        return $this;
    }

    public function getincentiveCounterparts(): ?array
    {
        return $this->_incentiveCounterparts;
    }

    public function setincentiveCounterparts(?array $incentiveCounterparts): self
    {
        $this->_incentiveCounterparts = $incentiveCounterparts;

        return $this;
    }

    public function getStart(): ?WaypointDto
    {
        return $this->_start;
    }

    public function setStart(?WaypointDto $start): self
    {
        $this->_start = $start;

        return $this;
    }

    public function getEnd(): ?WaypointDto
    {
        return $this->_end;
    }

    public function setEnd(?WaypointDto $end): self
    {
        $this->_end = $end;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->_distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->_distance = $distance;

        return $this;
    }
}
