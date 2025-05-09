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
 * A message.
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
 *             "path"="/messages",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          },
 *          "carpool_standard_post"={
 *              "method"="POST",
 *              "path"="/messages",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "carpool_standard_get"={
 *             "method"="GET",
 *             "path"="/messages/{id}",
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
class Message
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of the message
     *
     * @Groups({"read"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var User the sender of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"read", "write"})
     */
    private $from;

    /**
     * @var User the recipient of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"read", "write"})
     */
    private $to;

    /**
     * @var string the content of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"read", "write"})
     */
    private $message;

    /**
     * @var string Defines if the recipient of this message is either the driver or the passenger. [DRIVER, PASSENGER]
     *
     * @Assert\NotBlank
     *
     * @Groups({"read", "write"})
     */
    private $recipientCarpoolerType;

    /**
     * @var null|string ID of the Driver's journey to which the message is related (if any)
     *
     * @Groups({"read", "write"})
     */
    private $driverJourneyId;

    /**
     * @var null|string ID of the Passenger's journey to which the message is related (if any)
     *
     * @Groups({"read", "write"})
     */
    private $passengerJourneyId;

    /**
     * @var string ID ($uuid) of the booking to which the message is related (if any)
     *
     * @Groups({"read", "write"})
     */
    private $bookingId;

    /**
     * @var null|\DateTimeInterface The created dateTime of the lessage
     *
     * @Groups({"read", "write"})
     */
    private $createdDateTime;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFrom(): User
    {
        return $this->from;
    }

    public function setFrom(User $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): User
    {
        return $this->to;
    }

    public function setTo(User $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getRecipientCarpoolerType(): string
    {
        return $this->recipientCarpoolerType;
    }

    public function setRecipientCarpoolerType(string $recipientCarpoolerType): self
    {
        $this->recipientCarpoolerType = $recipientCarpoolerType;

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

    public function getBookingId(): string
    {
        return $this->bookingId;
    }

    public function setBookingId(string $bookingId): self
    {
        $this->bookingId = $bookingId;

        return $this;
    }

    public function getCreatedDateTime(): ?\DateTimeInterface
    {
        return $this->createdDateTime;
    }

    public function setCreatedDateTime(\DateTimeInterface $createdDateTime): self
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
    }
}
