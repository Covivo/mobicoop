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
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;

/**
 * Carpooling : an dynamic ad.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readDynamic","results"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeDynamic"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/dynamics",
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
 *              "path"="/dynamics/{id}",
 *              "read"=false,
 *              "security"="is_granted('dynamic_read',object)"
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "path"="/dynamics/{id}",
 *              "read"=false,
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
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $id;

    /**
     * @var int The role for this ad.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $role;

    /**
     * @var \DateTimeInterface The date of dynamic ad.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $date;

    /**
     * @var array|null The waypoints.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $waypoints;
    
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
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $userId;

    /**
     * @var User|null The ad owner.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $user;
    
    /**
     * @var array|null The carpool results.
     *
     * @Groups("results")
     */
    private $results;

    /**
     * @var int|null The dynamic ad id for which the current ad is an ask.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $dynamicId;

    /**
     * @var int|null The matching id related to the above ad id.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $matchingId;

    /**
     * @var int The ask status if the ad concerns a given ask.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $askStatus;

    /**
     * @var int The ask id if the ad concerns a given ask.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $askId;

    /**
     * @var int The Id of the proposal associated to the ad.
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $proposalId;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->waypoints = [];
        $this->results = [];
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

    public function getWaypoints(): ?array
    {
        return $this->waypoints;
    }
    
    public function setWaypoints(?array $waypoints): self
    {
        $this->waypoints = $waypoints;
        
        return $this;
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

    public function getProposalId(): ?int
    {
        return $this->proposalId;
    }

    public function setProposalId(?int $proposalId): self
    {
        $this->proposalId = $proposalId;

        return $this;
    }
}
