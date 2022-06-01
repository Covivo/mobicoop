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

use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;
use App\Geography\Entity\Address;

/**
 * Carpooling : result for an ad.
 */
class Result
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this result.
     * @Groups("results")
     */
    private $id;

    /**
     * @var ResultRole|null The result with the requester as a driver and the carpooler as a passenger.
     * @Groups({"results","externalJourney"})
     */
    private $resultDriver;

    /**
     * @var ResultRole|null The result with the requester as a passenger and the carpooler as a driver.
     * @Groups({"results","externalJourney"})
     */
    private $resultPassenger;

    /**
     * @var int The role of this result (see Ad roles for constants)
     *
     * @Groups({"results"})
     */
    private $role;

    /**
     * @var User The carpooler found.
     * @Groups({"results","externalJourney"})
     */
    private $carpooler;

    /**
     * @var int The frequency of the ad (1 = punctual / 2 = regular).
     * @Groups({"results","externalJourney"})
     */
    private $frequency;

    /**
     * @var int The frequency of the matching proposal result (1 = punctual / 2 = regular).
     * @Groups("results")
     */
    private $frequencyResult;


    /**
     * @var Address The origin address to display.
     * @Groups({"results","externalJourney"})
     */
    private $origin;

    /**
     * @var boolean True if the origin is the first waypoint of the journey.
     * Groups("results")
     */
    private $originFirst;

    /**
     * @var Address The destination address to display.
     * @Groups({"results","externalJourney"})
     */
    private $destination;

    /**
     * @var boolean True if the destination is the last point of the journey.
     * Groups("results")
     */
    private $destinationLast;

    /**
     * @var Address The origin address of the driver.
     * Groups("results")
     */
    private $originDriver;

    /**
     * @var Address The destination address of the driver.
     * Groups("results")
     */
    private $destinationDriver;

    /**
     * @var Address The origin address of the passenger.
     * Groups("results")
     */
    private $originPassenger;

    /**
     * @var Address The destination address of the passenger.
     * Groups("results")
     */
    private $destinationPassenger;

    /**
     * @var Address The pickup outward address
     * @Groups({"results","externalJourney"})
     */
    private $pickUpOutward;

    /**
     * @var Address The pickup return address
     * @Groups({"results","externalJourney"})
     */
    private $pickUpReturn;

    /**
     * @var \DateTimeInterface|null The date to display.
     * @Groups({"results","externalJourney"})
     */
    private $date;

    /**
     * @var \DateTimeInterface|null The time to display.
     * @Groups({"results","externalJourney"})
     */
    private $time;

    /**
     * @var \DateTimeInterface|null The possible start date if regular.
     * @Groups({"results","externalJourney"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface|null The possible end date if regular.
     * @Groups({"results","externalJourney"})
     */
    private $toDate;

    /**
     * @var int The global number of places to display.
     * @Groups({"results","externalJourney"})
     */
    private $seats;

    /**
     * @var int The number of places offered to display.
     * @Groups("results")
     */
    private $seatsDriver;

    /**
     * @var int The number of places asked to display.
     * @Groups("results")
     */
    private $seatsPassenger;

    /**
     * @var string The computed price to display.
     * @Groups("results")
     */
    private $price;

    /**
     * @var string The computed rounded price to display.
     * @Groups({"results","externalJourney"})
     */
    private $roundedPrice;

    /**
     * @var string The comment to display.
     * @Groups("results")
     */
    private $comment;

    /**
     * @var int The detour distance in metres.
     * @Groups("results")
     */
    private $detourDistance;

    /**
     * @var int The detour duration in seconds.
     * @Groups("results")
     */
    private $detourDuration;

    /**
     * @var bool true : The detour is important enough to be "noticeable" (see .env)
     * @Groups("results")
     */
    private $noticeableDetour;

    /**
     * @var boolean|null The journey is available on mondays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $monCheck;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $tueCheck;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $wedCheck;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $thuCheck;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $friCheck;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $satCheck;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     * @Groups({"results","externalJourney"})
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null The outward time to display (if regular and unique).
     * @Groups({"results","externalJourney"})
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time to display (if regular and unique).
     * @Groups({"results","externalJourney"})
     */
    private $returnTime;

    /**
     * @var boolean|null The journey has a return trip.
     * @Groups({"results","externalJourney"})
     */
    private $return;

    /**
     * @var array|null The communities for this result
     * @Groups("results")
     */
    private $communities;

    /**
     * @var boolean If the Result has an initiated Ask
     * @Groups("results")
     */
    private $initiatedAsk;

    /**
     * @var boolean If the Result has a pending Ask
     * @Groups("results")
     */
    private $pendingAsk;

    /**
     * @var boolean If the Result has an accepted Ask
     * @Groups("results")
     */
    private $acceptedAsk;

    /**
     * @var string Url of the result if it's an external result (like RDEX)
     * @Groups("externalJourney")
     */
    private $externalUrl;

    /**
     * @var string Name of the external operator of the result if it's an external result (like RDEX)
     * @Groups("externalJourney")
     */
    private $externalOperator;

    /**
     * @var string Origin of the result if it's an external result (like RDEX)
     * @Groups("externalJourney")
     */
    private $externalOrigin;

    /**
     * @var string Provider of the result if it's an external result (like RDEX)
     * @Groups("externalJourney")
     */
    private $externalProvider;

    /**
     * @var string External journeyId of the result if it's an external result (like RDEX)
     * @Groups("externalJourney")
     */
    private $externalJourneyId;

    /**
     * @var int|null
     * @Groups("results")
     */
    private $askId;

    /**
     * @var boolean|null Solidary.
     *
     * @Groups("results")
     */
    private $solidary;

    /**
     * @var boolean|null Solidary exclusive.
     *
     * @Groups("results")
     */
    private $solidaryExclusive;

    /**
     * @var boolean UserId of the announcer of this ResultItem
     * @Groups("results")
     */
    private $userId;

    /**
     * @var boolean If the Result is owned by the caller
     * @Groups("results")
     */
    private $myOwn;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
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

    public function getResultDriver(): ?ResultRole
    {
        return $this->resultDriver;
    }

    public function setResultDriver(?ResultRole $resultDriver): self
    {
        $this->resultDriver = $resultDriver;

        return $this;
    }

    public function getResultPassenger(): ?ResultRole
    {
        return $this->resultPassenger;
    }

    public function setResultPassenger(?ResultRole $resultPassenger): self
    {
        $this->resultPassenger = $resultPassenger;

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

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(?User $carpooler): self
    {
        $this->carpooler = $carpooler;

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

    public function getFrequencyResult(): ?int
    {
        return $this->frequencyResult;
    }

    public function setFrequencyResult(int $frequencyResult): self
    {
        $this->frequencyResult = $frequencyResult;

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

    public function isOriginFirst(): ?bool
    {
        return $this->originFirst;
    }

    public function setOriginFirst(bool $isOriginFirst): self
    {
        $this->originFirst = $isOriginFirst;

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

    public function isDestinationLast(): ?bool
    {
        return $this->destinationLast;
    }

    public function setDestinationLast(bool $isDestinationLast): self
    {
        $this->destinationLast = $isDestinationLast;

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

    public function getPickUpOutward(): ?Address
    {
        return $this->pickUpOutward;
    }

    public function setPickUpOutward(?Address $pickUpOutward): self
    {
        $this->pickUpOutward = $pickUpOutward;

        return $this;
    }

    public function getPickUpReturn(): ?Address
    {
        return $this->pickUpReturn;
    }

    public function setPickUpReturn(?Address $pickUpReturn): self
    {
        $this->pickUpReturn = $pickUpReturn;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

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

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(?int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getSeatsDriver(): ?int
    {
        return $this->seatsDriver;
    }

    public function setSeatsDriver(?int $seatsDriver): self
    {
        $this->seatsDriver = $seatsDriver;

        return $this;
    }

    public function getSeatsPassenger(): ?int
    {
        return $this->seatsPassenger;
    }

    public function setSeatsPassenger(?int $seatsPassenger): self
    {
        $this->seatsPassenger = $seatsPassenger;

        return $this;
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
        return round($this->roundedPrice, 2);
    }

    public function setRoundedPrice(?string $roundedPrice)
    {
        $this->roundedPrice = $roundedPrice;
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

    public function getDetourDistance(): ?int
    {
        return $this->detourDistance;
    }

    public function setDetourDistance(int $detourDistance): self
    {
        $this->detourDistance = $detourDistance;

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

    public function hasNoticeableDetour(): ?bool
    {
        return $this->noticeableDetour;
    }

    public function setNoticeableDetour(bool $noticeableDetour): self
    {
        $this->noticeableDetour = $noticeableDetour;

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

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?\DateTimeInterface $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

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

    public function hasReturn(): ?bool
    {
        return $this->return;
    }

    public function setReturn(bool $hasReturn): self
    {
        $this->return = $hasReturn;

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

    public function hasInitiatedAsk(): ?bool
    {
        return $this->initiatedAsk;
    }

    public function setInitiatedAsk(?bool $initiatedAsk): self
    {
        $this->initiatedAsk = $initiatedAsk;

        return $this;
    }

    public function hasPendingAsk(): ?bool
    {
        return $this->pendingAsk;
    }

    public function setPendingAsk(?bool $pendingAsk): self
    {
        $this->pendingAsk = $pendingAsk;

        return $this;
    }

    public function hasAcceptedAsk(): ?bool
    {
        return $this->acceptedAsk;
    }

    public function setAcceptedAsk(?bool $acceptedAsk): self
    {
        $this->acceptedAsk = $acceptedAsk;

        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;

        return $this;
    }

    public function getExternalOperator(): ?string
    {
        return $this->externalOperator;
    }

    public function setExternalOperator(?string $externalOperator): self
    {
        $this->externalOperator = $externalOperator;

        return $this;
    }

    public function getExternalOrigin(): ?string
    {
        return $this->externalOrigin;
    }

    public function setExternalOrigin(?string $externalOrigin): self
    {
        $this->externalOrigin = $externalOrigin;

        return $this;
    }

    public function getExternalProvider(): ?string
    {
        return $this->externalProvider;
    }

    public function setExternalProvider(?string $externalProvider): self
    {
        $this->externalProvider = $externalProvider;

        return $this;
    }

    public function getExternalJourneyId(): ?string
    {
        return $this->externalJourneyId;
    }

    public function setExternalJourneyId(?string $externalJourneyId): self
    {
        $this->externalJourneyId = $externalJourneyId;

        return $this;
    }

    public function getAskId(): ?int
    {
        return $this->askId;
    }

    public function setAskId(?int $askId): Result
    {
        $this->askId = $askId;
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function isMyOwn(): bool
    {
        return $this->myOwn ? true : false;
    }

    public function setMyOwn(?bool $myOwn): self
    {
        $this->myOwn = $myOwn;

        return $this;
    }
}
