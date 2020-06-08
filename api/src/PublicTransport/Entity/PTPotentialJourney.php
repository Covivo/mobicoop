<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\PublicTransport\Entity;

use App\Match\Entity\MassPerson;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport potential journey.
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTPotentialJourney
{
    /**
     * @var int The id of this potential journey.
     *
     * @Groups("pt")
     */
    private $id;
    
    /**
     * @var MassPerson The owner of this potential journey.
     *
     * @Groups("pt")
     */
    private $massPerson;

    /**
     * @var int The total distance of this potential journey.
     *
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var int The total duration of this potential journey (in seconds).
     *
     * @Groups("pt")
     */
    private $duration;

    /**
     * @var int The estimated CO2 emission of this potential journey.
     *
     * @Groups("pt")
     */
    private $co2;

    /**
     * @var int The number of changes of this potential journey.
     *
     * @Groups("pt")
     */
    private $changeNumber;
    
    /**
     * @var int The distance from home of this potential journey
     *
     * @Groups("pt")
     */
    private $distanceWalkFromHome;
    
    /**
     * @var int The duration from home of this potential journey (in seconds)
     *
     * @Groups("pt")
     */
    private $durationWalkFromHome;

    /**
     * @var int The distance from work of this potential journey
     *
     * @Groups("pt")
     */
    private $distanceWalkFromWork;

    /**
     * @var int The duration from work of this potential journey (in seconds)
     *
     * @Groups("pt")
     */
    private $durationWalkFromWork;

    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getMassPerson(): MassPerson
    {
        return $this->massPerson;
    }
    
    public function setMassPerson(MassPerson $massPerson): self
    {
        $this->massPerson = $massPerson;
        
        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }
    
    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;
        
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
    
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        
        return $this;
    }
    
    public function getChangeNumber(): ?int
    {
        return $this->changeNumber;
    }
    
    public function setChangeNumber(?int $changeNumber): self
    {
        $this->changeNumber = $changeNumber;
        
        return $this;
    }

    public function getCo2(): ?int
    {
        return $this->co2;
    }
    
    public function setCo2(?int $co2): self
    {
        $this->co2 = $co2;
        
        return $this;
    }

    public function getDistanceWalkFromHome(): ?int
    {
        return $this->distanceWalkFromHome;
    }
    
    public function setDistanceWalkFromHome(?int $distanceWalkFromHome): self
    {
        $this->distanceWalkFromHome = $distanceWalkFromHome;
        
        return $this;
    }
    
    public function getDurationWalkFromHome(): ?int
    {
        return $this->durationWalkFromHome;
    }
    
    public function setDurationWalkFromHome(?int $durationWalkFromHome): self
    {
        $this->durationWalkFromHome = $durationWalkFromHome;
        
        return $this;
    }

    public function getDistanceWalkFromWork(): ?int
    {
        return $this->distanceWalkFromWork;
    }
    
    public function setDistanceWalkFromWork(?int $distanceWalkFromWork): self
    {
        $this->distanceWalkFromWork = $distanceWalkFromWork;
        
        return $this;
    }

    public function getDurationWalkFromWork(): ?int
    {
        return $this->durationWalkFromWork;
    }
    
    public function setDurationWalkFromWork(?int $durationWalkFromWork): self
    {
        $this->durationWalkFromWork = $durationWalkFromWork;
        
        return $this;
    }
}
