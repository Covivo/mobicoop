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

namespace App\Match\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An Journey after a Matching diagnostic
 */
class MassJourney
{
    /**
     * @var int $idPerson
     * @Groups({"mass","massCompute"})
     */
    private $idPerson;

    /**
     * @var int $distance
     * @Groups({"mass","massCompute"})
     */
    private $distance;

    /**
     * @var int $duration
     * @Groups({"mass","massCompute"})
     */
    private $duration;

    /**
     * @var float co2
     * @Groups({"mass","massCompute"})
     */
    private $co2;

    /**
     * MassJourney constructor.
     * @param int $idPerson
     * @param int $distance
     * @param int $duration
     * @param float $co2
     */
    public function __construct(int $distance, int $duration, float $co2, int $idPerson=null)
    {
        $this->idPerson = $idPerson;
        $this->distance = $distance;
        $this->duration = $duration;
        $this->co2 = $co2;
    }


    /**
     * @return int
     */
    public function getIdPerson(): int
    {
        return $this->idPerson;
    }

    /**
     * @param int $idPerson
     * @return int
     */
    public function setIdPerson(int $idPerson): int
    {
        $this->idPerson = $idPerson;
    }

    /**
     * @return int
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     * @return int
     */
    public function setDistance(int $distance): int
    {
        $this->distance = $distance;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return int
     */
    public function setDuration(int $duration): int
    {
        $this->duration = $duration;
    }

    /**
     * @return float
     */
    public function getCo2(): float
    {
        return $this->co2;
    }

    /**
     * @param float $co2
     * @return float
     */
    public function setCo2(float $co2): float
    {
        $this->co2 = $co2;
    }
}
