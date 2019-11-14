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

use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Carpool\Controller\AdPost;

/**
 * Carpooling : an ad.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "controller"=AdPost::class,
 *          },
 *      },
 *      itemOperations={
 *          "get","delete","put"
 *      }
 * )
 */
class Ad
{
    const DEFAULT_ID = 999999999999;
    
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;

    /**
     * @var int The id of this ad.
     *
     * @ApiProperty(identifier=true)
     * @Groups("read")
     */
    private $id;

    /**
     * @var int The role for this ad.
     *
     * @Groups({"read","write"})
     */
    private $role;

    /**
     * @var int The frequency for this ad.
     *
     * @Groups({"read","write"})
     */
    private $frequency;

    /**
     * @var ArrayCollection The waypoints for the outward.
     *
     * @Groups({"read","write"})
     */
    private $outwardWaypoints;

    /**
     * @var ArrayCollection|null The waypoints for the return.
     *
     * @Groups({"read","write"})
     */
    private $returnWaypoints;

    /**
     * @var \DateTimeInterface|null The date for the outward if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $outwardDate;

    /**
     * @var \DateTimeInterface|null The date for the return if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $returnDate;

    /**
     * @var \DateTimeInterface|null The time for the outward if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The time for the return if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $returnTime;

    /**
     * @var ArrayCollection|null The schedule for if the frequency is regular.
     *
     * @Groups({"read","write"})
     */
    private $schedule;
    
    /**
    * @var float|null The price per km.
    *
    * @Groups({"read","write"})
    */
    private $priceKm;

    /**
    * @var float|null The total price selected by the user :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $price;

    /**
    * @var float|null The total price rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $roundedPrice;

    /**
    * @var float|null The total price computed by the system, using the user price per km, not rounded :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $computedPrice;

    /**
    * @var float|null The computed price rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $computedRoundedPrice;

    /**
     * @var int|null The number of seats available / required.
     *
     * @Groups({"read","write"})
     */
    private $seats;

    /**
     * @var boolean|null Big luggage accepted / asked.
     *
     * @Groups({"read","write"})
     */
    private $luggage;

    /**
     * @var boolean|null Bike accepted / asked.
     *
     * @Groups({"read","write"})
     */
    private $bike;

    /**
     * @var boolean|null 2 passengers max on the back seats.
     *
     * @Groups({"read","write"})
     */
    private $backSeats;

    /**
     * @var boolean|null Solidary request.
     *
     * @Groups({"read","write"})
     */
    private $solidary;

    /**
     * @var boolean|null Solidary exclusive.
     *
     * @Groups({"read","write"})
     */
    private $solidaryExclusive;

    /**
     * @var boolean|null Avoid motorway.
     *
     * @Groups({"read","write"})
     */
    private $avoidMotorway;

    /**
     * @var boolean|null Avoid toll.
     *
     * @Groups({"read","write"})
     */
    private $avoidToll;

    /**
     * @var string A comment about the ad.
     *
     * @Groups({"read","write"})
     */
    private $comment;

    /**
     * @var ArrayCollection|null The carpool results for the ad.
     *
     * @Groups("read")
     */
    private $results;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->outwardWaypoints = new ArrayCollection();
        $this->returnWaypoints = new ArrayCollection();
        $this->schedule = new ArrayCollection();
        $this->results = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }
    
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getOutwardWaypoints()
    {
        return $this->outwardWaypoints->getValues();
    }
    
    public function setOutwardWaypoints(array $outwardWaypoints): self
    {
        $this->outwardWaypoints = $outwardWaypoints;
        
        return $this;
    }

    public function getReturnWaypoints()
    {
        return $this->returnWaypoints->getValues();
    }
    
    public function setReturnWaypoints(?array $returnWaypoints): self
    {
        $this->returnWaypoints = $returnWaypoints;
        
        return $this;
    }

    public function getOutwardDate(): ?\DateTimeInterface
    {
        return $this->outwardDate;
    }

    public function setOutwardDate(?\DateTimeInterface $outwardDate): self
    {
        $this->outwardDate = $outwardDate;

        return $this;
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?\DateTimeInterface $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?\DateTimeInterface $returnTime): self
    {
        $this->returnTime = $returnTime;

        return $this;
    }

    public function getSchedule()
    {
        return $this->schedule->getValues();
    }
    
    public function setSchedule(array $schedule): self
    {
        $this->schedule = $schedule;
        
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

    public function getRoundedPrice(): ?string
    {
        return $this->roundedPrice;
    }
    
    public function setRoundedPrice(?string $roundedPrice)
    {
        $this->roundedPrice = $roundedPrice;
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

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        
        return $this;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }
}
