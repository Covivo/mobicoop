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

namespace App\RdexPlus\Resource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\RdexPlus\Entity\Geopoint;
use App\RdexPlus\Entity\Price;
use App\RdexPlus\Entity\User;
use App\RdexPlus\Entity\Waypoint;
use App\RdexPlus\Entity\WaySchedule;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RDEX+ : Journey
 * The RDEX+ protocol does'nt require the POST route. We did it anyway.
 * @ApiResource(
 *      routePrefix="/interoperability",
 *      attributes={
 *          "normalization_context"={"groups"={"rdexPlusRead"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"rdexPlusWrite"}}
 *      },
 *      collectionOperations={
 *          "interop_get"={
 *              "method"="GET",
 *              "path"="/journeys",
 *              "security"="is_granted('ad_list',object)",
 *              "swagger_context" = {
 *                  "summary"="Search for matching journeys",
 *                  "tags"={"Interoperability", "RDEX+"}
 *              }
 *          },
 *          "interop_post"={
 *              "method"="POST",
 *              "path"="/journeys",
 *              "security_post_denormalize"="is_granted('ad_search_create',object)",
 *              "swagger_context" = {
 *                  "summary"="Publish a journey",
 *                  "tags"={"Interoperability", "RDEX+"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "interop_get_item"={
 *              "method"="GET",
 *              "path"="/journeys/{id}",
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Get a journey (not implemented)",
 *                  "tags"={"Interoperability", "RDEX+"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Journey
{
    const DEFAULT_ID = "999999999999";

    const TYPE_PLANNED = "planned";
    const TYPE_DYNAMIC = "dynamic";
    const TYPE_LINE = "line";

    const CARPOOLER_TYPE_DRIVER = "driver";
    const CARPOOLER_TYPE_PASSENGER = "passenger";
    const CARPOOLER_TYPE_BOTH = "both";

    const FREQUENCY_PUNCTUAL = "punctual";
    const FREQUENCY_REGULAR = "regular";
    const FREQUENCY_BOTH = "both";

    const TIME_MARGIN_DEFAULT = 900;

    /**
     * @var string Journey's id
     *
     * @ApiProperty(identifier=true)
     * @Groups({"rdexPlusRead"})
     */
    private $id;

    /**
     * @var string Journey's direct URL
     *
     * @Groups({"rdexPlusRead"})
     */
    private $webUrl;

    /**
     * @var string Journey's type (planned, dynamic, line)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $type;

    /**
     * @var string Journey's operator
     *
     * @Groups({"rdexPlusRead"})
     */
    private $operator;

    /**
     * @var string Journey's operator's website
     *
     * @Groups({"rdexPlusRead"})
     */
    private $operatorUrl;
    
    /**
     * @var string Journey's carpooler's type (driver, passenger, both)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $carpoolerType;

    /**
     * @var User Journey's carpooler
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $user;

    /**
     * @var int Journey's available seats (required if carpoolerType = driver or both)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $availableSeats;

    /**
     * @var int Journey's available seats (required if carpoolerType = passenger)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $requestedSeats;

    /**
     * @var Geopoint Journey's origin
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $from;
    
    /**
     * @var Geopoint Journey's destination
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $to;

    /**
     * @var int Journey's duration in seconds
     *
     * @Groups({"rdexPlusRead"})
     */
    private $duration;

    /**
     * @var int Journey's duration in seconds
     *
     * @Groups({"rdexPlusRead"})
     */
    private $distance;

    /**
     * @var int Journey's nomber of waypoints
     *
     * @Groups({"rdexPlusRead"})
     */
    private $numberOfWaypoints;
    
    /**
     * @var Waypoint[] Journey's waypoints (required if numberOfWaypoints>0)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $waypoints;

    /**
     * @var Price Journey's price
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $price;

    /**
     * @var string Journey's free comment
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $details;

    /**
     * @var string Journey's frequency (punctual, regular, both)
     * both : only on GET
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $frequency;

    /**
     * @var bool If the journey is a round trip
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $isRoundTrip;

    /**
     * @var bool If the journey is now stopped
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $isStopped;

    /**
     * @var WaySchedule Outward date, time and regular informations
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $outward;

    /**
     * @var WaySchedule Return date, time and regular informations (required if isRoundTrip = true)
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $return;
    
    public function __construct(int $id = null)
    {
        if (is_null($id)) {
            $this->id = self::DEFAULT_ID;
        } else {
            $this->id = $id;
        }

        $this->user = new User();
        $this->waypoints = [new Waypoint()];
        $this->price = new Price();
        $this->outward = new WaySchedule();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOperatorUrl(): ?string
    {
        return $this->operatorUrl;
    }

    public function setOperatorUrl(?string $operatorUrl): self
    {
        $this->operatorUrl = $operatorUrl;

        return $this;
    }

    public function getCarpoolerType(): ?string
    {
        return $this->carpoolerType;
    }

    public function setCarpoolerType(?string $carpoolerType): self
    {
        $this->carpoolerType = $carpoolerType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(?int $availableSeats): self
    {
        $this->availableSeats = $availableSeats;

        return $this;
    }
    
    public function getRequestedSeats(): ?int
    {
        return $this->requestedSeats;
    }

    public function setRequestedSeats(?int $requestedSeats): self
    {
        $this->requestedSeats = $requestedSeats;

        return $this;
    }

    public function getFrom(): ?Geopoint
    {
        return $this->from;
    }

    public function setFrom(?Geopoint $from): self
    {
        $this->from = $from;

        return $this;
    }
    
    public function getTo(): ?Geopoint
    {
        return $this->to;
    }

    public function setTo(?Geopoint $to): self
    {
        $this->to = $to;

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

    public function setistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }
    
    public function getNumberOfWaypoints(): ?int
    {
        return $this->numberOfWaypoints;
    }

    public function setNumberOfWaypoints(?int $numberOfWaypoints): self
    {
        $this->numberOfWaypoints = $numberOfWaypoints;

        return $this;
    }
    
    public function getWaypoints(): ?array
    {
        return $this->waypoints;
    }

    public function setWaypoints(?array $waypoints): self
    {
        $this->waypoints = $waypoints;

        return $this;
    }
    
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    public function setPrice(?Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getIsRoundTrip(): ?bool
    {
        return $this->isRoundTrip;
    }

    public function setIsRoundTrip(?bool $isRoundTrip): self
    {
        $this->isRoundTrip = $isRoundTrip;

        return $this;
    }

    public function getIsStopped(): ?bool
    {
        return $this->isStopped;
    }

    public function setIsStopped(?bool $isStopped): self
    {
        $this->isStopped = $isStopped;

        return $this;
    }

    public function getOutward(): ?WaySchedule
    {
        return $this->outward;
    }

    public function setOutward(?WaySchedule $outward): self
    {
        $this->outward = $outward;

        return $this;
    }
    
    public function getReturn(): ?WaySchedule
    {
        return $this->return;
    }

    public function setReturn(?WaySchedule $return): self
    {
        $this->return = $return;

        return $this;
    }
}
