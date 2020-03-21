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

namespace App\Carpool\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : carpool proof.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolProof
{
    /**
     * @var int The id of this proof.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Ask The ask related to the proof.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask", mappedBy="carpoolProof")
     */
    private $ask;
        
    /**
     * @var Address Position of the passenger when pickup certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickupPassengerAddress;

    /**
     * @var Address Position of the driver when pickup certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickupDriverAddress;

    /**
     * @var Address Position of the passenger when dropoff certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropoffPassengerAddress;

    /**
     * @var Address Position of the driver when dropoff certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropoffDriverAddress;

    /**
     * @var Direction|null Direction related to the dynamic carpool - updated at each position update.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $direction;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getPickupPassengerAddress(): ?Address
    {
        return $this->pickupPassengerAddress;
    }

    public function setPickupPassengerAddress(?Address $pickupPassengerAddress): self
    {
        $this->pickupPassengerAddress = $pickupPassengerAddress;

        return $this;
    }

    public function getPickupDriverAddress(): ?Address
    {
        return $this->pickupDriverAddress;
    }

    public function setPickupDriverAddress(?Address $pickupDriverAddress): self
    {
        $this->pickupDriverAddress = $pickupDriverAddress;

        return $this;
    }

    public function getDropoffPassengerAddress(): ?Address
    {
        return $this->dropoffPassengerAddress;
    }

    public function setDropoffPassengerAddress(?Address $dropoffPassengerAddress): self
    {
        $this->dropoffPassengerAddress = $dropoffPassengerAddress;

        return $this;
    }

    public function getDropoffDriverAddress(): ?Address
    {
        return $this->dropoffDriverAddress;
    }

    public function setDropoffDriverAddress(?Address $dropoffDriverAddress): self
    {
        $this->dropoffDriverAddress = $dropoffDriverAddress;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }
    
    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;
        
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
