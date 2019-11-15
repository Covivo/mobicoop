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
    
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_DRIVER_OR_PASSENGER = 3;

    const TYPE_ONE_WAY = 1;
    const TYPE_ROUND = 2;

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
     * @var int|null The ad type (1 = one way; 2 = round trip).
     *
     * @Groups({"read","write"})
     */
    private $type;

    /**
     * @var int The frequency for this ad.
     *
     * @Groups({"read","write"})
     */
    private $frequency;

    /**
     * @var array The waypoints for the outward.
     *
     * @Groups({"read","write"})
     */
    private $outwardWaypoints;

    /**
     * @var array|null The waypoints for the return.
     *
     * @Groups({"read","write"})
     */
    private $returnWaypoints;

    /**
     * @var \DateTimeInterface|null The date for the outward if the frequency is punctual, the start date of the outward if the frequency is regular.
     *
     * @Groups({"read","write"})
     */
    private $outwardDate;

    /**
     * @var \DateTimeInterface|null The limit date for the outward if the frequency is regular.
     *
     * @Groups({"read","write"})
     */
    private $outwardLimitDate;

    /**
     * @var \DateTimeInterface|null The date for the return if the frequency is punctual, the start date of the return if the frequency is regular.
     *
     * @Groups({"read","write"})
     */
    private $returnDate;

    /**
     * @var \DateTimeInterface|null The limit date for the return if the frequency is regular.
     *
     * @Groups({"read","write"})
     */
    private $returnLimitDate;

    /**
     * @var string|null The time for the outward if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $outwardTime;

    /**
     * @var string|null The time for the return if the frequency is punctual.
     *
     * @Groups({"read","write"})
     */
    private $returnTime;

    /**
     * @var array|null The schedule for if the frequency is regular.
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
    * @var float|null The total price of the outward selected by the user :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $outwardPrice;

    /**
    * @var float|null The total price of the outward rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $outwardRoundedPrice;

    /**
    * @var float|null The total price of the outward computed by the system, using the user price per km, not rounded :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $outwardComputedPrice;

    /**
    * @var float|null The computed price of the outward rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $outwardComputedRoundedPrice;

    /**
    * @var float|null The total price of the return selected by the user :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $returnPrice;

    /**
    * @var float|null The total price of the return rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $returnRoundedPrice;

    /**
    * @var float|null The total price of the return computed by the system, using the user price per km, not rounded :
    * - as a driver if driver and passenger
    * - as a passenger only if passenger
    *
    * @Groups({"read","write"})
    */
    private $returnComputedPrice;

    /**
    * @var float|null The computed price of the return rounded using the rounding rules.
    *
    * @Groups({"read","write"})
    */
    private $returnComputedRoundedPrice;

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
     * @var int The user id of the ad owner.
     *
     * @Groups({"read","write"})
     */
    private $userId;

    /**
     * @var int|null The user id of the poster (used for delegation).
     *
     *@Groups({"read","write"})
     */
    private $posterId;

    /**
     * @var array|null The communities associated with the ad.
     *
     * @Groups({"read","write"})
     */
    private $communities;

    /**
     * @var int|null The event id associated with the ad.
     *
     * @Groups({"read","write"})
     */
    private $eventId;

    /**
     * @var array|null The carpool results for the outward.
     *
     * @Groups("read")
     */
    private $outwardResults;

    /**
     * @var array|null The carpool results for the return.
     *
     * @Groups("read")
     */
    private $returnResults;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->outwardWaypoints = [];
        $this->returnWaypoints = [];
        $this->schedule = [];
        $this->communities = [];
        $this->outwardResults = [];
        $this->returnResults = [];
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getOutwardWaypoints(): array
    {
        return $this->outwardWaypoints;
    }
    
    public function setOutwardWaypoints(array $outwardWaypoints): self
    {
        $this->outwardWaypoints = $outwardWaypoints;
        
        return $this;
    }

    public function getReturnWaypoints(): ?array
    {
        return $this->returnWaypoints;
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

    public function getOutwardLimitDate(): ?\DateTimeInterface
    {
        return $this->outwardLimitDate;
    }

    public function setOutwardLimitDate(?\DateTimeInterface $outwardLimitDate): self
    {
        $this->outwardLimitDate = $outwardLimitDate;

        return $this;
    }

    public function getOutwardTime(): ?string
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?string $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

        return $this;
    }

    public function getReturnDate(): ?string
    {
        return $this->returnDate;
    }

    public function setReturnDate(?string $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getReturnLimitDate(): ?\DateTimeInterface
    {
        return $this->returnLimitDate;
    }

    public function setReturnLimitDate(?\DateTimeInterface $returnLimitDate): self
    {
        $this->returnLimitDate = $returnLimitDate;

        return $this;
    }

    public function getReturnTime(): ?string
    {
        return $this->returnTime;
    }

    public function setReturnTime(?string $returnTime): self
    {
        $this->returnTime = $returnTime;

        return $this;
    }

    public function getSchedule(): ?array
    {
        return $this->schedule;
    }
    
    public function setSchedule(?array $schedule): self
    {
        $this->schedule = $schedule;
        
        return $this;
    }

    public function getCommunities(): ?array
    {
        return $this->communities;
    }
    
    public function setCommunities(?array $communities): self
    {
        $this->communities = $communities;
        
        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(?int $eventId): self
    {
        $this->eventId = $eventId;

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

    public function getOutwardPrice(): ?string
    {
        return $this->outwardPrice;
    }
    
    public function setOutwardPrice(?string $outwardPrice)
    {
        $this->outwardPrice = $outwardPrice;
    }

    public function getOutwardRoundedPrice(): ?string
    {
        return $this->outwardRoundedPrice;
    }
    
    public function setOutwardRoundedPrice(?string $outwardRoundedPrice)
    {
        $this->outwardRoundedPrice = $outwardRoundedPrice;
    }

    public function getOutwardComputedPrice(): ?string
    {
        return $this->outwardComputedPrice;
    }
    
    public function setOutwardComputedPrice(?string $outwardComputedPrice)
    {
        $this->outwardComputedPrice = $outwardComputedPrice;
    }

    public function getOutwardComputedRoundedPrice(): ?string
    {
        return $this->outwardComputedRoundedPrice;
    }
    
    public function setOutwardComputedRoundedPrice(?string $outwardComputedRoundedPrice)
    {
        $this->outwardComputedRoundedPrice = $outwardComputedRoundedPrice;
    }

    public function getReturnPrice(): ?string
    {
        return $this->returnPrice;
    }
    
    public function setReturnPrice(?string $returnPrice)
    {
        $this->returnPrice = $returnPrice;
    }

    public function getReturnRoundedPrice(): ?string
    {
        return $this->returnRoundedPrice;
    }
    
    public function setReturnRoundedPrice(?string $returnRoundedPrice)
    {
        $this->returnRoundedPrice = $returnRoundedPrice;
    }

    public function getReturnComputedPrice(): ?string
    {
        return $this->returnComputedPrice;
    }
    
    public function setReturnComputedPrice(?string $returnComputedPrice)
    {
        $this->returnComputedPrice = $returnComputedPrice;
    }

    public function getReturnComputedRoundedPrice(): ?string
    {
        return $this->returnComputedRoundedPrice;
    }
    
    public function setReturnComputedRoundedPrice(?string $returnComputedRoundedPrice)
    {
        $this->returnComputedRoundedPrice = $returnComputedRoundedPrice;
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getPosterId(): ?int
    {
        return $this->posterId;
    }

    public function setPosterId(?int $posterId): self
    {
        $this->posterId = $posterId;

        return $this;
    }

    public function getOutwardResults(): array
    {
        return $this->outwardResults;
    }

    public function setOutwardResults(array $outwardResults)
    {
        $this->outwardResults = $outwardResults;

        return $this;
    }

    public function getReturnResults(): array
    {
        return $this->returnResults;
    }

    public function setReturnResults(array $returnResults)
    {
        $this->returnResults = $returnResults;

        return $this;
    }
}
