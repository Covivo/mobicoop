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

namespace App\Carpool\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * Carpooling : result item for a search / ad post.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class ResultItem
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this result item.
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int The matching proposal id.
     * @Groups("results")
     */
    private $proposalId;

    /**
     * @var int The matching id if it has already been persisted.
     * @Groups("results")
     */
    private $matchingId;

    /**
     * @var \DateTimeInterface The computed date for a punctual journey for the person who search / post.
     * @Groups("results")
     */
    private $date;

    /**
     * @var \DateTimeInterface The computed time for a punctual journey for the person who search / post.
     * @Groups("results")
     */
    private $time;

    /**
     * @var \DateTimeInterface The min date for a regular journey.
     * @Groups("results")
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The max date for a regular journey.
     * @Groups("results")
     */
    private $toDate;

    /**
     * @var Address The origin address (the origin of the carpooler who search or post).
     * @Groups("results")
     */
    private $origin;

    /**
     * @var Address The destination address (the destination of the carpooler who search or post).
     * @Groups("results")
     */
    private $destination;

    /**
     * @var Address The origin address of the driver.
     * @Groups("results")
     */
    private $originDriver;

    /**
     * @var Address The destination address of the driver.
     * @Groups("results")
     */
    private $destinationDriver;

    /**
     * @var Address The origin address of the passenger.
     * @Groups("results")
     */
    private $originPassenger;

    /**
     * @var Address The destination address of the passenger.
     * @Groups("results")
     */
    private $destinationPassenger;

    /**
     * @var array The waypoints of the journey.
     * @Groups("results")
     */
    private $waypoints;

    /**
     * @var boolean|null The journey is available on mondays (if regular).
     * @Groups("results")
     */
    private $monCheck;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     * @Groups("results")
     */
    private $tueCheck;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     * @Groups("results")
     */
    private $wedCheck;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     * @Groups("results")
     */
    private $thuCheck;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     * @Groups("results")
     */
    private $friCheck;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     * @Groups("results")
     */
    private $satCheck;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     * @Groups("results")
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null Mondays computed starting time (if regular).
     * @Groups("results")
     */
    private $monTime;

    /**
     * @var \DateTimeInterface|null Tuesdays computed starting time (if regular).
     * @Groups("results")
     */
    private $tueTime;

    /**
     * @var \DateTimeInterface|null Wednesdays computed starting time (if regular).
     * @Groups("results")
     */
    private $wedTime;

    /**
     * @var \DateTimeInterface|null Thursdays computed starting time (if regular).
     * @Groups("results")
     */
    private $thuTime;

    /**
     * @var \DateTimeInterface|null Fridays computed starting time (if regular).
     * @Groups("results")
     */
    private $friTime;

    /**
     * @var \DateTimeInterface|null Saturdays computed starting time (if regular).
     * @Groups("results")
     */
    private $satTime;

    /**
     * @var \DateTimeInterface|null Sundays computed starting time (if regular).
     * @Groups("results")
     */
    private $sunTime;

    /**
     * @var boolean|null Multiple times are used for the days.
     * @Groups("results")
     */
    private $multipleTimes;

    /**
     * @var string The price by km asked by the driver.
     * @Groups("results")
     */
    private $priceKm;

    /**
     * @var string The original price asked by the driver for his trip.
     * @Groups("results")
     */
    private $originalPrice;

    /**
     * @var string The computed price for the common distance carpooled.
     * @Groups("results")
     */
    private $computedPrice;

    /**
     * @var string The computed rounded price for the common distance carpooled.
     * @Groups("results")
     */
    private $computedRoundedPrice;

    /**
     * @var int The original distance in metres.
     * @Groups("results")
     */
    private $originalDistance;
    
    /**
     * @var int The accepted detour distance in metres.
     * @Groups("results")
     */
    private $acceptedDetourDistance;
    
    /**
     * @var int The new distance in metres.
     * @Groups("results")
     */
    private $newDistance;
    
    /**
     * @var int The detour distance in metres.
     * @Groups("results")
     */
    private $detourDistance;
    
    /**
     * @var int The detour distance in percentage of the original distance.
     * @Groups("results")
     */
    private $detourDistancePercent;
    
    /**
     * @var int The original duration in seconds.
     * @Groups("results")
     */
    private $originalDuration;
    
    /**
     * @var int The accepted detour duration in seconds.
     * @Groups("results")
     */
    private $acceptedDetourDuration;
    
    /**
     * @var int The new duration in seconds.
     * @Groups("results")
     */
    private $newDuration;
    
    /**
     * @var int The detour duration in seconds.
     * @Groups("results")
     */
    
    private $detourDuration;

    /**
     * @var int The detour duration in percent of the original duration.
     * @Groups("results")
     */
    private $detourDurationPercent;
    
    /**
     * @var int The common distance in metres.
     * @Groups("results")
     */
    private $commonDistance;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->waypoints = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposalId(): ?int
    {
        return $this->proposalId;
    }
    
    public function setProposalId(?int $proposalId): self
    {
        $this->proposalId = $proposalId;
        
        return $this;
    }

    public function getMatchingId(): ?int
    {
        return $this->matchingId;
    }
    
    public function setMatchingId(?int $matchingId): self
    {
        $this->matchingId = $matchingId;
        
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getOrigin(): ?Address
    {
        return $this->origin;
    }

    public function setOrigin(?Address $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): ?Address
    {
        return $this->destination;
    }

    public function setDestination(?Address $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getOriginDriver(): ?Address
    {
        return $this->originDriver;
    }

    public function setOriginDriver(?Address $originDriver): self
    {
        $this->originDriver = $originDriver;

        return $this;
    }

    public function getDestinationDriver(): ?Address
    {
        return $this->destinationDriver;
    }

    public function setDestinationDriver(?Address $destinationDriver): self
    {
        $this->destinationDriver = $destinationDriver;

        return $this;
    }

    public function getOriginPassenger(): ?Address
    {
        return $this->originPassenger;
    }

    public function setOriginPassenger(?Address $originPassenger): self
    {
        $this->originPassenger = $originPassenger;

        return $this;
    }

    public function getDestinationPassenger(): ?Address
    {
        return $this->destinationPassenger;
    }

    public function setDestinationPassenger(?Address $destinationPassenger): self
    {
        $this->destinationPassenger = $destinationPassenger;

        return $this;
    }
    
    public function getWaypoints()
    {
        return $this->waypoints;
    }

    public function setWaypoints($waypoints): self
    {
        $this->waypoints = $waypoints;
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

    public function hasMultipleTimes(): ?bool
    {
        return $this->multipleTimes;
    }

    public function setMultipleTimes(): self
    {
        $time = [];
        if ($this->isMonCheck()) {
            $time[$this->getMonTime()->format('His')] = 1;
        }
        if ($this->isTueCheck()) {
            $time[$this->getTueTime()->format('His')] = 1;
        }
        if ($this->isWedCheck()) {
            $time[$this->getWedTime()->format('His')] = 1;
        }
        if ($this->isThuCheck()) {
            $time[$this->getThuTime()->format('His')] = 1;
        }
        if ($this->isFriCheck()) {
            $time[$this->getFriTime()->format('His')] = 1;
        }
        if ($this->isSatCheck()) {
            $time[$this->getSatTime()->format('His')] = 1;
        }
        if ($this->isSunCheck()) {
            $time[$this->getSunTime()->format('His')] = 1;
        }
        $this->multipleTimes = (count($time) > 1);

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

    public function getOriginalPrice(): ?string
    {
        return $this->originalPrice;
    }
    
    public function setOriginalPrice(?string $originalPrice)
    {
        $this->originalPrice = $originalPrice;
    }

    public function getComputedPrice(): ?string
    {
        return $this->computedPrice;
    }
    
    public function setComputedPrice(?string $computedPrice)
    {
        $this->computedPrice = $computedPrice;
    }

    public function getComputedRoundedPrice(): ?string
    {
        return $this->computedRoundedPrice;
    }
    
    public function setComputedRoundedPrice(?string $computedRoundedPrice)
    {
        $this->computedRoundedPrice = $computedRoundedPrice;
    }

    public function getOriginalDistance(): ?int
    {
        return $this->originalDistance;
    }

    public function setOriginalDistance(int $originalDistance): self
    {
        $this->originalDistance = $originalDistance;

        return $this;
    }

    public function getAcceptedDetourDistance(): ?int
    {
        return $this->acceptedDetourDistance;
    }

    public function setAcceptedDetourDistance(int $acceptedDetourDistance): self
    {
        $this->acceptedDetourDistance = $acceptedDetourDistance;

        return $this;
    }

    public function getNewDistance(): ?int
    {
        return $this->newDistance;
    }

    public function setNewDistance(int $newDistance): self
    {
        $this->newDistance = $newDistance;

        return $this;
    }

    public function getDetourDistance(): ?int
    {
        return $this->detourDistance;
    }

    public function setDetourDistance(int $detourDistance): self
    {
        $this->detourDistance = $detourDistance;

        return $this;
    }

    public function getDetourDistancePercent(): ?int
    {
        return $this->detourDistancePercent;
    }

    public function setDetourDistancePercent(int $detourDistancePercent): self
    {
        $this->detourDistancePercent = $detourDistancePercent;

        return $this;
    }

    public function getOriginalDuration(): ?int
    {
        return $this->originalDuration;
    }

    public function setOriginalDuration(int $originalDuration): self
    {
        $this->originalDuration = $originalDuration;

        return $this;
    }

    public function getAcceptedDetourDuration(): ?int
    {
        return $this->acceptedDetourDuration;
    }

    public function setAcceptedDetourDuration(int $acceptedDetourDuration): self
    {
        $this->acceptedDetourDuration = $acceptedDetourDuration;

        return $this;
    }

    public function getNewDuration(): ?int
    {
        return $this->newDuration;
    }

    public function setNewDuration(int $newDuration): self
    {
        $this->newDuration = $newDuration;

        return $this;
    }

    public function getDetourDuration(): ?int
    {
        return $this->detourDuration;
    }

    public function setDetourDuration(int $detourDuration): self
    {
        $this->detourDuration = $detourDuration;

        return $this;
    }

    public function getDetourDurationPercent(): ?int
    {
        return $this->detourDurationPercent;
    }

    public function setDetourDurationPercent(int $detourDurationPercent): self
    {
        $this->detourDurationPercent = $detourDurationPercent;

        return $this;
    }

    public function getCommonDistance(): ?int
    {
        return $this->commonDistance;
    }

    public function setCommonDistance(int $commonDistance): self
    {
        $this->commonDistance = $commonDistance;

        return $this;
    }
}
