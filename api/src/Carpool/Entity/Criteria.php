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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Entity\Car;
use App\Geography\Entity\Direction;
use App\PublicTransport\Entity\PTJourney;

/**
 * Carpooling : criteria (restriction for an offer / selection for a request).
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get"}
 * )
 */
class Criteria
{
    const DEFAULT_ID = 999999999999;
    
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;
    
    /**
     * @var int The id of this criteria.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var boolean The user can be a driver.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $driver;

    /**
     * @var boolean The user can be a passenger.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $passenger;
    
    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","results","write","thread","threads"})
     */
    private $frequency;

    /**
     * @var int The number of available seats.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     * @Groups({"read","results","write","thread"})
     */
    private $seats;

    /**
     * @var \DateTimeInterface The starting date (= proposal date if punctual).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="date")
     * @Groups({"read","results","write","thread","threads"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The starting time.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $fromTime;

    /**
     * @var \DateTimeInterface|null The min starting time if punctual.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread","threads"})
     */
    private $minTime;

    /**
     * @var \DateTimeInterface|null The max starting time if punctual.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread","threads"})
     */
    private $maxTime;

    /**
     * @var int Accepted margin duration for punctual proposal in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $marginDuration;

    /**
     * @var boolean For punctual proposals, the user accepts only matchings for the defined date (no ranges).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $strictDate;

    /**
     * @var boolean For punctual proposals, the user accepts only matchings with punctual trips (no regular trips).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $strictPunctual;

    /**
     * @var boolean For regular proposals, the user accepts only matchings with regular trips (no punctual trips).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $strictRegular;

    /**
     * @var \DateTimeInterface|null The end date if regular proposal, the last accepted date if punctual.
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $toDate;

    /**
     * @var boolean|null The proposal is available on mondays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $monCheck;

    /**
     * @var boolean|null The proposal is available on tuesdays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $tueCheck;

    /**
     * @var boolean|null The proposal is available on wednesdays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $wedCheck;

    /**
     * @var boolean|null The proposal is available on thursdays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $thuCheck;

    /**
     * @var boolean|null The proposal is available on fridays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $friCheck;

    /**
     * @var boolean|null The proposal is available on saturdays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $satCheck;

    /**
     * @var boolean|null The proposal is available on sundays (if regular).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","results","write","thread","threads"})
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null Mondays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $monTime;

    /**
     * @var \DateTimeInterface|null Mondays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $monMinTime;

    /**
     * @var \DateTimeInterface|null Mondays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $monMaxTime;

    /**
     * @var \DateTimeInterface|null Tuesdays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $tueTime;

    /**
     * @var \DateTimeInterface|null Tuesdays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $tueMinTime;

    /**
     * @var \DateTimeInterface|null Tuesdays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $tueMaxTime;

    /**
     * @var \DateTimeInterface|null Wednesdays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $wedTime;

    /**
     * @var \DateTimeInterface|null Wednesdays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $wedMinTime;

    /**
     * @var \DateTimeInterface|null Wednesdays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $wedMaxTime;

    /**
     * @var \DateTimeInterface|null Thursdays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $thuTime;

    /**
     * @var \DateTimeInterface|null Thursdays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $thuMinTime;

    /**
     * @var \DateTimeInterface|null Thursdays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $thuMaxTime;

    /**
     * @var \DateTimeInterface|null Fridays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $friTime;

    /**
     * @var \DateTimeInterface|null Fridays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $friMinTime;

    /**
     * @var \DateTimeInterface|null Fridays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $friMaxTime;

    /**
     * @var \DateTimeInterface|null Saturdays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $satTime;

    /**
     * @var \DateTimeInterface|null Saturdays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $satMinTime;

    /**
     * @var \DateTimeInterface|null Saturdays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $satMaxTime;

    /**
     * @var \DateTimeInterface|null Sundays starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","results","write","thread"})
     */
    private $sunTime;

    /**
     * @var \DateTimeInterface|null Sundays min starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $sunMinTime;

    /**
     * @var \DateTimeInterface|null Sundays max starting time (if regular).
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $sunMaxTime;

    /**
     * @var int Accepted margin for monday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $monMarginDuration;

    /**
     * @var int Accepted margin for tuesday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $tueMarginDuration;
    
    /**
     * @var int Accepted margin for wednesday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $wedMarginDuration;
    
    /**
     * @var int Accepted margin for thursday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $thuMarginDuration;
    
    /**
     * @var int Accepted margin for friday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $friMarginDuration;
    
    /**
     * @var int Accepted margin for saturday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $satMarginDuration;
    
    /**
     * @var int Accepted margin for sunday starting time in seconds.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $sunMarginDuration;
    
    /**
     * @var int|null The maximum detour duration (in milliseconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $maxDetourDuration;
    
    /**
     * @var int|null The maximum detour distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $maxDetourDistance;
    
    /**
     * @var boolean The user accepts any route as a passenger from its origin to the destination.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $anyRouteAsPassenger;
    
    /**
     * @var boolean|null The user accepts any transportation mode.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $multiTransportMode;
    
    /**
    * @var float|null The price per km.
    *
    * @ORM\Column(type="decimal", precision=4, scale=2, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $priceKm;

    /**
    * @var float|null The price for the whole journey (usually, the rounded (priceKm * distance)).
    *
    * @ORM\Column(type="decimal", precision=4, scale=2, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $price;

    /**
     * @var boolean Big luggage accepted / asked.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $luggage;

    /**
     * @var boolean Bike accepted / asked.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $bike;

    /**
     * @var boolean 2 passengers max on the back seats.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $backSeats;

    /**
     * @var boolean Solidary request.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $solidary;

    /**
     * @var boolean Solidary exclusive.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $solidaryExclusive;

    /**
     * @var Car|null The car used in the journey.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\Car")
     * @Groups({"read","write","thread"})
     * @MaxDepth(1)
     */
    private $car;
    
    /**
     * @var Direction|null The direction used in the journey as a driver.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"})
     * @Groups({"read","results"})
     */
    private $directionDriver;
    
    /**
     * @var Direction|null The direction used in the journey as a passenger.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"})
     * @Groups({"read","results","thread"})
     */
    private $directionPassenger;
    
    /**
     * @var PTJourney|null The public transport journey used.
     *
     * @ORM\ManyToOne(targetEntity="\App\PublicTransport\Entity\PTJourney")
     * @Groups({"read","write"})
     */
    private $ptjourney;

    /**
     * @var Proposal The proposal that uses this criteria.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="criteria")
     * @Groups({"read","write"})
     */
    private $proposal;

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

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function isDriver(): ?bool
    {
        return $this->driver;
    }
    
    public function setDriver(bool $isDriver): self
    {
        $this->driver = $isDriver;
        
        return $this;
    }
    
    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }
    
    public function setPassenger(bool $isPassenger): self
    {
        $this->passenger = $isPassenger;
        
        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getFromTime(): ?\DateTimeInterface
    {
        if ($this->fromTime) {
            return \DateTime::createFromFormat('His', $this->fromTime->format('His'));
        }
        return null;
    }

    public function setFromTime(?\DateTimeInterface $fromTime): self
    {
        $this->fromTime = $fromTime;

        return $this;
    }


    public function getMarginDuration(): ?int
    {
        return $this->marginDuration;
    }

    public function setMarginDuration(?int $marginDuration): self
    {
        $this->marginDuration = $marginDuration;

        return $this;
    }

    public function getMinTime(): ?\DateTimeInterface
    {
        if ($this->minTime) {
            return \DateTime::createFromFormat('His', $this->minTime->format('His'));
        }
        return null;
    }

    public function setMinTime(?\DateTimeInterface $minTime): self
    {
        $this->minTime = $minTime;

        return $this;
    }

    public function getMaxTime(): ?\DateTimeInterface
    {
        if ($this->maxTime) {
            return \DateTime::createFromFormat('His', $this->maxTime->format('His'));
        }
        return null;
    }

    public function setMaxTime(?\DateTimeInterface $maxTime): self
    {
        $this->maxTime = $maxTime;

        return $this;
    }

    public function isStrictDate(): ?bool
    {
        return $this->strictDate;
    }
    
    public function setStrictDate(?bool $isStrictDate): self
    {
        $this->strictDate = $isStrictDate;
        
        return $this;
    }

    public function isStrictPunctual(): ?bool
    {
        return $this->strictPunctual;
    }
    
    public function setStrictPunctual(?bool $isStrictPunctual): self
    {
        $this->strictPunctual = $isStrictPunctual;
        
        return $this;
    }

    public function isStrictRegular(): ?bool
    {
        return $this->strictRegular;
    }
    
    public function setStrictRegular(?bool $isStrictRegular): self
    {
        $this->strictRegular = $isStrictRegular;
        
        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function isMonCheck(): ?bool
    {
        return $this->monCheck;
    }

    public function setMonCheck(?bool $monCheck): self
    {
        $this->monCheck = $monCheck;

        return $this;
    }

    public function isTueCheck(): ?bool
    {
        return $this->tueCheck;
    }

    public function setTueCheck(?bool $tueCheck): self
    {
        $this->tueCheck = $tueCheck;

        return $this;
    }

    public function isWedCheck(): ?bool
    {
        return $this->wedCheck;
    }

    public function setWedCheck(?bool $wedCheck): self
    {
        $this->wedCheck = $wedCheck;

        return $this;
    }

    public function isThuCheck(): ?bool
    {
        return $this->thuCheck;
    }

    public function setThuCheck(?bool $thuCheck): self
    {
        $this->thuCheck = $thuCheck;

        return $this;
    }

    public function isFriCheck(): ?bool
    {
        return $this->friCheck;
    }

    public function setFriCheck(?bool $friCheck): self
    {
        $this->friCheck = $friCheck;

        return $this;
    }

    public function isSatCheck(): ?bool
    {
        return $this->satCheck;
    }

    public function setSatCheck(?bool $satCheck): self
    {
        $this->satCheck = $satCheck;

        return $this;
    }

    public function isSunCheck(): ?bool
    {
        return $this->sunCheck;
    }

    public function setSunCheck(?bool $sunCheck): self
    {
        $this->sunCheck = $sunCheck;

        return $this;
    }

    public function getMonTime(): ?\DateTimeInterface
    {
        if ($this->monTime) {
            return \DateTime::createFromFormat('His', $this->monTime->format('His'));
        }
        return null;
    }

    public function setMonTime(?\DateTimeInterface $monTime): self
    {
        $this->monTime = $monTime;

        return $this;
    }

    public function getMonMinTime(): ?\DateTimeInterface
    {
        if ($this->monMinTime) {
            return \DateTime::createFromFormat('His', $this->monMinTime->format('His'));
        }
        return null;
    }

    public function setMonMinTime(?\DateTimeInterface $monMinTime): self
    {
        $this->monMinTime = $monMinTime;

        return $this;
    }

    public function getMonMaxTime(): ?\DateTimeInterface
    {
        if ($this->monMaxTime) {
            return \DateTime::createFromFormat('His', $this->monMaxTime->format('His'));
        }
        return null;
    }

    public function setMonMaxTime(?\DateTimeInterface $monMaxTime): self
    {
        $this->monMaxTime = $monMaxTime;

        return $this;
    }

    public function getTueTime(): ?\DateTimeInterface
    {
        if ($this->tueTime) {
            return \DateTime::createFromFormat('His', $this->tueTime->format('His'));
        }
        return null;
    }

    public function setTueTime(?\DateTimeInterface $tueTime): self
    {
        $this->tueTime = $tueTime;

        return $this;
    }

    public function getTueMinTime(): ?\DateTimeInterface
    {
        if ($this->tueMinTime) {
            return \DateTime::createFromFormat('His', $this->tueMinTime->format('His'));
        }
        return null;
    }

    public function setTueMinTime(?\DateTimeInterface $tueMinTime): self
    {
        $this->tueMinTime = $tueMinTime;

        return $this;
    }

    public function getTueMaxTime(): ?\DateTimeInterface
    {
        if ($this->tueMaxTime) {
            return \DateTime::createFromFormat('His', $this->tueMaxTime->format('His'));
        }
        return null;
    }

    public function setTueMaxTime(?\DateTimeInterface $tueMaxTime): self
    {
        $this->tueMaxTime = $tueMaxTime;

        return $this;
    }

    public function getWedTime(): ?\DateTimeInterface
    {
        if ($this->wedTime) {
            return \DateTime::createFromFormat('His', $this->wedTime->format('His'));
        }
        return null;
    }

    public function setWedTime(?\DateTimeInterface $wedTime): self
    {
        $this->wedTime = $wedTime;

        return $this;
    }

    public function getWedMinTime(): ?\DateTimeInterface
    {
        if ($this->wedMinTime) {
            return \DateTime::createFromFormat('His', $this->wedMinTime->format('His'));
        }
        return null;
    }

    public function setWedMinTime(?\DateTimeInterface $wedMinTime): self
    {
        $this->wedMinTime = $wedMinTime;

        return $this;
    }

    public function getWedMaxTime(): ?\DateTimeInterface
    {
        if ($this->wedMaxTime) {
            return \DateTime::createFromFormat('His', $this->wedMaxTime->format('His'));
        }
        return null;
    }

    public function setWedMaxTime(?\DateTimeInterface $wedMaxTime): self
    {
        $this->wedMaxTime = $wedMaxTime;

        return $this;
    }

    public function getThuTime(): ?\DateTimeInterface
    {
        if ($this->thuTime) {
            return \DateTime::createFromFormat('His', $this->thuTime->format('His'));
        }
        return null;
    }

    public function setThuTime(?\DateTimeInterface $thuTime): self
    {
        $this->thuTime = $thuTime;

        return $this;
    }

    public function getThuMinTime(): ?\DateTimeInterface
    {
        if ($this->thuMinTime) {
            return \DateTime::createFromFormat('His', $this->thuMinTime->format('His'));
        }
        return null;
    }

    public function setThuMinTime(?\DateTimeInterface $thuMinTime): self
    {
        $this->thuMinTime = $thuMinTime;

        return $this;
    }

    public function getThuMaxTime(): ?\DateTimeInterface
    {
        if ($this->thuMaxTime) {
            return \DateTime::createFromFormat('His', $this->thuMaxTime->format('His'));
        }
        return null;
    }

    public function setThuMaxTime(?\DateTimeInterface $thuMaxTime): self
    {
        $this->thuMaxTime = $thuMaxTime;

        return $this;
    }

    public function getFriTime(): ?\DateTimeInterface
    {
        if ($this->friTime) {
            return \DateTime::createFromFormat('His', $this->friTime->format('His'));
        }
        return null;
    }

    public function setFriTime(?\DateTimeInterface $friTime): self
    {
        $this->friTime = $friTime;

        return $this;
    }

    public function getFriMinTime(): ?\DateTimeInterface
    {
        if ($this->friMinTime) {
            return \DateTime::createFromFormat('His', $this->friMinTime->format('His'));
        }
        return null;
    }

    public function setFriMinTime(?\DateTimeInterface $friMinTime): self
    {
        $this->friMinTime = $friMinTime;

        return $this;
    }

    public function getFriMaxTime(): ?\DateTimeInterface
    {
        if ($this->friMaxTime) {
            return \DateTime::createFromFormat('His', $this->friMaxTime->format('His'));
        }
        return null;
    }

    public function setFriMaxTime(?\DateTimeInterface $friMaxTime): self
    {
        $this->friMaxTime = $friMaxTime;

        return $this;
    }

    public function getSatTime(): ?\DateTimeInterface
    {
        if ($this->satTime) {
            return \DateTime::createFromFormat('His', $this->satTime->format('His'));
        }
        return null;
    }

    public function setSatTime(?\DateTimeInterface $satTime): self
    {
        $this->satTime = $satTime;

        return $this;
    }

    public function getSatMinTime(): ?\DateTimeInterface
    {
        if ($this->satMinTime) {
            return \DateTime::createFromFormat('His', $this->satMinTime->format('His'));
        }
        return null;
    }

    public function setSatMinTime(?\DateTimeInterface $satMinTime): self
    {
        $this->satMinTime = $satMinTime;

        return $this;
    }

    public function getSatMaxTime(): ?\DateTimeInterface
    {
        if ($this->satMaxTime) {
            return \DateTime::createFromFormat('His', $this->satMaxTime->format('His'));
        }
        return null;
    }

    public function setSatMaxTime(?\DateTimeInterface $satMaxTime): self
    {
        $this->satMaxTime = $satMaxTime;

        return $this;
    }

    public function getSunTime(): ?\DateTimeInterface
    {
        if ($this->sunTime) {
            return \DateTime::createFromFormat('His', $this->sunTime->format('His'));
        }
        return null;
    }
    
    public function setSunTime(?\DateTimeInterface $sunTime): self
    {
        $this->sunTime = $sunTime;
        
        return $this;
    }

    public function getSunMinTime(): ?\DateTimeInterface
    {
        if ($this->sunMinTime) {
            return \DateTime::createFromFormat('His', $this->sunMinTime->format('His'));
        }
        return null;
    }

    public function setSunMinTime(?\DateTimeInterface $sunMinTime): self
    {
        $this->sunMinTime = $sunMinTime;

        return $this;
    }

    public function getSunMaxTime(): ?\DateTimeInterface
    {
        if ($this->sunMaxTime) {
            return \DateTime::createFromFormat('His', $this->sunMaxTime->format('His'));
        }
        return null;
    }

    public function setSunMaxTime(?\DateTimeInterface $sunMaxTime): self
    {
        $this->sunMaxTime = $sunMaxTime;

        return $this;
    }

    public function getMonMarginDuration(): ?int
    {
        return $this->monMarginDuration;
    }

    public function setMonMarginDuration(?int $monMarginDuration): self
    {
        $this->monMarginDuration = $monMarginDuration;

        return $this;
    }
    
    public function getTueMarginDuration(): ?int
    {
        return $this->tueMarginDuration;
    }
    
    public function setTueMarginDuration(?int $tueMarginDuration): self
    {
        $this->tueMarginDuration = $tueMarginDuration;
        
        return $this;
    }
    
    public function getWedMarginDuration(): ?int
    {
        return $this->wedMarginDuration;
    }
    
    public function setWedMarginDuration(?int $wedMarginDuration): self
    {
        $this->wedMarginDuration = $wedMarginDuration;
        
        return $this;
    }
    
    public function getThuMarginDuration(): ?int
    {
        return $this->thuMarginDuration;
    }
    
    public function setThuMarginDuration(?int $thuMarginDuration): self
    {
        $this->thuMarginDuration = $thuMarginDuration;
        
        return $this;
    }
    
    public function getFriMarginDuration(): ?int
    {
        return $this->friMarginDuration;
    }
    
    public function setFriMarginDuration(?int $friMarginDuration): self
    {
        $this->friMarginDuration = $friMarginDuration;
        
        return $this;
    }
    
    public function getSatMarginDuration(): ?int
    {
        return $this->satMarginDuration;
    }
    
    public function setSatMarginDuration(?int $satMarginDuration): self
    {
        $this->satMarginDuration = $satMarginDuration;
        
        return $this;
    }
    
    public function getSunMarginDuration(): ?int
    {
        return $this->sunMarginDuration;
    }
    
    public function setSunMarginDuration(?int $sunMarginDuration): self
    {
        $this->sunMarginDuration = $sunMarginDuration;
        
        return $this;
    }
    
    public function getMaxDetourDistance(): ?int
    {
        return $this->maxDetourDistance;
    }
    
    public function setMaxDetourDistance(?int $maxDetourDistance): self
    {
        $this->maxDetourDistance = $maxDetourDistance;
        
        return $this;
    }

    public function getMaxDetourDuration(): ?int
    {
        return $this->maxDetourDuration;
    }

    public function setMaxDetourDuration(?int $maxDetourDuration): self
    {
        $this->maxDetourDuration = $maxDetourDuration;
        
        return $this;
    }
    
    public function getAnyRouteAsPassenger(): ?bool
    {
        return $this->anyRouteAsPassenger;
    }
    
    public function setAnyRouteAsPassenger(?bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;
        
        return $this;
    }
    
    public function getMultiTransportMode(): ?bool
    {
        return (!is_null($this->multiTransportMode) ? $this->multiTransportMode : true);
    }
    
    public function setMultiTransportMode(?bool $multiTransportMode): self
    {
        $this->multiTransportMode = $multiTransportMode;
        
        return $this;
    }
    
    public function getPriceKm(): ?string
    {
        return $this->priceKm;
    }
    
    public function setPriceKm(?string $priceKm)
    {
        $this->priceKm = $priceKm;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }
    
    public function setPrice(?string $price)
    {
        $this->price = $price;
    }

    public function hasLuggage(): ?bool
    {
        return $this->luggage;
    }
    
    public function setLuggage(?bool $hasLuggage): self
    {
        $this->luggage = $hasLuggage;
        
        return $this;
    }

    public function hasBike(): ?bool
    {
        return $this->bike;
    }
    
    public function setBike(?bool $hasBike): self
    {
        $this->bike = $hasBike;
        
        return $this;
    }

    public function hasBackSeats(): ?bool
    {
        return $this->backSeats;
    }
    
    public function setBackSeats(?bool $hasBackSeats): self
    {
        $this->backSeats = $hasBackSeats;
        
        return $this;
    }

    public function isSolidary(): ?bool
    {
        return $this->solidary;
    }
    
    public function setSolidary(?bool $isSolidary): self
    {
        $this->solidary = $isSolidary;
        
        return $this;
    }

    public function isSolidaryExclusive(): ?bool
    {
        return $this->solidaryExclusive;
    }
    
    public function setSolidaryExclusive(?bool $isSolidaryExclusive): self
    {
        $this->solidaryExclusive = $isSolidaryExclusive;
        
        return $this;
    }
    
    public function getCar(): ?Car
    {
        return $this->car;
    }
    
    public function setCar(?Car $car): self
    {
        $this->car = $car;
        
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
    
    public function getPTJourney(): ?PTJourney
    {
        return $this->ptjourney;
    }
    
    public function setPTJourney(?PTJourney $ptjourney): self
    {
        $this->ptjourney = $ptjourney;
        
        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }
    
    public function clone(Criteria $criteria)
    {
        // for now we just clone frequency, seats, fromDate, fromTime and toDate
        $this->setFrequency($criteria->getFrequency());
        $this->setSeats($criteria->getSeats());
        $this->setPriceKm($criteria->getPriceKm());
        $this->setFromDate($criteria->getFromDate());
        $this->setFromTime($criteria->getFromTime());
        $this->setToDate($criteria->getToDate());
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
