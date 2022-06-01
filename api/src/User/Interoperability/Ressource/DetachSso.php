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
 */

namespace App\User\Interoperability\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Detach a User from it's sso provider source.
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
 *               "summary"="Erase the SsoId and the SsoProvider informations of the user account. Uuid or userId MUST BE FILLED. If both given, Uuid is ignored",
 *               "tags"={"Interoperability"},
 *               "parameters" = {
 *                    {
 *                        "name" = "uuid",
 *                        "type" = "string",
 *                        "required" = false,
 *                        "description" = "User's id in the sso provider's internal system"
 *                    },
 *                    {
 *                        "name" = "userId",
 *                        "type" = "int",
 *                        "required" = false,
 *                        "description" = "Instance user's id"
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
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DetachSso
{
    /**
     * @var string The User's id in the sso provider's system (if successful. null otherwise)
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readUser","writeUser"})
     */
    private $uuid;

    /**
     * @var int The User's id that has been detached (if successful. null otherwise)
     *
     * @Groups({"readUser","writeUser"})
     */
    private $userId;

    /**
     * @var bool If the User that has been detached was an already existing User not created by SSO
     *
     * @Groups({"readUser"})
     */
    private $previouslyExisting;

    /**
     * @var User The User related to the detachment
     */
    private $user;

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

    public function isPreviouslyExisting(): ?bool
    {
        return $this->previouslyExisting;
    }

    public function setPreviouslyExisting(?bool $previouslyExisting): self
    {
        $this->previouslyExisting = $previouslyExisting;

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
}
