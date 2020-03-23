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
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_ERROR = 2;

    const ACTOR_DRIVER = 1;
    const ACTOR_PASSENGER = 2;

    /**
     * @var int The id of this proof.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int Proof status (0 = pending, 1 = sent to the register; 2 = error while sending to the register).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var Ask The ask related to the proof.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask", mappedBy="carpoolProof", cascade={"persist"})
     */
    private $ask;

    /**
     * @var \DateTimeInterface Passenger pickup certification date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pickUpPassengerDate;

    /**
     * @var \DateTimeInterface Driver pickup certification date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pickUpDriverDate;

    /**
     * @var \DateTimeInterface Passenger dropoff certification date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dropOffPassengerDate;

    /**
     * @var \DateTimeInterface Driver dropoff certification date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dropOffDriverDate;

    /**
     * @var Address Position of the passenger when pickup certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickUpPassengerAddress;

    /**
     * @var Address Position of the driver when pickup certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickUpDriverAddress;

    /**
     * @var Address Position of the passenger when dropoff certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropOffPassengerAddress;

    /**
     * @var Address Position of the driver when dropoff certification is asked.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropOffDriverAddress;

    /**
     * @var Direction|null Driver direction.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $directionDriver;

    /**
     * @var Direction|null Passenger direction.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $directionPassenger;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAsk(): ?Ask
    {
        return $this->ask;
    }

    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        // set the owning side
        $ask->setCarpoolProof($this);

        return $this;
    }

    public function getPickUpPassengerDate(): ?\DateTimeInterface
    {
        return $this->pickUpPassengerDate;
    }

    public function setPickUpPassengerDate(\DateTimeInterface $pickUpPassengerDate): self
    {
        $this->pickUpPassengerDate = $pickUpPassengerDate;

        return $this;
    }

    public function getPickUpDriverDate(): ?\DateTimeInterface
    {
        return $this->pickUpDriverDate;
    }

    public function setPickUpDriverDate(\DateTimeInterface $pickUpDriverDate): self
    {
        $this->pickUpDriverDate = $pickUpDriverDate;

        return $this;
    }

    public function getDropOffPassengerDate(): ?\DateTimeInterface
    {
        return $this->dropOffPassengerDate;
    }

    public function setDropOffPassengerDate(\DateTimeInterface $dropOffPassengerDate): self
    {
        $this->dropOffPassengerDate = $dropOffPassengerDate;

        return $this;
    }

    public function getDropOffDriverDate(): ?\DateTimeInterface
    {
        return $this->dropOffDriverDate;
    }

    public function setDropOffDriverDate(\DateTimeInterface $dropOffDriverDate): self
    {
        $this->dropOffDriverDate = $dropOffDriverDate;

        return $this;
    }
    
    public function getPickUpPassengerAddress(): ?Address
    {
        return $this->pickUpPassengerAddress;
    }

    public function setPickUpPassengerAddress(?Address $pickUpPassengerAddress): self
    {
        $this->pickUpPassengerAddress = $pickUpPassengerAddress;

        return $this;
    }

    public function getPickUpDriverAddress(): ?Address
    {
        return $this->pickUpDriverAddress;
    }

    public function setPickUpDriverAddress(?Address $pickUpDriverAddress): self
    {
        $this->pickUpDriverAddress = $pickUpDriverAddress;

        return $this;
    }

    public function getDropOffPassengerAddress(): ?Address
    {
        return $this->dropOffPassengerAddress;
    }

    public function setDropOffPassengerAddress(?Address $dropOffPassengerAddress): self
    {
        $this->dropOffPassengerAddress = $dropOffPassengerAddress;

        return $this;
    }

    public function getDropOffDriverAddress(): ?Address
    {
        return $this->dropOffDriverAddress;
    }

    public function setDropOffDriverAddress(?Address $dropOffDriverAddress): self
    {
        $this->dropOffDriverAddress = $dropOffDriverAddress;

        return $this;
    }

    public function getDirectionDriver(): ?Direction
    {
        return $this->directionDriver;
    }
    
    public function setDirectionDriver(?Direction $directionDriver): self
    {
        $this->directionDriver = $directionDriver;
        
        return $this;
    }
    
    public function getDirectionPassenger(): ?Direction
    {
        return $this->directionPassenger;
    }
    
    public function setDirectionPassenger(?Direction $directionPassenger): self
    {
        $this->directionPassenger = $directionPassenger;
        
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
     * Status.
     *
     * @ORM\PrePersist
     */
    public function setAutoStatus()
    {
        $this->setStatus(self::STATUS_PENDING);
    }

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
