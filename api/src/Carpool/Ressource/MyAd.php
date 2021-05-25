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

namespace App\Carpool\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * Carpooling : an ad for the current api user.
 *
 * @ApiResource(
 *      collectionOperations={
 *          "get"={
 *              "path"="/my_carpools",
 *              "security"="is_granted('my_ad_list_self',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 *
 */
class MyAd
{
    const DEFAULT_ID = 999999999999;

    const PAYMENT_STATUS_NULL = -1;     // no payment for this ad
    const PAYMENT_STATUS_TODO = 1;      // there's a payment to validate (as a driver) or to pay (as a passenger)
    const PAYMENT_STATUS_PAID = 2;      // all payments are received (as a driver) or paid (as a passenger)

    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;

    /**
     * @var int The id of this ad.
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var boolean The ad is published.
     */
    private $published;

    /**
     * @var boolean The ad is paused.
     */
    private $paused;

    /**
     * @var boolean The user can be a driver.
     */
    private $roleDriver;

    /**
     * @var boolean The user can be a passenger.
     */
    private $rolePassenger;

    /**
     * @var int The ad frequency (1 = punctual; 2 = regular).
     */
    private $frequency;

    /**
     * @var string|null The original date of the outward (for punctual ads).
     */
    private $outwardDate;

    /**
     * @var string|null The original time of the outward (for punctual ads).
     */
    private $outwardTime;

    /**
     * @var string|null The original date of the return (for punctual ads).
     */
    private $returnDate;

    /**
     * @var string|null The original time of the return (for punctual ads).
     */
    private $returnTime;

    /**
     * @var string|null The date of the start of the ad (for regular ads).
     */
    private $fromDate;

    /**
     * @var string|null The date of the end of the ad (for regular ads).
     */
    private $toDate;

    /**
     * @var string|null The date of the start of the return ad (for regular ads).
     */
    private $returnFromDate;

    /**
     * @var string|null The date of the end of the return ad (for regular ads).
     */
    private $returnToDate;

    /**
     * @var array The schedule for regular ads.
     */
    private $schedule;

    /**
     * @var array The waypoints.
     */
    private $waypoints;

    /**
    * @var string|null The price per km.
    */
    private $priceKm;

    /**
    * @var string|null The total price selected by the user.
    */
    private $price;

    /**
     * @var int|null The number of seats available/required.
     */
    private $seats;

    /**
     * @var string|null A comment about the ad.
     */
    private $comment;

    /**
     * @var boolean The ad has asks.
     */
    private $asks;

    /**
     * @var array|null The details of the driver if the user is passenger and the ad has an accepted ask as passenger.
     */
    private $driver;

    /**
     * @var array|null The details of the passengers if the user is driver and the ad has accepted asks as driver.
     */
    private $passengers;

    /**
     * @var int The number of potential carpoolers of this ad.
     */
    private $carpoolers;

    /**
     * @var int The overall payment status of this ad.
     */
    private $paymentStatus;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
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
}
