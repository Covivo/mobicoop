<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Interoperability\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Carpool\Interoperability\Entity\Schedule;
use App\Carpool\Interoperability\Entity\Waypoint;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : an Interoperability Ad.
 * @ApiResource(
 *      routePrefix="/interoperability",
 *      attributes={
 *          "normalization_context"={"groups"={"adRead"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"adWrite"}}
 *      },
 *      collectionOperations={
 *          "interop_get"={
 *              "method"="GET",
 *              "path"="/carpools",
 *              "security_post_denormalize"="is_granted('ad_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Interoperability"}
 *              }
 *          },
 *          "interop_post"={
 *              "method"="POST",
 *              "path"="/carpools",
 *              "security_post_denormalize"="is_granted('ad_search_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Interoperability"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "interop_get"={
 *              "method"="GET",
 *              "path"="/carpools/{id}",
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Interoperability"}
 *              }
 *           }
 *       }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Ad
{
    const DEFAULT_ID = 999999999999;
    
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_DRIVER_OR_PASSENGER = 3;

    const ROLES = [
        self::ROLE_DRIVER,
        self::ROLE_PASSENGER,
        self::ROLE_DRIVER_OR_PASSENGER
    ];

    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;

    const FREQUENCIES = [
        self::FREQUENCY_PUNCTUAL,
        self::FREQUENCY_REGULAR
    ];

    /**
     * @var int The id of this ad.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"adRead","adWrite"})
     */
    private $id;

    /**
     * @var int The role for this ad.
     *
     * @Assert\NotBlank
     * @Groups({"adWrite"})
     */
    private $role;

    /**
     * @var boolean|null The ad is a one way trip.
     *
     * @Groups({"adWrite"})
     */
    private $oneWay;

    /**
     * @var int|null The frequency for this ad.
     *
     * @Assert\NotBlank
     * @Groups({"adWrite"})
     */
    private $frequency;

    /**
     * @var Waypoint[]|null The waypoints for the outward.
     *
     * @Assert\NotBlank
     * @Groups({"adWrite"})
     */
    private $outwardWaypoints;

    /**
     * @var Waypoint[]|null The waypoints for the return.
     *
     * @Groups({"adWrite"})
     */
    private $returnWaypoints;

    /**
     * @var \DateTimeInterface|null The date for the outward if the frequency is punctual, the start date of the outward if the frequency is regular.
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     * @Groups({"adWrite"})
     */
    private $outwardDate;

    /**
     * @var \DateTimeInterface|null The limit date for the outward if the frequency is regular.
     *
     * @Groups({"adWrite"})
     */
    private $outwardLimitDate;

    /**
     * @var \DateTimeInterface|null The date for the return if the frequency is punctual, the start date of the return if the frequency is regular.
     *
     * @Groups({"adWrite"})
     */
    private $returnDate;

    /**
     * @var \DateTimeInterface|null The limit date for the return if the frequency is regular.
     *
     * @Groups({"adWrite"})
     */
    private $returnLimitDate;

    /**
     * @var string|null The time for the outward if the frequency is punctual.
     *
     * @Groups({"adWrite"})
     */
    private $outwardTime;

    /**
     * @var string|null The time for the return if the frequency is punctual.
     *
     * @Groups({"adWrite"})
     */
    private $returnTime;

    /**
     * @var Schedule[]|null The schedule if the frequency is regular.
     * The schedule contains the outward and return elements.
     *
     * @Groups({"adWrite"})
     */
    private $schedule;

    /**
    * @var int|null The price in cents
    *
     * @Groups({"adWrite"})
    */
    private $price;

    /**
     * @var int|null The number of seats available.
     *
     * @Groups({"adWrite"})
     */
    private $seats;

    /**
     * @var boolean|null Avoid motorway.
     *
     * @Groups({"adWrite"})
     */
    private $avoidMotorway;

    /**
     * @var boolean|null Avoid toll.
     *
     * @Groups({"adWrite"})
     */
    private $avoidToll;

    /**
     * @var string|null A comment about the ad.
     *
     * @Groups({"adWrite"})
     */
    private $comment;

    /**
     * @var int|null The user id of the ad owner. Null for an anonymous search.
     *
     * @Assert\NotBlank
     * @Groups({"adWrite"})
     */
    private $userId;

    /**
     * @var int|null The margin of the ad
     *
     * @Groups({"adWrite"})
     */
    private $marginDuration;

    public function __construct(int $id = null)
    {
        if (is_null($id)) {
            $this->id = self::DEFAULT_ID;
        } else {
            $this->id = $id;
        }
        $this->outwardWaypoints = [];
        $this->returnWaypoints = [];
        $this->schedule = [];
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

    public function getPrice(): ?int
    {
        return $this->price;
    }
    
    public function setPrice(?int $price)
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

    public function getMarginDuration(): ?int
    {
        return $this->marginDuration;
    }

    public function setMarginDuration(?int $marginDuration): self
    {
        $this->marginDuration = $marginDuration;

        return $this;
    }
}
