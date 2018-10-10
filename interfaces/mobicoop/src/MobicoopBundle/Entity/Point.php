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

namespace Mobicoop\Bundle\MobicoopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel point for a journey.
 */
Class Point 
{
    /**
     * @var int The id of this point.
     */
    private $id;
    
    /**
     * @var string|null The iri of this proposal.
     */
    private $iri;

    /**
     * @var int Position number of the point in the whole journey.
     * @Assert\NotBlank
     * 
     * @Groups({"post","put"})
     */
    private $position;

    /**
     * @var boolean The point is the last point of the whole journey.
     * @Assert\NotBlank
     * 
     * @Groups({"post","put"})
     */
    private $lastPoint;

    /**
     * @var int|null Real distance to next point in metres.
     */
    private $distanceNextReal;

    /**
     * @var int|null Flying distance to next point in metres.
     */
    private $distanceNextFly;

    /**
     * @var int|null Duration to the next point in seconds (based on real distance).
     */
    private $durationNext;

    /**
     * @var Proposal The proposal that created the point.
     * @Assert\NotBlank
     */
    private $proposal;
    
    /**
     * @var Path The path associated with the point as a start.
     * @Assert\NotBlank
     */
    private $pathStart;
    
    /**
     * @var Path The path associated with the point as a destination.
     * @Assert\NotBlank
     */
    private $pathDestination;

    /**
     * @var Address The address of the point.
     * @Assert\NotBlank
     * 
     * @Groups({"post","put"})
     */
    private $address;

    /**
     * @var TravelMode|null The travel mode used from the point to the next point.
     * 
     * @Groups({"post","put"})
     */
    private $travelMode;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLastPoint(): ?bool
    {
        return $this->lastPoint;
    }

    public function setLastPoint(bool $lastPoint): self
    {
        $this->lastPoint = $lastPoint;

        return $this;
    }

    public function getDistanceNextReal(): ?int
    {
        return $this->distanceNextReal;
    }

    public function setDistanceNextReal(?int $distanceNextReal): self
    {
        $this->distanceNextReal = $distanceNextReal;

        return $this;
    }

    public function getDistanceNextFly(): ?int
    {
        return $this->distanceNextFly;
    }

    public function setDistanceNextFly(?int $distanceNextFly): self
    {
        $this->distanceNextFly = $distanceNextFly;

        return $this;
    }

    public function getDurationNext(): ?int
    {
        return $this->durationNext;
    }

    public function setDurationNext(?int $durationNext): self
    {
        $this->durationNext = $durationNext;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getTravelMode(): ?TravelMode
    {
        return $this->travelMode;
    }

    public function setTravelMode(?TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;

        return $this;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }

}