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
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

/**
 * Carpooling : carpool proof.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolProof
{
    const STATUS_INITIATED = 0;     // not ready to be sent, proof still under construction
    const STATUS_PENDING = 1;       // ready to be sent
    const STATUS_SENT = 2;          // sent
    const STATUS_ERROR = 3;         // error during the sending
    const STATUS_CANCELED = 4;      // cancellation before sending

    const ACTOR_DRIVER = 1;
    const ACTOR_PASSENGER = 2;

    const TYPE_LOW = "A";
    const TYPE_MID = "B";
    const TYPE_HIGH = "C";
    const TYPE_UNDETERMINED_CLASSIC = "CX";
    const TYPE_UNDETERMINED_DYNAMIC = "DX";

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
     * @var string Register system proof type.
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $type;

    /**
     * @var Ask The ask related to the proof.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="carpoolProofs")
     */
    private $ask;

    /**
     * @var \DateTimeInterface Driver start date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDriverDate;

    /**
     * @var \DateTimeInterface Driver end date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDriverDate;

    /**
     * @var Address Origin of the driver.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originDriverAddress;

    /**
     * @var Address Destination of the driver.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $destinationDriverAddress;

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
     * @var Direction|null The direction related with the proof.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $direction;

    /**
     * @var string History of geographic points as a linestring, used to compute the direction. Updated at each new position. Can be emptied when the carpool is finished.
     * @ORM\Column(type="linestring", nullable=true)
     */
    private $geoJsonPoints;

    /**
     * @var User|null The driver, used to keep a link to the driver if the passenger deletes its ad (the ask may be deleted aswell).
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="carpoolProofsAsDriver")
     * @ORM\JoinColumn(nullable=true)
     */
    private $driver;

    /**
     * @var User|null The driver, used to keep a link to the driver if the passenger deletes its ad (the ask may be deleted aswell).
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="carpoolProofsAsPassenger")
     * @ORM\JoinColumn(nullable=true)
     */
    private $passenger;

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

    /**
     * @var array|null The array of points as Address objects. Used to create the geoJsonPoints.
     */
    private $points;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAsk(): ?Ask
    {
        return $this->ask;
    }

    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        if (!is_null($ask)) {
            // set the owning side
            $ask->addCarpoolProof($this);
        }
        
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

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }
    
    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }
    
    public function getGeoJsonPoints()
    {
        return $this->geoJsonPoints;
    }
    
    public function setGeoJsonPoints($geoJsonPoints): self
    {
        $this->geoJsonPoints = $geoJsonPoints;
        
        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPassenger(): ?User
    {
        return $this->passenger;
    }

    public function setPassenger(?User $passenger): self
    {
        $this->passenger = $passenger;

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

    public function getPoints(): ?array
    {
        return $this->points;
    }
    
    public function setPoints(array $points): self
    {
        $this->points = $points;
        
        return $this;
    }

    public function getStartDriverDate(): ?\DateTimeInterface
    {
        return $this->startDriverDate;
    }

    public function setStartDriverDate(\DateTimeInterface $startDriverDate): self
    {
        $this->startDriverDate = $startDriverDate;

        return $this;
    }

    public function getEndDriverDate(): ?\DateTimeInterface
    {
        return $this->endDriverDate;
    }

    public function setEndDriverDate(\DateTimeInterface $endDriverDate): self
    {
        $this->endDriverDate = $endDriverDate;

        return $this;
    }

    public function getOriginDriverAddress(): ?Address
    {
        return $this->originDriverAddress;
    }

    public function setOriginDriverAddress(?Address $originDriverAddress): self
    {
        $this->originDriverAddress = $originDriverAddress;

        return $this;
    }

    public function getDestinationDriverAddress(): ?Address
    {
        return $this->destinationDriverAddress;
    }

    public function setDestinationDriverAddress(?Address $destinationDriverAddress): self
    {
        $this->destinationDriverAddress = $destinationDriverAddress;

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
        if (is_null($this->getStatus())) {
            $this->setStatus(self::STATUS_INITIATED);
        }
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

    /**
     * GeoJson representation of the points.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAutoGeoJsonPoints()
    {
        if (!is_null($this->getPoints())) {
            $arrayPoints = [];
            foreach ($this->getPoints() as $address) {
                $arrayPoints[] = new Point($address->getLongitude(), $address->getLatitude());
            }
            $this->setGeoJsonPoints(new LineString($arrayPoints));
        }
    }
}
