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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;

/**
 * Carpooling : an ad fo a user.
 */
class MyAd extends GamificationEntity implements ResourceInterface, \JsonSerializable
{
    public const RESOURCE_NAME = 'my_carpools';

    public const PAYMENT_STATUS_NULL = -1;     // no payment for this ad
    public const PAYMENT_STATUS_TODO = 1;      // there's a payment to validate (as a driver) or to pay (as a passenger)
    public const PAYMENT_STATUS_PAID = 2;      // all payments are received (as a driver) or paid (as a passenger)

    public const FREQUENCY_PUNCTUAL = 1;
    public const FREQUENCY_REGULAR = 2;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;

    public const TYPE_ONE_WAY = 0;
    public const TYPE_OUTWARD = 1;
    public const TYPE_RETURN = 2;

    /**
     * @var int the id of this ad
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var bool the ad is published
     */
    private $published;

    /**
     * @var bool the ad is paused
     */
    private $paused;

    /**
     * @var bool the user can be a driver
     */
    private $roleDriver;

    /**
     * @var bool the user can be a passenger
     */
    private $rolePassenger;

    /**
     * @var int the ad frequency (1 = punctual; 2 = regular)
     */
    private $frequency;

    /**
     * @var null|string the original date of the outward (for punctual ads)
     */
    private $outwardDate;

    /**
     * @var null|string the original time of the outward (for punctual ads)
     */
    private $outwardTime;

    /**
     * @var null|string the original date of the return (for punctual ads)
     */
    private $returnDate;

    /**
     * @var null|string the original time of the return (for punctual ads)
     */
    private $returnTime;

    /**
     * @var null|string the date of the start of the ad (for regular ads)
     */
    private $fromDate;

    /**
     * @var null|string the date of the end of the ad (for regular ads)
     */
    private $toDate;

    /**
     * @var null|string the date of the start of the return ad (for regular ads)
     */
    private $returnFromDate;

    /**
     * @var null|string the date of the end of the return ad (for regular ads)
     */
    private $returnToDate;

    /**
     * @var array the schedule for regular ads
     */
    private $schedule;

    /**
     * @var array the waypoints
     */
    private $waypoints;

    /**
     * @var null|string the price per km
     */
    private $priceKm;

    /**
     * @var null|string the total price selected by the user
     */
    private $price;

    /**
     * @var null|int the number of seats available/required
     */
    private $seats;

    /**
     * @var null|string a comment about the ad
     */
    private $comment;

    /**
     * @var bool the ad has asks
     */
    private $asks;

    /**
     * @var null|array the details of the driver if the user is passenger and the ad has an accepted ask as passenger
     */
    private $driver;

    /**
     * @var null|array the details of the passengers if the user is driver and the ad has accepted asks as driver
     */
    private $passengers;

    /**
     * @var int the number of potential carpoolers of this ad
     */
    private $carpoolers;

    /**
     * @var int the overall payment status of this ad
     */
    private $paymentStatus;

    /**
     * @var null|int the type of the ad 0:oneWay, 1:outward, 2:return
     */
    private $type;

    private $solidaryExclusive;

    /**
     * @var null|MyAdCommunity[] If the Ad has a Communities
     */
    private $communities;

    /**
     * @var null|int
     *
     * @version 1.87
     */
    private $_linkedAd;

    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->waypoints = [];
        $this->schedule = [];
        $this->driver = [];
        $this->passengers = [];
        $this->carpoolers = 0;
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

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $isPublished): self
    {
        $this->published = $isPublished;

        return $this;
    }

    public function isPaused(): ?bool
    {
        return $this->paused;
    }

    public function setPaused(bool $ispaused): self
    {
        $this->paused = $ispaused;

        return $this;
    }

    public function hasRoleDriver(): ?bool
    {
        return $this->roleDriver;
    }

    public function setRoleDriver(bool $hasRoleDriver): self
    {
        $this->roleDriver = $hasRoleDriver;

        return $this;
    }

    public function hasRolePassenger(): ?bool
    {
        return $this->rolePassenger;
    }

    public function setRolePassenger(bool $hasRolePassenger): self
    {
        $this->rolePassenger = $hasRolePassenger;

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

    public function getOutwardDate(): ?string
    {
        return $this->outwardDate;
    }

    public function setOutwardDate(string $outwardDate): self
    {
        $this->outwardDate = $outwardDate;

        return $this;
    }

    public function getOutwardTime(): ?string
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(string $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

        return $this;
    }

    public function getReturnDate(): ?string
    {
        return $this->returnDate;
    }

    public function setReturnDate(string $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getReturnTime(): ?string
    {
        return $this->returnTime;
    }

    public function setReturnTime(string $returnTime): self
    {
        $this->returnTime = $returnTime;

        return $this;
    }

    public function getFromDate(): ?string
    {
        return $this->fromDate;
    }

    public function setFromDate(?string $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?string
    {
        return $this->toDate;
    }

    public function setToDate(?string $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getReturnFromDate(): ?string
    {
        return $this->returnFromDate;
    }

    public function setReturnFromDate(?string $returnFromDate): self
    {
        $this->returnFromDate = $returnFromDate;

        return $this;
    }

    public function getReturnToDate(): ?string
    {
        return $this->returnToDate;
    }

    public function setReturnToDate(?string $returnToDate): self
    {
        $this->returnToDate = $returnToDate;

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

    public function getWaypoints(): array
    {
        return $this->waypoints;
    }

    public function setWaypoints(array $waypoints): self
    {
        $this->waypoints = $waypoints;

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

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

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

    public function hasAsks(): ?bool
    {
        return $this->asks;
    }

    public function setAsks(bool $hasAsks): self
    {
        $this->asks = $hasAsks;

        return $this;
    }

    public function getDriver(): ?array
    {
        return $this->driver;
    }

    public function setDriver(?array $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPassengers(): ?array
    {
        return $this->passengers;
    }

    public function setPassengers(array $passengers)
    {
        $this->passengers = $passengers;

        return $this;
    }

    public function getCarpoolers(): int
    {
        return $this->carpoolers;
    }

    public function setCarpoolers(int $carpoolers): self
    {
        $this->carpoolers = $carpoolers;

        return $this;
    }

    public function getPaymentStatus(): int
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(int $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of solidaryExclusive.
     */
    public function isSolidaryExclusive()
    {
        return $this->solidaryExclusive;
    }

    /**
     * Set the value of solidaryExclusive.
     *
     * @param mixed $solidaryExclusive
     *
     * @return self
     */
    public function setSolidaryExclusive($solidaryExclusive)
    {
        $this->solidaryExclusive = $solidaryExclusive;

        return $this;
    }

    public function getcommunities(): ?array
    {
        return $this->communities;
    }

    public function setcommunities(?array $communities): self
    {
        $this->communities = $communities;

        return $this;
    }

    public function addCommunity(MyAdCommunity $community): self
    {
        $this->communities[] = $community;

        return $this;
    }

    public function getLinkedAd(): ?int
    {
        return $this->_linkedAd;
    }

    public function setLinkedAd($linkedAd): self
    {
        $this->_linkedAd = $linkedAd;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'linkedAd' => $this->getLinkedAd(),
                'published' => $this->isPublished(),
                'paused' => $this->isPaused(),
                'roleDriver' => $this->hasRoleDriver(),
                'rolePassenger' => $this->hasRolePassenger(),
                'frequency' => $this->getFrequency(),
                'outwardDate' => $this->getOutwardDate(),
                'outwardTime' => $this->getOutwardTime(),
                'returnDate' => $this->getReturnDate(),
                'returnTime' => $this->getReturnTime(),
                'fromDate' => $this->getFromDate(),
                'toDate' => $this->getToDate(),
                'returnFromDate' => $this->getReturnFromDate(),
                'returnToDate' => $this->getReturnToDate(),
                'schedule' => $this->getSchedule(),
                'waypoints' => $this->getWaypoints(),
                'priceKm' => $this->getPriceKm(),
                'price' => $this->getPrice(),
                'seats' => $this->getSeats(),
                'comment' => $this->getComment(),
                'asks' => $this->hasAsks(),
                'driver' => $this->getDriver(),
                'passengers' => $this->getPassengers(),
                'carpoolers' => $this->getCarpoolers(),
                'paymentStatus' => $this->getPaymentStatus(),
                'gamificationNotifications' => $this->getGamificationNotifications(),
                'isSolidaryExclusive' => $this->isSolidaryExclusive(),
                'type' => $this->getType(),
                'communities' => $this->getcommunities(),
            ];
    }
}
