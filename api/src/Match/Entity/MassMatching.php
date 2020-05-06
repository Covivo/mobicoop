<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Direction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A potential matching between 2 persons from a mass file import.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"mass"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class MassMatching
{
    /**
     * @var int The id of this matching.
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MassPerson The first person.
     * @ORM\ManyToOne(targetEntity="\App\Match\Entity\MassPerson", cascade={"persist","remove"}, inversedBy="matchingsAsDriver")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $massPerson1;

    /**
     * @var MassPerson The second person.
     * @ORM\ManyToOne(targetEntity="\App\Match\Entity\MassPerson", cascade={"persist","remove"}, inversedBy="matchingsAsPassenger")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $massPerson2;

    /**
     * @var int The total distance of the direction in meter.
     * corresponding to newDistance in classic carpool matching
     * @ORM\Column(type="integer")
     * @Groups({"mass","massCompute"})
     */
    private $distance;
    
    /**
     * @var int|null The original distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $originalDistance;

    /**
     * @var int|null The accepted detour distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $acceptedDetourDistance;

    /**
     * @var int|null The detour distance of the driver in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $detourDistance;

    /**
     * @var float|null The detour distance of the driver in percentage of the original distance.
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $detourDistancePercent;

    /**
     * @var int|null The original duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $originalDuration;

    /**
     * @var int|null The accepted detour duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $acceptedDetourDuration;

    /**
     * @var int The total duration of the direction in milliseconds.
     * corresponding to newDuration in classic carpool matching
     * @ORM\Column(type="integer")
     * @Groups({"mass","massCompute"})
     */
    private $duration;

    /**
     * @var int|null The detour duration of the driver in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $detourDuration;

    /**
     * @var int|null The detour duration of the driver in percentage of the original duration.
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $detourDurationPercent;

    /**
     * @var int|null The common distance in metres.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $commonDistance;

    /**
     * @var int|null The duration till the pick up of the passenger in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $pickUpDuration;

    /**
     * @var int|null The duration till the dropoff of the passenger in seconds.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massCompute"})
     */
    private $dropOffDuration;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMassPerson1(): MassPerson
    {
        return $this->massPerson1;
    }

    public function setMassPerson1(MassPerson $massPerson1): self
    {
        $this->massPerson1 = $massPerson1;

        return $this;
    }

    public function getMassPerson1Id(): int
    {
        return $this->massPerson1->getId();
    }

    public function getMassPerson2(): MassPerson
    {
        return $this->massPerson2;
    }

    public function setMassPerson2(MassPerson $massPerson2): self
    {
        $this->massPerson2 = $massPerson2;

        return $this;
    }

    public function getMassPerson2Id(): int
    {
        return $this->massPerson2->getId();
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

    public function getOriginalDistance(): ?int
    {
        return $this->originalDistance;
    }

    public function setOriginalDistance(?int $originalDistance): self
    {
        $this->originalDistance = $originalDistance;

        return $this;
    }

    public function getAcceptedDetourDistance(): ?int
    {
        return $this->acceptedDetourDistance;
    }

    public function setAcceptedDetourDistance(?int $acceptedDetourDistance): self
    {
        $this->acceptedDetourDistance = $acceptedDetourDistance;

        return $this;
    }

    public function getDetourDistance(): ?int
    {
        return $this->detourDistance;
    }

    public function setDetourDistance(?int $detourDistance): self
    {
        $this->detourDistance = $detourDistance;

        return $this;
    }

    public function getDetourDistancePercent(): ?float
    {
        return $this->detourDistancePercent;
    }

    public function setDetourDistancePercent(?float $detourDistancePercent): self
    {
        $this->detourDistancePercent = $detourDistancePercent;

        return $this;
    }

    public function getOriginalDuration(): ?int
    {
        return $this->originalDuration;
    }

    public function setOriginalDuration(?int $originalDuration): self
    {
        $this->originalDuration = $originalDuration;

        return $this;
    }

    public function getAcceptedDetourDuration(): ?int
    {
        return $this->acceptedDetourDuration;
    }

    public function setAcceptedDetourDuration(?int $acceptedDetourDuration): self
    {
        $this->acceptedDetourDuration = $acceptedDetourDuration;

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

    public function getDetourDuration(): ?int
    {
        return $this->detourDuration;
    }

    public function setDetourDuration(?int $detourDuration): self
    {
        $this->detourDuration = $detourDuration;

        return $this;
    }

    public function getDetourDurationPercent(): ?float
    {
        return $this->detourDurationPercent;
    }

    public function setDetourDurationPercent(?float $detourDurationPercent): self
    {
        $this->detourDurationPercent = $detourDurationPercent;

        return $this;
    }

    public function getCommonDistance(): ?int
    {
        return $this->commonDistance;
    }

    public function setCommonDistance(?int $commonDistance): self
    {
        $this->commonDistance = $commonDistance;

        return $this;
    }

    public function getPickUpDuration(): ?int
    {
        return $this->pickUpDuration;
    }

    public function setPickUpDuration(?int $pickUpDuration): self
    {
        $this->pickUpDuration = $pickUpDuration;

        return $this;
    }

    public function getDropOffDuration(): ?int
    {
        return $this->dropOffDuration;
    }

    public function setDropOffDuration(?int $dropOffDuration): self
    {
        $this->dropOffDuration = $dropOffDuration;

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

    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
