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

namespace App\ExternalService\Interfaces\DTO\CarpoolProof;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofDto
{
    /**
     * @var int
     */
    private $_journeyId;

    /**
     * @var PassengerDto
     */
    private $_passenger;

    /**
     * @var DriverDto
     */
    private $_driver;

    /**
     * @var WaypointDto
     */
    private $_pickUpPassenger;

    /**
     * @var WaypointDto
     */
    private $_pickUpDriver;

    /**
     * @var WaypointDto
     */
    private $_dropOffDriver;

    /**
     * @var WaypointDto
     */
    private $_dropOffPassenger;

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

    public function getPickUpPassenger(): ?WaypointDto
    {
        return $this->_pickUpPassenger;
    }

    public function setPickUpPassenger(?WaypointDto $pickUpPassenger): self
    {
        $this->_pickUpPassenger = $pickUpPassenger;

        return $this;
    }

    public function getPickUpDriver(): ?WaypointDto
    {
        return $this->_pickUpDriver;
    }

    public function setPickUpDriver(?WaypointDto $pickUpDriver): self
    {
        $this->_pickUpDriver = $pickUpDriver;

        return $this;
    }

    public function getDropOffPassenger(): ?WaypointDto
    {
        return $this->_dropOffPassenger;
    }

    public function setDropOffPassenger(?WaypointDto $dropOffPassenger): self
    {
        $this->_dropOffPassenger = $dropOffPassenger;

        return $this;
    }

    public function getDropOffDriver(): ?WaypointDto
    {
        return $this->_dropOffDriver;
    }

    public function setDropOffDriver(?WaypointDto $dropOffDriver): self
    {
        $this->_dropOffDriver = $dropOffDriver;

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
