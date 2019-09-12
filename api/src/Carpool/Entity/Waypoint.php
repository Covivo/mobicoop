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
use App\Geography\Entity\Address;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Geography\Entity\Direction;

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
class Waypoint
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this point.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread"})
     */
    private $id;

    /**
     * @var int Position number of the point in the whole route.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","results","write","threads","thread"})
     */
    private $position;

    /**
     * @var boolean The point is the last point of the whole route.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="boolean")
     * @Groups({"read","results","write","threads","thread"})
     */
    private $destination;

    /**
     * @var Proposal|null The proposal that created the point.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="waypoints")
     */
    private $proposal;
    
    /**
     * @var Matching The matching that created the point.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="waypoints")
     */
    private $matching;
    
    /**
     * @var Ask The ask that created the point.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="waypoints")
     */
    private $ask;
    
    /**
     * @var Address The address of the point.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","results","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $address;
    
    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

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

    public function isDestination(): ?bool
    {
        return $this->destination;
    }

    public function setDestination(bool $isDestination): self
    {
        $this->destination = $isDestination;

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
    
    public function getMatching(): ?Matching
    {
        return $this->matching;
    }
    
    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;
        
        return $this;
    }
    
    public function getAsk(): ?Ask
    {
        return $this->ask;
    }
    
    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        
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
}
