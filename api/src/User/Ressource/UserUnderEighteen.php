<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\User\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *              "denormalization_context"={"groups"={"write"}},
 *              "normalization_context"={"groups"={"read"}},
 *              "read"="false",
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "giveParentalConsent"={
 *              "method"="POST",
 *              "denormalization_context"={"groups"={"write"}},
 *              "normalization_context"={"groups"={"read"}},
 *              "path"="/user_under_eighteens/giveParentalConsent",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"read"}},
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "userUnderEighteenGetByUuid"={
 *             "method"="GET",
 *             "normalization_context"={"groups"={"read"}},
 *             "denormalization_context"={"groups"={"write"}},
 *             "path"="/user_under_eighteens/{id}/uuid",
 *             "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *      }
 * )
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserUnderEighteen
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Block
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string The User parentalConsentUuid
     *
     * @Groups({"write", "read"})
     */
    private $uuid;

    /**
     * @var string The User parentalConsentToken
     *
     * @Groups({"write"})
     */
    private $token;

    /**
     * @var string The User givenname
     *
     * @Groups({"read"})
     */
    private $givenName;

    /**
     * @var string The User familyname
     *
     * @Groups({"read"})
     */
    private $familyName;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }
}
