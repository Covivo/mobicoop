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

namespace App\ExternalService\Core\Domain\Entity\CarpoolProof;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofEntity
{
    /**
     * @var int
     */
    private $_id;

    /**
     * @var PassengerEntity
     */
    private $_passenger;

    /**
     * @var DriverEntity
     */
    private $_driver;

    /**
     * @var WaypointEntity
     */
    private $_pickUpPassenger;

    /**
     * @var WaypointEntity
     */
    private $_pickUpDriver;

    /**
     * @var WaypointEntity
     */
    private $_dropOffDriver;

    /**
     * @var WaypointEntity
     */
    private $_dropOffPassenger;

    /**
     * @var int
     */
    private $_distance;

    public function getId(): ?int
    {
        return $this->_id;
    }

    public function setId(?int $id): self
    {
        $this->_id = $id;

        return $this;
    }

    public function getPassenger(): ?PassengerEntity
    {
        return $this->_passenger;
    }

    public function setPassenger(?PassengerEntity $passenger): self
    {
        $this->_passenger = $passenger;

        return $this;
    }

    public function getDriver(): ?DriverEntity
    {
        return $this->_driver;
    }

    public function setDriver(?DriverEntity $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }

    public function getPickUpPassenger(): ?WaypointEntity
    {
        return $this->_pickUpPassenger;
    }

    public function setPickUpPassenger(?WaypointEntity $pickUpPassenger): self
    {
        $this->_pickUpPassenger = $pickUpPassenger;

        return $this;
    }

    public function getPickUpDriver(): ?WaypointEntity
    {
        return $this->_pickUpDriver;
    }

    public function setPickUpDriver(?WaypointEntity $pickUpDriver): self
    {
        $this->_pickUpDriver = $pickUpDriver;

        return $this;
    }

    public function getDropOffPassenger(): ?WaypointEntity
    {
        return $this->_dropOffPassenger;
    }

    public function setDropOffPassenger(?WaypointEntity $dropOffPassenger): self
    {
        $this->_dropOffPassenger = $dropOffPassenger;

        return $this;
    }

    public function getDropOffDriver(): ?WaypointEntity
    {
        return $this->_dropOffDriver;
    }

    public function setDropOffDriver(?WaypointEntity $dropOffDriver): self
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
