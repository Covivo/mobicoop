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
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : an ad for the current api user.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readMyAd"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeMyAd"}},
 *          "validation_groups"={"writeMyAd"}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('my_ad_list_self',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "read"=false,
 *              "security"="is_granted('my_ad_read_self',object)"
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
     * @Groups({"readMyAd","updateMyAd"})
     */
    private $id;

    /**
     * @var boolean The ad is published.
     *
     * @Groups("readMyAd")
     */
    private $published;

    /**
     * @var boolean The user can be a driver.
     *
     * @Groups("readMyAd")
     */
    private $roleDriver;

    /**
     * @var boolean The user can be a passenger.
     *
     * @Groups("readMyAd")
     */
    private $rolePassenger;

    /**
     * @var int The ad frequency (1 = punctual; 2 = regular).
     *
     * @Groups("readMyAd")
     */
    private $frequency;

    /**
     * @var \DateTimeInterface The original date and time of the start of the outward (for punctual ads).
     * Outward time could be different for an accepted carpool => depend on pickup time for a passenger
     *
     * @Groups("readMyAd")
     */
    private $outwardDate;

    /**
     * @var \DateTimeInterface The original date and time of the start of the return (for punctual ads).
     * Return time could be different for an accepted carpool => depend on pickup time for a passenger
     *
     * @Groups("readMyAd")
     */
    private $returnDate;

    /**
     * @var \DateTimeInterface|null The date of the start of the ad (for regular ads).
     *
     * @Groups("readMyAd")
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The date of the end of the ad (for regular ads).
     *
     * @Groups("readMyAd")
     */
    private $toDate;

    /**
     * @var array The schedule for regular ads.
     *
     * @Groups("readMyAd")
     */
    private $schedule;

    /**
     * @var array The waypoints.
     *
     * @Groups("readMyAd")
     */
    private $waypoints;

    /**
    * @var string|null The price per km.
    *
    * @Groups("readMyAd")
    */
    private $priceKm;

    /**
    * @var string|null The total price selected by the user.
    *
    * @Groups("readMyAd")
    */
    private $price;

    /**
     * @var int|null The number of seats available/required.
     *
     * @Groups("readMyAd")
     */
    private $seats;

    /**
     * @var string|null A comment about the ad.
     *
     * @Groups("readMyAd")
     */
    private $comment;

    /**
     * @var array|null The details of the driver if the user is passenger and the ad has an accepted ask as passenger.
     *
     * @Groups("readMyAd")
     */
    private $driver;

    /**
     * @var array|null The details of the passengers if the user is driver and the ad has accepted asks as driver.
     *
     * @Groups("readMyAd")
     */
    private $passengers;

    /**
     * @var int The number of potential carpoolers of this ad.
     *
     * @Groups("readMyAd")
     */
    private $carpoolers;

    /**
     * @var int The overall payment status of this ad.
     *
     * @Groups("readMyAd")
     */
    private $paymentStatus;

    /**
     * @var User The author of the ad.
     * Used for security check.
     */
    private $author;

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

    public function getOutwardDate(): ?\DateTimeInterface
    {
        return $this->outwardDate;
    }

    public function setOutwardDate(\DateTimeInterface $outwardDate): self
    {
        $this->outwardDate = $outwardDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(?\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
