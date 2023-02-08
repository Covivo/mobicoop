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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A message.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class Message implements ResourceInterface, \JsonSerializable
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this payment week
     *
     * @Groups({"post"})
     */
    private $id;

    /**
     * @var User the sender of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"post"})
     */
    private $from;

    /**
     * @var User the recipient of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"post"})
     */
    private $to;

    /**
     * @var string the content of the message
     *
     * @Assert\NotBlank
     *
     * @Groups({"post"})
     */
    private $message;

    /**
     * @var string Defines if the recipient of this message is either the driver or the passenger. [DRIVER, PASSENGER]
     *
     * @Assert\NotBlank
     *
     * @Groups({"post"})
     */
    private $recipientCarpoolerType;

    /**
     * @var null|string ID of the Driver's journey to which the message is related (if any)
     *
     * @Groups({"post"})
     */
    private $driverJourneyId;

    /**
     * @var null|string ID of the Passenger's journey to which the message is related (if any)
     *
     * @Groups({"post"})
     */
    private $passengerJourneyId;

    /**
     * @var null|string ID ($uuid) of the booking to which the message is related (if any)
     *
     * @Groups({"post"})
     */
    private $bookingId;

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

    public function getBookingId(): ?string
    {
        return $this->bookingId;
    }

    public function setBookingId(?string $bookingId): self
    {
        $this->bookingId = $bookingId;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'from' => $this->getFrom(),
                'to' => $this->getTo(),
                'message' => $this->getMessage(),
                'recipientCarpoolerType' => $this->getRecipientCarpoolerType(),
                'driverJourneyId' => $this->getDriverJourneyId(),
                'passengerJourneyId' => $this->getPassengerJourneyId(),
                'bookingId' => $this->getBookingId(),
            ];
    }
}
