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

namespace App\Carpool\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Address\Entity\Address;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel point for a journey.
 * 
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
Class Point 
{
    /**
     * @var int The id of this point.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Position number of the point in the whole journey.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var boolean The point is the last point of the whole journey.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="boolean")
     * @Groups({"read","write"})
     */
    private $lastPoint;

    /**
     * @var int|null Real distance to next point in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceNextReal;

    /**
     * @var int|null Flying distance to next point in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceNextFly;

    /**
     * @var int|null Duration to the next point in seconds (based on real distance).
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $durationNext;

    /**
     * @var Proposal The proposal that created the point.
     * 
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Proposal", inversedBy="points")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proposal;
    
    /**
     * @var Path The path associated with the point as a start.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Path", mappedBy="point1", orphanRemoval=true)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $pathStart;
    
    /**
     * @var Path The path associated with the point as a destination.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Path", mappedBy="point2", orphanRemoval=true)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $pathDestination;

    /**
     * @var Address The address of the point.
     * 
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Address\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var TravelMode|null The travel mode used from the point to the next point.
     *  
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\TravelMode")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelMode;
    
    public function getId(): ?int
    {
        return $this->id;
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

}