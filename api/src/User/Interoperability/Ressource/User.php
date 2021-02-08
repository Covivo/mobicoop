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

namespace App\User\Interoperability\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A User for Interoperability
 *
 * @ApiResource(
 *      routePrefix="/interoperability",
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeUser"}}
 *      },
 *      collectionOperations={
 *          "interop_get"={
 *             "method"="GET",
 *             "security"="is_granted('reject',object)"
 *          },
 *          "interop_post"={
 *             "method"="POST",
 *             "security_post_denormalize"="is_granted('interop_user_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "interop_get"={
 *             "method"="GET",
 *             "security"="is_granted('interop_user_read',object)"
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class User
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Block
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readUser"})
     */
    private $id;

    public function __construct(int $id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        } else {
            $this->id = self::DEFAULT_ID;
        }
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
}
