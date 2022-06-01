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
 * Carpooling : an ask for a dynamic ad.
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
 *              "security_post_denormalize"="is_granted('dynamic_ask_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('dynamic_ask_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "read"=false,
 *              "normalization_context"={"groups"={"updateDynamic"}},
 *              "denormalization_context"={"groups"={"updateDynamic"}},
 *              "validation_groups"={"updateDynamic"},
 *              "security"="is_granted('dynamic_ask_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 *
 */
class DynamicAsk
{
    public const DEFAULT_ID = 999999999999;

    public const STATUS_PENDING = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_DECLINED = 3;
    public const STATUS_CANCELLED = 4;

    /**
     * @var int The id of this dynamic ad ask.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $id;

    /**
     * @var int Ask status (1 = pending, 2 = accepted; 3 = declined; 4 = cancelled).
     *
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $status;

    /**
     * @var string|null A message to send to the carpooler.
     *
     * @Groups({"readDynamic","writeDynamic","updateDynamic"})
     */
    private $message;

    /**
     * @var User|null The ask owner.
     *
     * @Groups("readDynamic")
     */
    private $user;

    /**
     * @var User|null The carpooler (user related to the ask).
     *
     * @Groups("readDynamic")
     */
    private $carpooler;

    /**
     * @var int|null The matching id related to the ask.
     *
     * @Groups("writeDynamic")
     */
    private $matchingId;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(?User $carpooler): self
    {
        $this->carpooler = $carpooler;

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
}
