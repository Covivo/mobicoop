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
 *             "security"="is_granted('reject',object)",
 *             "swagger_context" = {
 *               "summary"="Not permitted",
 *               "tags"={"Interoperability"}
 *             }
 *          },
 *          "interop_post"={
 *             "method"="POST",
 *             "security_post_denormalize"="is_granted('interop_user_create',object)",
 *             "swagger_context" = {
 *                  "summary"="Create a User created via interoperability",
 *                  "tags"={"Interoperability"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "givenName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's given name!"
 *                      },
 *                      {
 *                          "name" = "familyName",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's family name"
 *                      },
 *                      {
 *                          "name" = "email",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "User's email"
 *                      },
 *                      {
 *                          "name" = "password",
 *                          "type" = "string",
 *                          "required" = true,
 *                          "description" = "Clear version of the password"
 *                      },
 *                      {
 *                          "name" = "gender",
 *                          "type" = "int",
 *                          "enum" = {1,2,3},
 *                          "required" = true,
 *                          "description" = "User's gender (1 : female, 2 : male, 3 : other)"
 *                      },
 *                      {
 *                          "name" = "newsSubscription",
 *                          "type" = "boolean",
 *                          "required" = false,
 *                          "description" = "News subscription"
 *                      },
 *                      {
 *                          "name" = "externalId",
 *                          "type" = "int",
 *                          "required" = false,
 *                          "description" = "External id of the user (the id used in the partner's system)"
 *                      }
 *                  }
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "interop_get"={
 *             "path"="/users/{id}",
 *             "method"="GET",
 *             "security"="is_granted('interop_user_read',object)",
 *             "swagger_context" = {
 *               "summary"="Get a User created via interoperability. You can only GET the Users that you created",
 *               "tags"={"Interoperability"},
 *               "parameters" = {
 *                   {
 *                       "name" = "id",
 *                       "type" = "int",
 *                       "required" = true,
 *                       "description" = "User's id in our system"
 *                   }
 *               }
 *             }
 *          },
 *          "interop_put"={
 *             "path"="/users/{id}",
 *             "method"="PUT",
 *             "security"="is_granted('interop_user_update',object)",
 *             "swagger_context" = {
 *               "summary"="Update a User created via interoperability",
 *               "tags"={"Interoperability"}
 *             }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class User
{
    const DEFAULT_ID = 999999999999;

    const GENDER_FEMALE = 1;
    const GENDER_MALE = 2;
    const GENDER_OTHER = 3;

    const GENDERS = [
        self::GENDER_FEMALE,
        self::GENDER_MALE,
        self::GENDER_OTHER
    ];

    /**
     * @var int The id of this User
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readUser","writeUser"})
     */
    private $id;

    /**
     * @var string|null The first name of the user.
     *
     * @Assert\NotBlank
     * @Groups({"readUser","writeUser"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the user.
     *
     * @Assert\NotBlank
     * @Groups({"readUser","writeUser"})
     */
    private $familyName;

    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @Groups({"readUser","writeUser"})
     */
    private $email;

    /**
     * @var string The encoded password of the user.
     *
     * @Groups({"writeUser"})
     */
    private $password;

    /**
     * @var int|null The gender of the user (1=female, 2=male, 3=nc)
     * @Assert\NotBlank
     * @Groups({"readUser","writeUser"})
     */
    private $gender;

    /**
     * @var boolean|null The user accepts to receive news about the platform.
     *
     * @Groups({"readUser","writeUser"})
     */
    private $newsSubscription;

    /**
     * @var int The external id of this User
     *
     * @Groups({"readUser","writeUser"})
     */
    private $externalId;

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

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function hasNewsSubscription(): ?bool
    {
        return $this->newsSubscription;
    }

    public function setNewsSubscription(?bool $newsSubscription): self
    {
        $this->newsSubscription = $newsSubscription;

        return $this;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(?int $externalId): self
    {
        $this->externalId = $externalId;
        
        return $this;
    }
}
