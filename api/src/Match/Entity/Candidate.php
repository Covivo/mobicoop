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
 */

namespace App\Match\Entity;

use App\Geography\Entity\Direction;

/**
 * A matching candidate.
 */
class Candidate
{
    /**
     * @var int the id of this candidate
     */
    private $id;

    /**
     * @var Address[] the address points of the candidate
     */
    private $addresses;

    /**
     * @var null|Direction the base direction of the candidate
     */
    private $direction;

    /**
     * @var int the direction time (in seconds)
     */
    private $duration;

    /**
     * @var int the direction distance (in metres)
     */
    private $distance;

    /**
     * @var int the maximum detour time (in seconds) accepted to match
     */
    private $maxDetourDuration;

    /**
     * @var int the maximum detour distance (in metres) accepted to match
     */
    private $maxDetourDistance;

    /**
     * @var int the maximum detour time (in percentage of the original duration) accepted to match
     */
    private $maxDetourDurationPercent;

    /**
     * @var int the maximum detour distance (in percentage of the original distance) accepted to match
     */
    private $maxDetourDistancePercent;

    /**
     * @var int the minimum common travel distance in percentage of the original distance accepted to match
     */
    private $minCommonDistancePercent;

    /**
     * @var MassPerson The mass person associated with the candidate in case of mass matching
     */
    private $massPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function setAddresses(array $addresses): self
    {
        $this->addresses = $addresses;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getMaxDetourDuration(): ?int
    {
        return $this->maxDetourDuration;
    }

    public function setMaxDetourDuration(int $maxDetourDuration): self
    {
        $this->maxDetourDuration = $maxDetourDuration;

        return $this;
    }

    public function getMaxDetourDistance(): ?int
    {
        return $this->maxDetourDistance;
    }

    public function setMaxDetourDistance(int $maxDetourDistance): self
    {
        $this->maxDetourDistance = $maxDetourDistance;

        return $this;
    }

    public function getMaxDetourDurationPercent(): ?int
    {
        return $this->maxDetourDurationPercent;
    }

    public function setMaxDetourDurationPercent(int $maxDetourDurationPercent): self
    {
        $this->maxDetourDurationPercent = $maxDetourDurationPercent;

        return $this;
    }

    public function getMaxDetourDistancePercent(): ?int
    {
        return $this->maxDetourDistancePercent;
    }

    public function setMaxDetourDistancePercent(int $maxDetourDistancePercent): self
    {
        $this->maxDetourDistancePercent = $maxDetourDistancePercent;

        return $this;
    }

    public function getMinCommonDistancePercent(): ?int
    {
        return $this->minCommonDistancePercent;
    }

    public function setMinCommonDistancePercent(int $minCommonDistancePercent): self
    {
        $this->minCommonDistancePercent = $minCommonDistancePercent;

        return $this;
    }

    public function getMassPerson(): ?MassPerson
    {
        return $this->massPerson;
    }

    public function setMassPerson(?MassPerson $massPerson): self
    {
        $this->massPerson = $massPerson;

        return $this;
    }
}
