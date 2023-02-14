<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\CarpoolStandard\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A booking.
 *
 * @ApiResource(
 *      routePrefix="/carpool_standard",
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *       collectionOperations={
 *          "carpool_standard_get"={
 *             "method"="GET",
 *             "path"="/bookings",
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          },
 *          "carpool_standard_post"={
 *              "method"="POST",
 *              "path"="/bookings",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "carpool_standard_get"={
 *             "method"="GET",
 *             "path"="/bookings/{id}",
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class Booking
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var string the id of the booking
     *
     * @Groups({"read"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var User the driver of the carpool
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $driver;

    /**
     * @var User the passenger of the carpool
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passenger;

    /**
     * @var int Passenger pickup datetime as a UNIX UTC timestamp in seconds
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passengerPickupDate;

    /**
     * @var float latitude of the passenger pick-up point
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passengerPickupLat;

    /**
     * @var float longitude of the passenger pick-up point
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passengerPickupLng;

    /**
     * @var float latitude of the passenger drop-off point
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passengerDropLat;

    /**
     * @var float longitude of the passenger drop-off point
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $passengerDropLng;

    /**
     * @var null|string string representing the pick-up address
     *
     * @Groups({"read", "write"})
     */
    private $passengerPickupAddress;

    /**
     * @var null|string string representing the drop-off address
     *
     * @Groups({"read", "write"})
     */
    private $passengerDropAddress;

    /**
     * @var string Status of the booking [WAITING_CONFIRMATION, CONFIRMED, CANCELLED, COMPLETED_PENDING_VALIDATION, VALIDATED]
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $status;

    /**
     * @var null|int carpooling duration in seconds
     *
     * @Groups({"read", "write"})
     */
    private $duration;

    /**
     * @var null|int carpooling distance in meters
     *
     * @Groups({"read", "write"})
     */
    private $distance;

    /**
     * @var null|string URL of the booking on the webservice provider platform
     *
     * @Groups({"read", "write"})
     */
    private $webUrl;

    /**
     * @var Price Price
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $price;

    /**
     * @var null|string ID of the Driver's journey to which the booking is related (if any) Unique given the User's operator property
     *
     * @Groups({"read", "write"})
     */
    private $driverJourneyId;

    /**
     * @var null|string ID of the Driver's journey to which the booking is related (if any) Unique given the User's operator property
     *
     * @Groups({"read", "write"})
     */
    private $passengerJourneyId;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDriver(): User
    {
        return $this->driver;
    }

    public function setDriver(User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPassenger(): User
    {
        return $this->passenger;
    }

    public function setPassenger(User $passenger): self
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function getPassengerPickupDate(): int
    {
        return $this->passengerPickupDate;
    }

    public function setPassengerPickupDate(int $passengerPickupDate): self
    {
        $this->passengerPickupDate = $passengerPickupDate;

        return $this;
    }

    public function getPassengerPickupLat(): float
    {
        return $this->passengerPickupLat;
    }

    public function setPassengerPickupLat(float $passengerPickupLat): self
    {
        $this->passengerPickupLat = $passengerPickupLat;

        return $this;
    }

    public function getPassengerPickupLng(): float
    {
        return $this->passengerPickupLng;
    }

    public function setPassengerPickupLng(float $passengerPickupLng): self
    {
        $this->passengerPickupLng = $passengerPickupLng;

        return $this;
    }

    public function getPassengerDropLat(): float
    {
        return $this->passengerDropLat;
    }

    public function setPassengerDropLat(float $passengerDropLat): self
    {
        $this->passengerDropLat = $passengerDropLat;

        return $this;
    }

    public function getPassengerDropLng(): float
    {
        return $this->passengerDropLng;
    }

    public function setPassengerDropLng(float $passengerDropLng): self
    {
        $this->passengerDropLng = $passengerDropLng;

        return $this;
    }

    public function getPassengerPickupAddress(): ?string
    {
        return $this->passengerPickupAddress;
    }

    public function setPassengerPickupAddress(?string $passengerPickupAddress): self
    {
        $this->passengerPickupAddress = $passengerPickupAddress;

        return $this;
    }

    public function getPassengerDropAddress(): ?string
    {
        return $this->passengerDropAddress;
    }

    public function setPassengerDropAddress(?string $passengerDropAddress): self
    {
        $this->passengerDropAddress = $passengerDropAddress;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getWebUrl(): ?string
    {
        return $this->webUrl;
    }

    public function setWebUrl(?string $webUrl): self
    {
        $this->webUrl = $webUrl;

        return $this;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function setPrice(Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDriverJourneyId(): ?string
    {
        return $this->driverJourneyId;
    }

    public function setDriverJourneyId(?string $driverJourneyId): self
    {
        $this->driverJourneyId = $driverJourneyId;

        return $this;
    }

    public function getPassengerJourneyId(): ?string
    {
        return $this->passengerJourneyId;
    }

    public function setPassengerJourneyId(?string $passengerJourneyId): self
    {
        $this->passengerJourneyId = $passengerJourneyId;

        return $this;
    }
}
