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
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      }
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
     * @var int The total duration of the direction in milliseconds.
     * corresponding to newDuration in classic carpool matching
     * @ORM\Column(type="integer")
     * @Groups({"mass","massCompute"})
     */
    private $duration;

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }
    
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        
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
