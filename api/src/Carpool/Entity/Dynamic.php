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

namespace App\Carpool\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Geography\Entity\Address;
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Constraints\UserIdProvided;

/**
 * Carpooling : an dynamic ad.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readDynamic"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeDynamic"}},
 *          "validation_groups"={"writeDynamic"}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "normalization_context"={"groups"={"writeDynamic","results"}},
 *              "security_post_denormalize"="is_granted('dynamic_create',object)"
 *          },
 *          "post_ask"={
 *              "method"="POST",
 *              "path"="/dynamics/ask",
 *              "security_post_denormalize"="is_granted('dynamic_ask_create',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "read"=false,
 *              "security"="is_granted('dynamic_read',object)"
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "read"=false,
 *              "normalization_context"={"groups"={"updateDynamic","results"}},
 *              "denormalization_context"={"groups"={"updateDynamic"}},
 *              "validation_groups"={"updateDynamic"},
 *              "security"="is_granted('dynamic_update',object)"
 *          },
 *          "put_ask"={
 *              "method"="PUT",
 *              "path"="/dynamics/ask/{id}",
 *              "read"=false,
 *              "security"="is_granted('dynamic_ask_update',object)"
 *          },
 *          "get_ask"={
 *              "method"="GET",
 *              "path"="/dynamics/ask/{id}",
 *              "read"=false,
 *              "security"="is_granted('dynamic_ask_read',object)"
 *          },
 *      }
 * )
 *
 */
class Dynamic
{
    const DEFAULT_ID = 999999999999;
    
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;

    /**
     * @var int The id of this dynamic ad.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readDynamic","updateDynamic"})
     */
    private $id;

    /**
     * @var int The role for this ad.
     *
     * @Groups({"readDynamic","writeDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic"})
     */
    private $role;

    /**
     * @var \DateTimeInterface The date and time of the start of the dynamic ad (= date and time of the creation of the dynamic ad, automatically filled).
     *
     * @Groups("readDynamic")
     */
    private $date;

    /**
     * @var array The waypoints.
     *
     * @Groups({"readDynamic","writeDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic"})
     */
    private $waypoints;

    /**
     * @var string The last latitude given.
     *
     * @Groups("updateDynamic")
     * @Assert\NotBlank(groups={"updateDynamic"})
     */
    private $latitude;

    /**
     * @var string The last longitude given.
     *
     * @Groups("updateDynamic")
     * @Assert\NotBlank(groups={"updateDynamic"})
     */
    private $longitude;

    /**
    * @var string|null The price per km.
    *
    * @Groups({"readDynamic","writeDynamic"})
    */
    private $priceKm;

    /**
    * @var string|null The total price selected by the user.
    *
    * @Groups({"readDynamic","writeDynamic"})
    */
    private $price;

    /**
     * @var int|null The number of seats available/required.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $seats;

    /**
     * @var string|null A comment about the ad.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $comment;

    /**
     * @var int|null The user id of the dynamic ad owner.
     *
     * @UserIdProvided(groups={"writeDynamic"})
     * @Groups("writeDynamic")
     */
    private $userId;

    /**
     * @var User|null The ad owner.
     *
     * @Groups("readDynamic")
     */
    private $user;
    
    /**
     * @var array|null The carpool results.
     *
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $results;

    /**
     * @var int|null The dynamic ad id for which the current ad is an ask (used when creating an ask for a dynamic ad).
     *
     * @Groups("writeDynamic")
     */
    private $dynamicId;

    /**
     * @var int|null The matching id related to the above ad id (used when creating an ask for a dynamic ad).
     *
     * @Groups("writeDynamic")
     */
    private $matchingId;

    /**
     * @var int The ask status if the ad concerns a given ask.
     *
     * @Groups("writeDynamic")
     */
    private $askStatus;

    /**
     * @var int The ask id if the ad concerns a given ask.
     *
     * @Groups("writeDynamic")
     */
    private $askId;

    /**
     * @var array|null The filters to apply to the results.
     *
     * @Groups("writeDynamic")
     */
    private $filters;

    /**
     * @var Proposal The proposal associated with the dynamic ad.
     */
    private $proposal;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->waypoints = [];
        $this->results = [];
        $this->filters = [];
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
    
    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

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

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

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

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;

        return $this;
    }

    public function getDynamicId(): ?int
    {
        return $this->dynamicId;
    }

    public function setDynamicId(?int $dynamicId): self
    {
        $this->dynamicId = $dynamicId;

        return $this;
    }

    public function getMatchingId(): ?int
    {
        return $this->matchingId;
    }

    public function setMatchingId(?int $matchingId): self
    {
        $this->matchingId = $matchingId;

        return $this;
    }

    public function getAskStatus(): ?int
    {
        return $this->askStatus;
    }

    public function setAskStatus(int $askStatus): self
    {
        $this->askStatus = $askStatus;

        return $this;
    }

    public function getAskId(): ?int
    {
        return $this->askId;
    }

    public function setAskId(int $askId): self
    {
        $this->askId = $askId;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters)
    {
        $this->filters = $filters;

        return $this;
    }
}
