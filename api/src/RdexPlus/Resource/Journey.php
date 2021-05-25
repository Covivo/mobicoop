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
use App\RdexPlus\Entity\User;
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

    /**
     * @var string Journey's id
     *
     * @ApiProperty(identifier=true)
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $id;

    /**
     * @var string Journey's direct URL
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
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
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $operator;

    /**
     * @var string Journey's operator's website
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
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

    public function __construct(int $id = null)
    {
        if (is_null($id)) {
            $this->id = self::DEFAULT_ID;
        } else {
            $this->id = $id;
        }

        $this->user = new User();
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
}
