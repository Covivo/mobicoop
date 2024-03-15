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

namespace App\Mapper\Interfaces\DTO\CarpoolProof;

use App\ExternalService\Interfaces\DTO\DTO;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofDTO extends DTO
{
    public const CONTEXT = 'carpool.proof';

    /**
     * @var PassengerDTO
     */
    private $_passenger;

    /**
     * @var DriverDTO
     */
    private $_driver;

    /**
     * @var ?WaypointDTO
     */
    private $_pickUpPassenger;

    /**
     * @var ?WaypointDTO
     */
    private $_pickUpDriver;

    /**
     * @var ?WaypointDTO
     */
    private $_dropOffDriver;

    /**
     * @var ?WaypointDTO
     */
    private $_dropOffPassenger;

    /**
     * @var int
     */
    private $_distance;

    public function getPassenger(): ?PassengerDTO
    {
        return $this->_passenger;
    }

    public function setPassenger(?PassengerDTO $passenger): self
    {
        $this->_passenger = $passenger;

        return $this;
    }

    public function getDriver(): ?DriverDTO
    {
        return $this->_driver;
    }

    public function setDriver(?DriverDTO $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }

    public function getPickUpPassenger(): ?WaypointDTO
    {
        return $this->_pickUpPassenger;
    }

    public function setPickUpPassenger(?WaypointDTO $pickUpPassenger): self
    {
        $this->_pickUpPassenger = $pickUpPassenger;

        return $this;
    }

    public function getPickUpDriver(): ?WaypointDTO
    {
        return $this->_pickUpDriver;
    }

    public function setPickUpDriver(?WaypointDTO $pickUpDriver): self
    {
        $this->_pickUpDriver = $pickUpDriver;

        return $this;
    }

    public function getDropOffPassenger(): ?WaypointDTO
    {
        return $this->_dropOffPassenger;
    }

    public function setDropOffPassenger(?WaypointDTO $dropOffPassenger): self
    {
        $this->_dropOffPassenger = $dropOffPassenger;

        return $this;
    }

    public function getDropOffDriver(): ?WaypointDTO
    {
        return $this->_dropOffDriver;
    }

    public function setDropOffDriver(?WaypointDTO $dropOffDriver): self
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
