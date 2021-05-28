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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Detach a User from it's sso provider source
 *
 * @ApiResource(
 *      routePrefix="/interoperability",
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeUser"}}
 *      },
 *      collectionOperations={
 *          "interop_detach_sso"={
 *             "path"="/users/detachSso",
 *             "method"="POST",
 *             "security"="is_granted('interop_user_create',object)",
 *             "swagger_context" = {
 *               "summary"="Erase the SsoId and the SsoProvider informations of the user account",
 *               "tags"={"Interoperability"},
 *               "parameters" = {
 *                    {
 *                        "name" = "uuid",
 *                        "type" = "string",
 *                        "required" = true,
 *                        "description" = "User's id in the sso provider's internal system"
 *                    }
 *                }
 *             }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "method"="GET",
 *             "security"="is_granted('reject',object)",
 *             "swagger_context" = {
 *               "summary"="Not permitted",
 *               "tags"={"Interoperability"}
 *             }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DetachSso
{
    /**
     * @var string The User's id in the sso provider's system
     *
     * @ApiProperty(identifier=true)
     * @Assert\NotBlank
     * @Groups({"readUser","writeUser"})
     */
    private $uuid;

    /**
     * @var int The User's id that has been detached (if successful. null otherwise)
     *
     * @Groups({"readUser"})
     */
    private $userId;

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        
        return $this;
    }
}
