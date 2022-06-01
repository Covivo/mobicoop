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

namespace App\Carpool\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Address;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel point for a journey.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
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
    public const DEFAULT_ID = 999999999999;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;

    /**
     * @var int the id of this point
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread"})
     */
    private $id;

    /**
     * @var int position number of the point in the whole route
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","results","write","threads","thread", "readCommunity", "readEvent"})
     */
    private $position;

    /**
     * @var bool the point is the last point of the whole route
     *
     * @Assert\NotBlank
     * @ORM\Column(type="boolean")
     * @Groups({"read","results","write","threads","thread","readCommunity","readEvent"})
     */
    private $destination;

    /**
     * @var bool the waypoint is a floating waypoint (for dynamic carpooling)
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $floating;

    /**
     * @var bool the waypoint has been reached during a dynamic carpooling
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $reached;

    /**
     * @var null|Proposal the proposal that created the point
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="waypoints", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $proposal;

    /**
     * @var Matching the matching that created the point
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="waypoints")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $matching;

    /**
     * @var Ask the ask that created the point
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="waypoints")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $ask;

    /**
     * @var Address the address of the point
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Address", inversedBy="waypoint", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","results","write","threads","thread","readCommunity","readEvent"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    /**
     * @var null|int the duration to the waypoint
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var null|int the role associated with the waypoint
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $role;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function __clone()
    {
        // when we clone a Waypoint we exclude the id, proposal, matching, ask
        $this->id = null;
        $this->proposal = null;
        $this->matching = null;
        $this->ask = null;
        // we also clone the address
        $newAddress = clone $this->address;
        $this->address = $newAddress;
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

    public function isFloating(): bool
    {
        return $this->floating ? true : false;
    }

    public function setFloating(?bool $floating): self
    {
        $this->floating = $floating;

        return $this;
    }

    public function isReached(): bool
    {
        return $this->reached ? true : false;
    }

    public function setReached(?bool $reached): self
    {
        $this->reached = $reached;

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
        // set the reverse side, useful for address managing
        if ($address->getWaypoint() !== $this) {
            $address->setWaypoint($this);
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

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

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
