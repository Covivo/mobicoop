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
 */

namespace App\Carpool\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Carpool\Entity\Proposal;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
 *          "post"={
 *              "method"="POST",
 *              "normalization_context"={"groups"={"writeDynamic","results"}},
 *              "security_post_denormalize"="is_granted('dynamic_ad_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "active"={
 *              "method"="GET",
 *              "path"="/dynamics/active",
 *              "security"="is_granted('dynamic_ad_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "unfinished"={
 *              "method"="GET",
 *              "path"="/dynamics/unfinished",
 *              "security"="is_granted('dynamic_ad_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "read"=false,
 *              "security"="is_granted('dynamic_ad_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "read"=false,
 *              "normalization_context"={"groups"={"updateDynamic","results"}},
 *              "denormalization_context"={"groups"={"updateDynamic"}},
 *              "validation_groups"={"updateDynamic"},
 *              "security"="is_granted('dynamic_ad_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 */
class Dynamic
{
    public const DEFAULT_ID = 999999999999;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;

    /**
     * @var int the id of this dynamic ad
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readDynamic","updateDynamic"})
     */
    private $id;

    /**
     * @var int the role for this ad
     *
     * @Groups({"readDynamic","writeDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic"})
     */
    private $role;

    /**
     * @var \DateTimeInterface the date and time of the start of the dynamic ad
     *                         (= date and time of the creation of the dynamic ad, automatically filled to current utc time if not provided)
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $date;

    /**
     * @var array the waypoints
     *
     * @Groups({"readDynamic","writeDynamic"})
     * @Assert\NotBlank(groups={"writeDynamic"})
     */
    private $waypoints;

    /**
     * @var string the last latitude given
     *
     * @Groups("updateDynamic")
     * @Assert\NotBlank(groups={"updateDynamic"})
     */
    private $latitude;

    /**
     * @var string the last longitude given
     *
     * @Groups("updateDynamic")
     * @Assert\NotBlank(groups={"updateDynamic"})
     */
    private $longitude;

    /**
     * @var null|string the price per km
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $priceKm;

    /**
     * @var null|string the total price selected by the user
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $price;

    /**
     * @var null|int the number of seats available/required
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $seats;

    /**
     * @var null|string a comment about the ad
     *
     * @Groups({"readDynamic","writeDynamic"})
     */
    private $comment;

    /**
     * @var null|User the ad owner
     */
    private $user;

    /**
     * @var null|array the carpool results
     *
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $results;

    /**
     * @var null|array the filters to apply to the results
     */
    private $filters;

    /**
     * @var null|array the asks related to the ad
     *
     * @Groups({"readDynamic","updateDynamic"})
     */
    private $asks;

    /**
     * @var Proposal the proposal associated with the dynamic ad
     */
    private $proposal;

    /**
     * @var bool the destination is reached
     *
     * @Groups({"updateDynamic"})
     */
    private $destination;

    /**
     * @var bool the ad is finished
     *
     * @Groups({"updateDynamic"})
     */
    private $finished;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->waypoints = [];
        $this->results = [];
        $this->filters = [];
        $this->asks = [];
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAsks(): array
    {
        return $this->asks;
    }

    public function setAsks(array $asks)
    {
        $this->asks = $asks;

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

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;

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

    public function isDestination(): bool
    {
        return true === $this->destination ? true : false;
    }

    public function setDestination(bool $destination)
    {
        $this->destination = $destination;
    }

    public function isFinished(): bool
    {
        return true === $this->finished ? true : false;
    }

    public function setFinished(bool $finished)
    {
        $this->finished = $finished;
    }
}
