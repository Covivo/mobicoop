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
 */

namespace App\Match\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A public transport journey of a Mass Person.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassPTJourney
{
    /**
     * @var int the id of this journey
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MassPerson The mass person linked to this Journey
     * @ORM\ManyToOne(targetEntity="\App\Match\Entity\MassPerson", cascade={"persist"}, inversedBy="massPTJourneys")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $massPerson;

    /**
     * @var int the total distance of this journey
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distance;

    /**
     * @var int the total duration of this journey (in seconds)
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $duration;

    /**
     * @var int the number of changes of this journey
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $changeNumber;

    /**
     * @var int The distance from home of this journey
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distanceWalkFromHome;

    /**
     * @var int The duration from home of this journey (in seconds)
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $durationWalkFromHome;

    /**
     * @var int The distance from work of this journey
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distanceWalkFromWork;

    /**
     * @var int The duration from work of this journey (in seconds)
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $durationWalkFromWork;

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
     * @var string PT provider used to compute this journey
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("pt")
     */
    private $provider;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMassPerson(): ?MassPerson
    {
        return $this->massPerson;
    }

    public function setMassPerson(?MassPerson $massPerson): self
    {
        $this->massPerson = $massPerson;

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

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;

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
