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
use App\Carpool\Controller\AdGet;
use App\Carpool\Controller\AdAskPost;
use App\Carpool\Controller\AdAskPut;
use App\Carpool\Controller\AdAskGet;

/**
 * Carpooling : an ad.
 * All actions related to a carpooling should be related to this entity.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read","results"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/carpools",
 *          },
 *          "get"={
 *              "method"="GET",
 *              "path"="/ads",
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/carpools",
 *              "controller"=AdPost::class,
 *          },
 *          "post_ask"={
 *              "method"="POST",
 *              "path"="/carpools/ask",
 *              "controller"=AdAskPost::class,
 *              "defaults"={"type"="ask"}
 *          },
 *          "post_contact"={
 *              "method"="POST",
 *              "path"="/carpools/contact",
 *              "controller"=AdAskPost::class,
 *              "defaults"={"type"="contact"}
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/carpools/{id}",
 *              "controller"=AdGet::class,
 *              "read"=false
 *          },
 *          "put_ask"={
 *              "method"="PUT",
 *              "path"="/carpools/ask/{id}",
 *              "controller"=AdAskPut::class,
 *              "read"=false
 *          },
 *          "get_ask"={
 *              "method"="GET",
 *              "path"="/carpools/ask/{id}",
 *              "controller"=AdAskGet::class,
 *              "read"=false
 *          }
 *      }
 * )
 *
 */
class Ad
{
    const DEFAULT_ID = 999999999999;
    
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_DRIVER_OR_PASSENGER = 3;

    /**
     * @var int The id of this ad.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"read","write"})
     */
    private $id;

    /**
     * @var boolean|null The ad is a search only.
     *
     * @Groups({"read","write"})
     */
    private $search;

    /**
     * @var int The role for this ad.
     *
     * @Groups({"read","write"})
     */
    private $role;

    /**
     * @var boolean|null The ad is a one way trip.
     *
     * @Groups({"read","write"})
     */
    private $oneWay;

    /**
     * @var int|null The frequency for this ad.
     *
     * @Groups({"read","write"})
     */
    private $frequency;

    /**
     * @var array|null The waypoints for the outward.
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
     * @var array|null The schedule if the frequency is regular.
     * The schedule contains the outward and return elements.
     *
     * @Groups({"read","write"})
     */
    private $schedule;

    /**
     * @var boolean|null For punctual proposals, the user accepts only matchings for the defined date (no ranges).
     *
     * @Groups({"read","write"})
     */
    private $strictDate;

    /**
     * @var boolean|null For punctual proposals, the user accepts only matchings with punctual trips (no regular trips).
     *
     * @Groups({"read","write"})
     */
    private $strictPunctual;

    /**
     * @var boolean|null For regular proposals, the user accepts only matchings with regular trips (no punctual trips).
     *
     * @Groups({"read","write"})
     */
    private $strictRegular;
    
    /**
    * @var string|null The price per km.
    *
    * @Groups({"read","write"})
    */
    private $priceKm;

    /**
    * @var string|null The total price of the outward selected by the user as a driver.
    *
    * @Groups({"read","write"})
    */
    private $outwardDriverPrice;

    /**
    * @var string|null The total price of the return selected by the user as a driver.
    *
    * @Groups({"read","write"})
    */
    private $returnDriverPrice;

    /**
    * @var string|null The total price of the outward selected by the user as a passenger.
    *
    * @Groups({"read","write"})
    */
    private $outwardPassengerPrice;

    /**
    * @var string|null The total price of the return selected by the user as a passenger.
    *
    * @Groups({"read","write"})
    */
    private $returnPassengerPrice;

    /**
     * @var int|null The number of seats available.
     *
     * @Groups({"read","write"})
     */
    private $seatsDriver;

    /**
     * @var int|null The number of seats required.
     *
     * @Groups({"read","write"})
     */
    private $seatsPassenger;

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
     * @var string|null A comment about the ad.
     *
     * @Groups({"read","write"})
     */
    private $comment;

    /**
     * @var int|null The user id of the ad owner. Null for an anonymous search.
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
     * @var array|null The carpool results.
     *
     * @Groups("read")
     */
    private $results;

    /**
     * @var int|null The ad id for which the current ad is an ask.
     *
     * @Groups({"read","write"})
     */
    private $adId;

    /**
     * @var int|null The matching id related to the above ad id.
     *
     * @Groups({"read","write"})
     */
    private $matchingId;

    /**
     * @var int The ask status if the ad concerns a given ask.
     *
     * @Groups({"read","write"})
     */
    private $askStatus;

    /**
     * @var int The ask id if the ad concerns a given ask.
     *
     * @Groups({"read","write"})
     */
    private $askId;

    /**
     * @var boolean|null The given user can update the ask if the ad concerns a given ask.
     *
     * @Groups({"read","write"})
     */
    private $canUpdateAsk;

    /**
     * @var array|null The filters to apply to the results.
     *
     * @Groups("write")
     */
    private $filters;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->outwardWaypoints = [];
        $this->returnWaypoints = [];
        $this->schedule = [];
        $this->communities = [];
        $this->results = [];
        $this->filters = [];
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function isSearch(): ?bool
    {
        return $this->search;
    }

    public function setSearch(bool $search): self
    {
        $this->search = $search;

        return $this;
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

    public function isOneWay(): ?bool
    {
        return $this->oneWay;
    }

    public function setOneWay(bool $oneWay): self
    {
        $this->oneWay = $oneWay;

        return $this;
    }
    
    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(?int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getOutwardWaypoints(): ?array
    {
        return $this->outwardWaypoints;
    }
    
    public function setOutwardWaypoints(?array $outwardWaypoints): self
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

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): self
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

    public function getPriceKm(): ?string
    {
        return $this->priceKm;
    }
    
    public function setPriceKm(?string $priceKm)
    {
        $this->priceKm = $priceKm;
    }

    public function getOutwardDriverPrice(): ?string
    {
        return $this->outwardDriverPrice;
    }
    
    public function setOutwardDriverPrice(?string $outwardDriverPrice)
    {
        $this->outwardDriverPrice = $outwardDriverPrice;
    }

    public function getReturnDriverPrice(): ?string
    {
        return $this->returnDriverPrice;
    }
    
    public function setReturnDriverPrice(?string $returnDriverPrice)
    {
        $this->returnDriverPrice = $returnDriverPrice;
    }

    public function getOutwardPassengerPrice(): ?string
    {
        return $this->outwardPassengerPrice;
    }
    
    public function setOutwardPassengerPrice(?string $outwardPassengerPrice)
    {
        $this->outwardPassengerPrice = $outwardPassengerPrice;
    }

    public function getReturnPassengerPrice(): ?string
    {
        return $this->returnPassengerPrice;
    }
    
    public function setReturnPassengerPrice(?string $returnPassengerPrice)
    {
        $this->returnPassengerPrice = $returnPassengerPrice;
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
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

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;

        return $this;
    }

    public function getAdId(): ?int
    {
        return $this->adId;
    }

    public function setAdId(?int $adId): self
    {
        $this->adId = $adId;

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

    public function getAskStatus(): ?int
    {
        return $this->askStatus;
    }

    public function setAskStatus(int $askStatus): self
    {
        $this->askStatus = $askStatus;

        return $this;
    }

    public function getAskId(): ?int
    {
        return $this->askId;
    }

    public function setAskId(int $askId): self
    {
        $this->askId = $askId;

        return $this;
    }

    public function getCanUpdateAsk(): ?bool
    {
        return $this->canUpdateAsk;
    }
    
    public function setCanUpdateAsk(?bool $canUpdateAsk): self
    {
        $this->canUpdateAsk = $canUpdateAsk;
        
        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters)
    {
        $this->filters = $filters;

        return $this;
    }
}
