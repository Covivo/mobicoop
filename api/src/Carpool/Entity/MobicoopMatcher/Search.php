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

class Search implements \JsonSerializable
{
    public const DEFAULT_SEATS_PASSENGER = 1;
    public const DEFAULT_SEATS_DRIVER = 3;

    /**
     * @var Waypoint[]
     */
    private $waypoints;

    /**
     * @var null|string
     */
    private $departure;

    /**
     * @var null|string
     */
    private $fromDate;

    /**
     * @var null|Schedule
     */
    private $schedule;

    /**
     * @var null|MarginSchedule
     */
    private $marginSchedule;

    /**
     * @var null|bool
     */
    private $driver;

    /**
     * @var null|bool
     */
    private $passenger;

    /**
     * @var null|int
     */
    private $seatsPassenger;

    /**
     * @var null|int
     */
    private $seatsDriver;

    /**
     * Margin duration for punctual journey (in seconds).
     *
     * @var null|int
     */
    private $marginDuration;

    public function __construct()
    {
        $this->driver = false;
        $this->passenger = false;
        $this->seatsDriver = self::DEFAULT_SEATS_DRIVER;
        $this->seatsPassenger = self::DEFAULT_SEATS_PASSENGER;
    }

    public function setWaypoints(?array $waypoints)
    {
        $this->waypoints = $waypoints;

        return $this;
    }

    public function getWaypoints(): ?array
    {
        return $this->waypoints;
    }

    public function setDeparture(?string $departure)
    {
        $this->departure = $departure;

        return $this;
    }

    public function getDeparture(): ?string
    {
        return $this->departure;
    }

    public function setFromDate(?string $fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getFromDate(): ?string
    {
        return $this->fromDate;
    }

    public function setSchedule(?Schedule $schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setMarginSchedule(?MarginSchedule $marginSchedule)
    {
        $this->marginSchedule = $marginSchedule;

        return $this;
    }

    public function getMarginSchedule(): ?MarginSchedule
    {
        return $this->marginSchedule;
    }

    public function setDriver(?bool $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->driver;
    }

    public function setPassenger(?bool $passenger)
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }

    public function setSeatsDriver(?int $seatsDriver)
    {
        $this->seatsDriver = $seatsDriver;

        return $this;
    }

    public function getSeatsDriver(): ?int
    {
        return $this->seatsDriver;
    }

    public function setSeatsPassenger(?int $seatsPassenger)
    {
        $this->seatsPassenger = $seatsPassenger;

        return $this;
    }

    public function getSeatsPassenger(): ?int
    {
        return $this->seatsPassenger;
    }

    public function setMarginDuration(?int $marginDuration)
    {
        $this->marginDuration = $marginDuration;

        return $this;
    }

    public function getMarginDuration(): ?int
    {
        return $this->marginDuration;
    }

    public function jsonSerialize()
    {
        return
            [
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
