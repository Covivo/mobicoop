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

namespace App\User\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Entity\User;

/**
 * A Block made by a User
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readBlock"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeBlock"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *              "denormalization_context"={"groups"={"writeBlock"}},
 *              "normalization_context"={"groups"={"readBlock"}},
 *              "read"="false",
 *              "security_post_denormalize"="is_granted('block_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "blocked"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"readBlock"}},
 *              "path"="/blocked",
 *              "read"="false",
 *              "security"="is_granted('block_blocked',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "blockedBy"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"readBlock"}},
 *              "path"="/blockedBy",
 *              "read"="false",
 *              "security"="is_granted('block_blockedby',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Block
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Block
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readBlock"})
     */
    private $id;

    /**
     * @var User The User who made the Block
     *
     * @Assert\NotBlank
     * @Groups({"readBlock","writeBlock"})
     *
    */
    private $user;
    
    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readBlock"})
     */
    private $createdDate;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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
}
