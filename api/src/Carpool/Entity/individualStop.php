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
use App\Geography\Entity\Direction;

/**
 * Carpooling : an individual stop.
 * Individual stop is a virtual public transport stop made from an offer proposal.
 * It is used for multimodal calculation. It is calculated only for offer proposal, in regions that are covered by public transportation.
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
class IndividualStop
{
    /**
     * @var int The id of this stop.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Position number of the stop in the whole route (all the individual stops of the route).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;
    
    /**
     * @var \DateTimeInterface|null The stop time.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $time;

    /**
     * @var Proposal The proposal that owns the stop.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Proposal", inversedBy="individualPoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proposal;
    
    /**
     * @var Address The address of the stop.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Address\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;
    
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
    
    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }
    
    public function setTime(?\DateTimeInterface $time): self
    {
        $this->time = $time;
        
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
}
