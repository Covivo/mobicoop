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

namespace Mobicoop\Bundle\MobicoopBundle\Journey\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * Carpooling : a journey (carpool summary between 2 cities for a user)
 */
class Journey implements ResourceInterface, \JsonSerializable
{
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_DRIVER_OR_PASSENGER = 3;
    
    /**
     * @var int|null The id of this journey.
     */
    private $id;

    /**
     * @var int|null The proposal id for this journey
     */
    private $proposalId;

    /**
     * @var int|null The user id for this journey
     */
    private $userId;

    /**
     * @var string|null The name of the user.
     */
    private $userName;

    /**
     * @var int|null The age of the user
     */
    private $age;
    
    /**
     * @var string The origin of the journey
     */
    private $origin;

    /**
     * @var string The sanitized origin of the journey
     */
    private $originSanitized;

    /**
     * @var float|null The latitude of the origin.
     */
    private $latitudeOrigin;

    /**
     * @var float|null The longitude of the origin.
     */
    private $longitudeOrigin;

    /**
     * @var string The destination of the journey
     */
    private $destination;

    /**
     * @var string The sanitized destination of the journey
     */
    private $destinationSanitized;

    /**
     * @var float|null The latitude of the destination.
     */
    private $latitudeDestination;

    /**
     * @var float|null The longitude of the destination.
     */
    private $longitudeDestination;

    /**
     * @var int|null The proposal frequency (1 = punctual; 2 = regular).
     */
    private $frequency;

    /**
     * @var int The proposal type (1 = oneway; 2 = return trip).
     */
    private $type;

    /**
     * @var int|null The role for this journey (1 = driver; 2 = passenger; 3 = driver or passenger).
     */
    private $role;

    /**
     * @var \DateTimeInterface|null The starting date.
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The end date.
     */
    private $toDate;

    /**
     * @var \DateTimeInterface|null The starting time for a punctual journey.
     */
    private $time;

    /**
     * @var string|null The json representation of the possible days for a regular journey.
     */
    private $days;

    /**
     * @var string|null The json representation of the outward times for a regular journey.
     */
    private $outwardTimes;

    /**
     * @var string|null The json representation of the return times for a regular journey.
     */
    private $returnTimes;

    /**
     * @var \DateTimeInterface|null Creation date of the journey.
     */
    private $createdDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProposalId(): ?int
    {
        return $this->proposalId;
    }
    
    public function setProposalId(int $proposalId): self
    {
        $this->proposalId = $proposalId;

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

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }
    
    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }
    
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getOriginSanitized(): ?string
    {
        return $this->originSanitized;
    }
    
    public function setOriginSanitized(string $originSanitized): self
    {
        $this->originSanitized = $originSanitized;

        return $this;
    }

    public function getLatitudeOrigin()
    {
        return $this->latitudeOrigin;
    }

    public function setLatitudeOrigin($latitudeOrigin)
    {
        $this->latitudeOrigin = $latitudeOrigin;
    }

    public function getLongitudeOrigin()
    {
        return $this->longitudeOrigin;
    }

    public function setLongitudeOrigin($longitudeOrigin)
    {
        $this->longitudeOrigin = $longitudeOrigin;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }
    
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestinationSanitized(): ?string
    {
        return $this->destinationSanitized;
    }
    
    public function setDestinationSanitized(string $destinationSanitized): self
    {
        $this->destinationSanitized = $destinationSanitized;

        return $this;
    }

    public function getLatitudeDestination()
    {
        return $this->latitudeDestination;
    }

    public function setLatitudeDestination($latitudeDestination)
    {
        $this->latitudeDestination = $latitudeDestination;
    }

    public function getLongitudeDestination()
    {
        return $this->longitudeDestination;
    }

    public function setLongitudeDestination($longitudeDestination)
    {
        $this->longitudeDestination = $longitudeDestination;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
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

    public function getTime(): ?\DateTimeInterface
    {
        if ($this->time) {
            return \DateTime::createFromFormat('His', $this->time->format('His'));
        }
        return null;
    }

    public function setTime(?\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getOutwardTime()
    {
        if ($this->outwardTimes) {
            $times = json_decode($this->outwardTimes);
            $outwardTime = null;
            foreach ($times as $time) {
                if (is_null($time)) {
                    continue;
                }
                if (is_null($outwardTime)) {
                    $outwardTime = $time;
                } elseif ($outwardTime !== $time) {
                    $outwardTime = null;
                    break;
                }
            }
            if (!is_null($outwardTime)) {
                return \DateTime::createFromFormat('H:i:s', $outwardTime)->format('Y-m-d H:i:s');
            }
        }
        return null;
    }

    public function getReturnTime()
    {
        if ($this->returnTimes) {
            $times = json_decode($this->returnTimes);
            $returnTime = null;
            foreach ($times as $time) {
                if (is_null($time)) {
                    continue;
                }
                if (is_null($returnTime)) {
                    $returnTime = $time;
                } elseif ($returnTime !== $time) {
                    $returnTime = null;
                    break;
                }
            }
            if (!is_null($returnTime)) {
                return \DateTime::createFromFormat('H:i:s', $returnTime)->format('Y-m-d H:i:s');
            }
        }
        return null;
    }

    public function getDays()
    {
        if (!is_null($this->days)) {
            return array_map(function ($value) {
                return $value == "1" ? true : false;
            }, json_decode($this->days, true));
        }
        return null;
    }

    public function setDays(?string $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getOutwardTimes(): ?string
    {
        return $this->outwardTimes;
    }

    public function setOutwardTimes(?string $outwardTimes): self
    {
        $this->outwardTimes = $outwardTimes;

        return $this;
    }

    public function getReturnTimes(): ?string
    {
        return $this->returnTimes;
    }

    public function setReturnTimes(?string $returnTimes): self
    {
        $this->returnTimes = $returnTimes;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'proposalId' => $this->getProposalId(),
            'userId' => $this->getUserId(),
            'username' => $this->getUsername(),
            'age' => $this->getAge(),
            'origin' => $this->getOrigin(),
            'originSanitized' => $this->getOriginSanitized(),
            'latitudeOrigin' => $this->getLatitudeOrigin(),
            'longitudeOrigin' => $this->getLongitudeOrigin(),
            'destination' => $this->getDestination(),
            'destinationSanitized' => $this->getDestinationSanitized(),
            'latitudeDestination' => $this->getLatitudeDestination(),
            'longitudeDestination' => $this->getLongitudeDestination(),
            'frequency' => $this->getFrequency(),
            'type' => $this->getType(),
            'role' => $this->getRole(),
            'fromDate' => $this->getFromDate(),
            'toDate' => $this->getToDate(),
            'time' => $this->getTime(),
            'days' => $this->getDays(),
            'outwardTimes' => $this->getOutwardTimes(),
            'returnTimes' => $this->getReturnTimes(),
            'outwardTime' => $this->getOutwardTime(),
            'returnTime' => $this->getReturnTime(),
            'createdDate' => $this->getCreatedDate()
        ];
    }
}
