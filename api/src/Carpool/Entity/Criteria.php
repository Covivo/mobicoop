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
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryMatching;

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
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *       }
 * )
 */
class Criteria
{
    const DEFAULT_ID = 999999999999;
    
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;
    const FREQUENCY_FLEXIBLE = 3; // only for solidary records, not stored

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
     * @var int The number of available seats for a driver.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     * @Groups({"read","results","write","thread"})
     */
    private $seatsDriver;

    /**
     * @var int The number of requested seats for a passenger.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     * @Groups({"read","results","write","thread"})
     */
    private $seatsPassenger;

    /**
     * @var \DateTimeInterface The starting date (= proposal date if punctual).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="date")
     * @Groups({"read","results","write","thread","threads"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The arrival date if punctual
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalDateTime;

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
     * @var \DateTimeInterface The arrival time on Mondays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalMonTime;

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
     * @var \DateTimeInterface The arrival time on Tuesdays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalTueTime;

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
     * @var \DateTimeInterface The arrival time on Wednesdays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalWedTime;

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
     * @var \DateTimeInterface The arrival time on Thursdays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalThuTime;

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
     * @var \DateTimeInterface The arrival time on Friday
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalFriTime;

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
     * @var \DateTimeInterface The arrival time on Saturdays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalSatTime;

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
     * @var \DateTimeInterface The arrival time on Sundays
     * @Groups({"read","results","write","thread","threads"})
     */
    private $arrivalSunTime;

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
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $priceKm;

    /**
    * @var float|null The total price selected by the user as a driver.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $driverPrice;

    /**
    * @var float|null The total price computed by the system, using the user price per km, not rounded, as a driver.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $driverComputedPrice;

    /**
    * @var float|null The driver computed price rounded using the rounding rules.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $driverComputedRoundedPrice;

    /**
    * @var float|null The driver master price to use. It's the price if it's not null, otherwise the computedPrice.
    * @Groups({"read","results","thread"})
    */
    private $driverMasterPrice;

    /**
    * @var float|null The total price selected by the user as a passenger.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $passengerPrice;

    /**
    * @var float|null The total price computed by the system, using the user price per km, not rounded, as a passenger.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $passengerComputedPrice;

    /**
    * @var float|null The passenger computed price rounded using the rounding rules.
    *
    * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
    * @Groups({"read","results","write","thread"})
    */
    private $passengerComputedRoundedPrice;

    /**
    * @var float|null The passenger master price to use. It's the price if it's not null, otherwise the computedPrice.
    * @Groups({"read","results","thread"})
    */
    private $passengerMasterPrice;

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
     * @var SolidaryAsk The SolidaryAsk that uses this criteria.
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryAsk", mappedBy="criteria")
     * @Groups({"read","write"})
     */
    private $solidaryAsk;

    /**
     * @var SolidaryMatching The SolidaryMatching that uses this criteria.
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="criteria")
     * @Groups({"read","write"})
     */
    private $solidaryMatching;

    /**
     * @var boolean Avoid motorway.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $avoidMotorway;

    /**
     * @var boolean Avoid toll.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write","thread"})
     */
    private $avoidToll;

    /**
     * @var Car|null The car used in the journey.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\Car")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"read","write","thread"})
     * @MaxDepth(1)
     */
    private $car;
    
    /**
     * @var Direction|null The direction used in the journey as a driver.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", inversedBy="criteriaDrivers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"read","results"})
     */
    private $directionDriver;
    
    /**
     * @var Direction|null The direction used in the journey as a passenger.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", inversedBy="criteriaPassengers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"read","results","thread"})
     */
    private $directionPassenger;
    
    /**
     * @var int Journey's Duration in secondes based on DirectionDriver if it exists or else on DirectionPassenger
     * @Groups({"read","results"})
     */
    private $duration;
    
    /**
     * @var PTJourney|null The public transport journey used.
     *
     * @ORM\ManyToOne(targetEntity="\App\PublicTransport\Entity\PTJourney")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
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

    public function getSeatsDriver(): ?int
    {
        return $this->seatsDriver;
    }

    public function setSeatsDriver(int $seatsDriver): self
    {
        $this->seatsDriver = $seatsDriver;

        return $this;
    }

    public function getSeatsPassenger(): ?int
    {
        return $this->seatsPassenger;
    }

    public function setSeatsPassenger(int $seatsPassenger): self
    {
        $this->seatsPassenger = $seatsPassenger;

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

    public function getArrivalDateTime(): ?\DateTimeInterface
    {
        if ($this->getFrequency()=== Criteria::FREQUENCY_PUNCTUAL) {
            $fromDate = clone $this->getFromDate();
            $fromTime = $this->getFromTime();
            $fromDate->setTime($fromTime->format("H"), $fromTime->format("i"), $fromTime->format("s"));

            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $fromDate->modify($duration." seconds");
            }
            return null;
        }
        return null;
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

    public function getArrivalMonTime(): ?\DateTimeInterface
    {
        if ($this->monTime) {
            $monTime = clone $this->getMonTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $monTime->modify($duration." seconds");
            }
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

    public function getArrivalTueTime(): ?\DateTimeInterface
    {
        if ($this->tueTime) {
            $tueTime = clone $this->getTueTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $tueTime->modify($duration." seconds");
            }
        }
        return null;
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

    public function getArrivalWedTime(): ?\DateTimeInterface
    {
        if ($this->wedTime) {
            $wedTime = clone $this->getWedTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $wedTime->modify($duration." seconds");
            }
        }
        return null;
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

    public function getArrivalThuTime(): ?\DateTimeInterface
    {
        if ($this->thuTime) {
            $thuTime = clone $this->getThuTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $thuTime->modify($duration." seconds");
            }
        }
        return null;
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

    public function getArrivalFriTime(): ?\DateTimeInterface
    {
        if ($this->friTime) {
            $friTime = clone $this->getFriTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $friTime->modify($duration." seconds");
            }
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

    public function getArrivalSatTime(): ?\DateTimeInterface
    {
        if ($this->satTime) {
            $satTime = clone $this->getSatTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $satTime->modify($duration." seconds");
            }
        }
        return null;
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

    public function getArrivalSunTime(): ?\DateTimeInterface
    {
        if ($this->sunTime) {
            $sunTime = clone $this->getSunTime();
            $duration = $this->getDuration();
            if (!is_null($duration)) {
                return $sunTime->modify($duration." seconds");
            }
        }
        return null;
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

    public function getDriverPrice(): ?string
    {
        return $this->driverPrice;
    }
    
    public function setDriverPrice(?string $driverPrice)
    {
        $this->driverPrice = $driverPrice;
    }

    public function getDriverComputedPrice(): ?string
    {
        return $this->driverComputedPrice;
    }
    
    public function setDriverComputedPrice(?string $driverComputedPrice)
    {
        $this->driverComputedPrice = $driverComputedPrice;
    }

    public function getDriverComputedRoundedPrice(): ?string
    {
        return round($this->driverComputedRoundedPrice, 2);
    }
    
    public function setDriverComputedRoundedPrice(?string $driverComputedRoundedPrice)
    {
        $this->driverComputedRoundedPrice = $driverComputedRoundedPrice;
    }

    public function getDriverMasterPrice(): ?string
    {
        if (!is_null($this->getDriverPrice())) {
            $this->driverMasterPrice = $this->getDriverPrice();
        } elseif (!is_null($this->getDriverComputedRoundedPrice())) {
            $this->driverMasterPrice = $this->getDriverComputedRoundedPrice();
        }
        return $this->driverMasterPrice;
    }

    public function getPassengerPrice(): ?string
    {
        return $this->passengerPrice;
    }
    
    public function setPassengerPrice(?string $passengerPrice)
    {
        $this->passengerPrice = $passengerPrice;
    }

    public function getPassengerComputedPrice(): ?string
    {
        return $this->passengerComputedPrice;
    }
    
    public function setPassengerComputedPrice(?string $passengerComputedPrice)
    {
        $this->passengerComputedPrice = $passengerComputedPrice;
    }

    public function getPassengerComputedRoundedPrice(): ?string
    {
        return round($this->passengerComputedRoundedPrice, 2);
    }
    
    public function setPassengerComputedRoundedPrice(?string $passengerComputedRoundedPrice)
    {
        $this->passengerComputedRoundedPrice = $passengerComputedRoundedPrice;
    }

    public function getPassengerMasterPrice(): ?string
    {
        if (!is_null($this->getPassengerPrice())) {
            $this->passengerMasterPrice = $this->getPassengerPrice();
        } elseif (!is_null($this->getPassengerComputedRoundedPrice())) {
            $this->passengerMasterPrice = $this->getPassengerComputedRoundedPrice();
        }
        return $this->passengerMasterPrice;
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

    public function getSolidaryAsk(): ?SolidaryAsk
    {
        return $this->solidaryAsk;
    }
    
    public function setSolidaryAsk(?SolidaryAsk $solidaryAsk): self
    {
        $this->solidaryAsk = $solidaryAsk;
        
        return $this;
    }

    public function getSolidaryMatching(): ?SolidaryMatching
    {
        return $this->solidaryMatching;
    }
    
    public function setSolidaryMatching(?SolidaryMatching $solidaryMatching): self
    {
        $this->solidaryMatching = $solidaryMatching;
        
        return $this;
    }

    public function avoidMotorway(): ?bool
    {
        return $this->avoidMotorway;
    }
    
    public function setAvoidMotorway(?bool $avoidMotorway): self
    {
        $this->avoidMotorway = $avoidMotorway;
        
        return $this;
    }

    public function avoidToll(): ?bool
    {
        return $this->avoidToll;
    }
    
    public function setAvoidToll(?bool $avoidToll): self
    {
        $this->avoidToll = $avoidToll;
        
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
        // set the reverse side, useful for direction managing
        if (!$directionDriver->getCriteriaDrivers(false)->contains($this)) {
            $directionDriver->addCriteriaDriver($this);
        }
        
        return $this;
    }
    
    public function getDirectionPassenger(): ?Direction
    {
        return $this->directionPassenger;
    }
    
    public function setDirectionPassenger(?Direction $directionPassenger): self
    {
        $this->directionPassenger = $directionPassenger;
        // set the reverse side, useful for direction managing
        if (!$directionPassenger->getCriteriaPassengers(false)->contains($this)) {
            $directionPassenger->addCriteriaPassenger($this);
        }
        
        return $this;
    }
    
    public function getDuration(): ?int
    {
        if (!is_null($this->getDirectionDriver())) {
            return $this->getDirectionDriver()->getDuration();
        } elseif (!is_null($this->getDirectionPassenger())) {
            return $this->getDirectionPassenger()->getDuration();
        }

        return null;
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
        $this->setSeatsDriver($criteria->getSeatsDriver());
        $this->setSeatsPassenger($criteria->getSeatsPassenger());
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

    public function getCriteriaString(?int $directionDriverId, ?int $directionPassengerId, string $delimiter=";")
    {
        return
        $this->id . $delimiter .
        ($this->driver ? '1' : '0') . $delimiter .
        ($this->passenger ? '1' : '0') . $delimiter .
        $this->frequency . $delimiter .
        '99' . $delimiter .
        $this->seatsPassenger . $delimiter;
        // ($this->fromDate ? $this->fromDate->format('Y-m-d') : '') . $delimiter .
        // ($this->fromTime ? $this->fromTime->format('H:i:s') : '') . $delimiter .
        // ($this->minTime ? $this->minTime->format('H:i:s') : '') . $delimiter .
        // ($this->maxTime ? $this->maxTime->format('H:i:s') : '') . $delimiter .
        // $this->marginDuration . $delimiter .
        // ($this->strictDate ? '1' : '0') . $delimiter .
        // ($this->strictPunctual ? '1' : '0') . $delimiter .
        // ($this->strictRegular ? '1' : '0') . $delimiter .
        // ($this->toDate ? $this->toDate->format('Y-m-d') : '') . $delimiter .
        // ($this->monCheck ? '1' : '0') . $delimiter .
        // ($this->tueCheck ? '1' : '0') . $delimiter .
        // ($this->wedCheck ? '1' : '0') . $delimiter .
        // ($this->thuCheck ? '1' : '0') . $delimiter .
        // ($this->friCheck ? '1' : '0') . $delimiter .
        // ($this->satCheck ? '1' : '0') . $delimiter .
        // ($this->sunCheck ? '1' : '0') . $delimiter .
        // ($this->monTime ? $this->monTime->format('H:i:s') : '') . $delimiter .
        // ($this->monMinTime ? $this->monMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->monMaxTime ? $this->monMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->tueTime ? $this->tueTime->format('H:i:s') : '') . $delimiter .
        // ($this->tueMinTime ? $this->tueMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->tueMaxTime ? $this->tueMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->wedTime ? $this->wedTime->format('H:i:s') : '') . $delimiter .
        // ($this->wedMinTime ? $this->wedMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->wedMaxTime ? $this->wedMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->thuTime ? $this->thuTime->format('H:i:s') : '') . $delimiter .
        // ($this->thuMinTime ? $this->thuMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->thuMaxTime ? $this->thuMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->friTime ? $this->friTime->format('H:i:s') : '') . $delimiter .
        // ($this->friMinTime ? $this->friMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->friMaxTime ? $this->friMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->satTime ? $this->satTime->format('H:i:s') : '') . $delimiter .
        // ($this->satMinTime ? $this->satMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->satMaxTime ? $this->satMaxTime->format('H:i:s') : '') . $delimiter .
        // ($this->sunTime ? $this->sunTime->format('H:i:s') : '') . $delimiter .
        // ($this->sunMinTime ? $this->sunMinTime->format('H:i:s') : '') . $delimiter .
        // ($this->sunMaxTime ? $this->sunMaxTime->format('H:i:s') : '') . $delimiter .
        // $this->monMarginDuration . $delimiter .
        // $this->tueMarginDuration . $delimiter .
        // $this->wedMarginDuration . $delimiter .
        // $this->thuMarginDuration . $delimiter .
        // $this->friMarginDuration . $delimiter .
        // $this->satMarginDuration . $delimiter .
        // $this->sunMarginDuration . $delimiter .
        // $this->maxDetourDuration . $delimiter .
        // $this->maxDetourDistance . $delimiter .
        // ($this->anyRouteAsPassenger ? '1' : '0') . $delimiter .
        // ($this->multiTransportMode ? '1' : '0') . $delimiter .
        // $this->priceKm . $delimiter .
        // $this->driverPrice . $delimiter .
        // $this->driverComputedPrice . $delimiter .
        // $this->driverComputedRoundedPrice . $delimiter .
        // $this->passengerPrice . $delimiter .
        // $this->passengerComputedPrice . $delimiter .
        // $this->passengerComputedRoundedPrice . $delimiter .
        // ($this->luggage ? '1' : '0') . $delimiter .
        // ($this->bike ? '1' : '0') . $delimiter .
        // ($this->backSeats ? '1' : '0') . $delimiter .
        // ($this->solidary ? '1' : '0') . $delimiter .
        // ($this->solidaryExclusive ? '1' : '0') . $delimiter .
        // ($this->avoidMotorway ? '1' : '0') . $delimiter .
        // ($this->avoidToll ? '1' : '0') . $delimiter .
        // $directionDriverId . $delimiter .
        // $directionPassengerId . $delimiter .
        // ($this->createdDate ? $this->createdDate->format('Y-m-d H:i:s') : '') . $delimiter;
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
