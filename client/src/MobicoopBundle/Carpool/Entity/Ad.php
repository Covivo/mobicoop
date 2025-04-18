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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Carpooling : an ad.
 * All actions related to a carpooling should be related to this entity.
 */
class Ad extends GamificationEntity implements ResourceInterface, \JsonSerializable
{
    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;
    public const ROLE_DRIVER_OR_PASSENGER = 3;
    public const FREQUENCY_PUNCTUAL = 1;
    public const FREQUENCY_REGULAR = 2;
    public const TYPE_ONE_WAY = 1;
    public const TYPE_OUTWARD = 2;
    public const TYPE_RETURN = 3;
    public const RESOURCE_NAME = 'carpools';

    /**
     * @var int the id of this ad
     */
    private $id;

    /**
     * @var null|bool the ad is a search only
     *
     * @Groups({"post","put"})
     */
    private $search;

    /**
     * @var int the role for this ad
     *
     * @Groups({"post","put"})
     */
    private $role;

    /**
     * @var null|bool the ad is a one way trip
     *
     * @Groups({"post","put"})
     */
    private $oneWay;

    /**
     * @var null|int the frequency for this ad
     *
     * @Groups({"post","put"})
     */
    private $frequency;

    /**
     * @var array the waypoints for the outward
     *
     * @Groups({"post","put"})
     */
    private $outwardWaypoints;

    /**
     * @var null|array the waypoints for the return
     *
     * @Groups({"post","put"})
     */
    private $returnWaypoints;

    /**
     * @var null|\DateTimeInterface the date for the outward if the frequency is punctual, the start date of the outward if the frequency is regular
     *
     * @Groups({"post","put"})
     */
    private $outwardDate;

    /**
     * @var null|\DateTimeInterface the limit date for the outward if the frequency is regular
     *
     * @Groups({"post","put"})
     */
    private $outwardLimitDate;

    /**
     * @var null|\DateTimeInterface the date for the return if the frequency is punctual, the start date of the return if the frequency is regular
     *
     * @Groups({"post","put"})
     */
    private $returnDate;

    /**
     * @var null|\DateTimeInterface the limit date for the return if the frequency is regular
     *
     * @Groups({"post","put"})
     */
    private $returnLimitDate;

    /**
     * @var null|string the time for the outward if the frequency is punctual
     *
     * @Groups({"post","put"})
     */
    private $outwardTime;

    /**
     * @var null|string the time for the return if the frequency is punctual
     *
     * @Groups({"post","put"})
     */
    private $returnTime;

    /**
     * @var null|array the schedule for if the frequency is regular
     *
     * @Groups({"post","put"})
     */
    private $schedule;

    /**
     * @var null|bool for punctual proposals, the user accepts only matchings for the defined date (no ranges)
     *
     * @Groups({"post","put"})
     */
    private $strictDate;

    /**
     * @var null|bool for punctual proposals, the user accepts only matchings with punctual trips (no regular trips)
     *
     * @Groups({"post","put"})
     */
    private $strictPunctual;

    /**
     * @var null|bool for regular proposals, the user accepts only matchings with regular trips (no punctual trips)
     *
     * @Groups({"post","put"})
     */
    private $strictRegular;

    /**
     * @var null|float the price per km
     *
     * @Groups({"post","put"})
     */
    private $priceKm;

    /**
     * @var null|float the total price of the outward selected by the user as a driver
     *
     * @Groups({"post","put"})
     */
    private $outwardDriverPrice;

    /**
     * @var null|float the total price of the return selected by the user as a driver
     *
     * @Groups({"post","put"})
     */
    private $returnDriverPrice;

    /**
     * @var null|float the total price of the outward selected by the user as a passenger
     *
     * @Groups({"post","put"})
     */
    private $outwardPassengerPrice;

    /**
     * @var null|float the total price of the return selected by the user as a passenger
     *
     * @Groups({"post","put"})
     */
    private $returnPassengerPrice;

    /**
     * @var null|int the number of seats available
     *
     * @Groups({"post","put"})
     */
    private $seatsDriver;

    /**
     * @var null|int the number of seats required
     *
     * @Groups({"post","put"})
     */
    private $seatsPassenger;

    /**
     * @var null|bool big luggage accepted / asked
     *
     * @Groups({"post","put"})
     */
    private $luggage;

    /**
     * @var null|bool bike accepted / asked
     *
     * @Groups({"post","put"})
     */
    private $bike;

    /**
     * @var null|bool 2 passengers max on the back seats
     *
     * @Groups({"post","put"})
     */
    private $backSeats;

    /**
     * @var null|bool solidary request
     *
     * @Groups({"post","put"})
     */
    private $solidary;

    /**
     * @var null|bool solidary exclusive
     *
     * @Groups({"post","put"})
     */
    private $solidaryExclusive;

    /**
     * @var null|bool avoid motorway
     *
     * @Groups({"post","put"})
     */
    private $avoidMotorway;

    /**
     * @var null|bool avoid toll
     *
     * @Groups({"post","put"})
     */
    private $avoidToll;

    /**
     * @var string a comment about the ad
     *
     * @Groups({"post","put"})
     */
    private $comment;

    /**
     * @var null|int The user id of the ad owner. Null for an anonymous search.
     *
     * @Groups({"post","put"})
     */
    private $userId;

    /**
     * @var null|int the user id of the poster (used for delegation)
     *
     *@Groups({"post","put"})
     */
    private $posterId;

    /**
     * @var null|array the communities associated with the ad
     *
     * @Groups({"post","put","get"})
     */
    private $communities;

    /**
     * @var null|int the event id associated with the ad
     *
     * @Groups({"post","put"})
     */
    private $eventId;

    /**
     * @var null|array the carpool results
     */
    private $results;

    /**
     * @var null|int the number of results
     */
    private $nbResults;

    /**
     * @var null|array the carpool asks
     */
    private $asks;

    /**
     * @var null|int the ad id for which the current ad is an ask
     *
     * @Groups({"post","put"})
     */
    private $adId;

    /**
     * @var null|int the ad id for which the current ad is an ask
     *
     * @Groups({"post","put"})
     */
    private $askId;

    /**
     * @var null|int the matching id related to the above ad id
     *
     * @Groups({"post","put"})
     */
    private $matchingId;

    /**
     * @var null|array the filters to apply to the results
     *
     * @Groups({"post","put"})
     */
    private $filters;

    /**
     * @var null|bool If the current user can make a formal Ask from this Ad
     */
    private $canUpdateAsk;

    /**
     * @var null|int Status of the Ask related to this Ad
     *
     * @Groups({"post","put"})
     */
    private $askStatus;

    /**
     * @var bool Paused ad.
     *           A paused ad can't be the found in the result of a search, and can be unpaused at any moment.
     *
     * @Groups({"post","put"})
     */
    private $paused;

    /**
     * @var null|int proposalId
     *
     * @Groups({"post","put"})
     */
    private $proposalId;

    /**
     * @var int
     *          Potential carpoolers count
     */
    private $potentialCarpoolers;

    /**
     * @var bool
     */
    private $smoke;

    /**
     * @var bool
     */
    private $music;

    /**
     * @var null|string The message if Ad owner is making major updates to his Ad
     *
     * @Groups({"post", "put"})
     */
    private $cancellationMessage;

    /**
     * @var null|string The message if Ad owner is deleting his Ad
     *
     * @Groups({"post", "put"})
     */
    private $deletionMessage;

    /**
     * @var null|int the user id of the deleter
     *
     *@Groups({"post","put"})
     */
    private $deleterId;

    /**
     * @var null|int The payment status of the Ad
     */
    private $paymentStatus;

    /**
     * @var null|int The id of the PaymentItem of the Ad
     */
    private $paymentItemId;

    /**
     * @var null|int The default week of the PaymentItem
     */
    private $paymentItemWeek;

    /**
     * @var null|\DateTimeInterface The date of an unpaid declaration for this Ad
     *
     * @Groups({"read","readPaymentStatus"})
     */
    private $unpaidDate;

    public function __construct($id = null)
    {
        $this->outwardWaypoints = [];
        $this->returnWaypoints = [];
        $this->schedule = [];
        $this->communities = [];
        $this->results = [];
        $this->filters = [];
        $this->asks = [];
        if (!is_null($id)) {
            $this->id = $id;
        }
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

    public function getRole(): ?int
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

    public function isSmoke(): ?bool
    {
        return $this->smoke;
    }

    public function setSmoke(?bool $smoke): self
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function hasMusic(): ?bool
    {
        return $this->music;
    }

    public function setMusic(?bool $music): Ad
    {
        $this->music = $music;

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

    public function getNbResults(): ?int
    {
        return $this->nbResults;
    }

    public function setNbResults(?int $nbResults): self
    {
        $this->nbResults = $nbResults;

        return $this;
    }

    public function getAsks(): ?array
    {
        return $this->asks;
    }

    public function setAsks(?array $asks)
    {
        $this->asks = $asks;

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

    public function getAskId(): ?int
    {
        return $this->askId;
    }

    public function setAskId(?int $askId): self
    {
        $this->askId = $askId;

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

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters)
    {
        $this->filters = $filters;

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

    public function getAskStatus(): ?int
    {
        return $this->askStatus;
    }

    public function setAskStatus(?int $askStatus): self
    {
        $this->askStatus = $askStatus;

        return $this;
    }

    public function isPaused(): bool
    {
        return $this->paused ? true : false;
    }

    public function setPaused(?bool $paused): self
    {
        $this->paused = $paused;

        return $this;
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

    public function getPotentialCarpoolers(): ?int
    {
        return $this->potentialCarpoolers;
    }

    public function setPotentialCarpoolers(int $potentialCarpoolers): self
    {
        $this->potentialCarpoolers = $potentialCarpoolers;

        return $this;
    }

    public function getOrigin()
    {
        if (!empty($this->getOutwardWaypoints())) {
            $origin = $this->getOutwardWaypoints()[array_search(0, array_column($this->getOutwardWaypoints(), 'position'))];
            if (isset($origin['@type']) && 'Address' === $origin['@type']) {
                return $origin;
            }
            if (isset($origin['address'])) {
                return $origin['address'];
            }
        }

        return null;
    }

    public function getDestination()
    {
        if (!empty($this->getOutwardWaypoints())) {
            $destination = $this->getOutwardWaypoints()[array_search(true, array_column($this->getOutwardWaypoints(), 'destination'))];
            if (isset($destination['@type']) && 'Address' === $destination['@type']) {
                return $destination;
            }
            if (isset($destination['address'])) {
                return $destination['address'];
            }
        }

        return null;
    }

    public function getCancellationMessage(): ?string
    {
        return $this->cancellationMessage;
    }

    public function setCancellationMessage(?string $cancellationMessage): Ad
    {
        $this->cancellationMessage = $cancellationMessage;

        return $this;
    }

    public function getDeleterId(): ?int
    {
        return $this->deleterId;
    }

    public function setDeleterId(?int $deleterId): self
    {
        $this->deleterId = $deleterId;

        return $this;
    }

    public function getDeletionMessage(): ?string
    {
        return $this->deletionMessage;
    }

    public function setDeletionMessage(?string $deletionMessage): Ad
    {
        $this->deletionMessage = $deletionMessage;

        return $this;
    }

    public function getPaymentStatus(): ?int
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?int $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getPaymentItemId(): ?int
    {
        return $this->paymentItemId;
    }

    public function setPaymentItemId(?int $paymentItemId): self
    {
        $this->paymentItemId = $paymentItemId;

        return $this;
    }

    public function getPaymentItemWeek(): ?int
    {
        return $this->paymentItemWeek;
    }

    public function setPaymentItemWeek(?int $paymentItemWeek): self
    {
        $this->paymentItemWeek = $paymentItemWeek;

        return $this;
    }

    public function getUnpaidDate(): ?\DateTimeInterface
    {
        return $this->unpaidDate;
    }

    public function setUnpaidDate(?\DateTimeInterface $unpaidDate): self
    {
        $this->unpaidDate = $unpaidDate;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'role' => $this->getRole(),
                'oneWay' => $this->isOneWay(),
                'outwardWaypoints' => $this->getOutwardWaypoints(),
                'returnWaypoints' => $this->getReturnWaypoints(),
                'outwardDate' => !is_null($this->getOutwardDate()) ? $this->getOutwardDate()->format('Y-m-d') : null,
                'outwardLimitDate' => !is_null($this->getOutwardLimitDate()) ? $this->getOutwardLimitDate()->format('Y-m-d') : null,
                'returnDate' => !is_null($this->getReturnDate()) ? $this->getReturnDate()->format('Y-m-d') : null,
                'returnLimitDate' => !is_null($this->getReturnLimitDate()) ? $this->getReturnLimitDate()->format('Y-m-d') : null,
                'outwardTime' => $this->getOutwardTime(),
                'returnTime' => $this->getReturnTime(),
                'priceKm' => $this->getPriceKm(),
                'outwardDriverPrice' => $this->getOutwardDriverPrice(),
                'seatsDriver' => $this->getSeatsDriver(),
                'seatsPassenger' => $this->getSeatsPassenger(),
                'luggage' => $this->hasLuggage(),
                'bike' => $this->hasBike(),
                'backSeats' => $this->hasBackSeats(),
                'message' => $this->getComment(),
                'origin' => $this->getOrigin(),
                'destination' => $this->getDestination(),
                'schedule' => $this->getSchedule(),
                'paused' => $this->isPaused(),
                'results' => $this->getResults(),
                'frequency' => $this->getFrequency(),
                'potentialCarpoolers' => $this->getPotentialCarpoolers(),
                'asks' => $this->getAsks(),
                'askId' => $this->getAskId(),
                'paymentStatus' => $this->getPaymentStatus(),
                'paymentItemId' => $this->getPaymentItemId(),
                'paymentItemWeek' => $this->getPaymentItemWeek(),
                'unpaidDate' => !is_null($this->getUnpaidDate()) ? $this->getUnpaidDate()->format('Y-m-d') : null,
                'gamificationNotifications' => $this->getGamificationNotifications(),
                'communities' => $this->getCommunities(),
            ];
    }
}
