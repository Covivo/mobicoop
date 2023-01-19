<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\CarpoolStandard\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A User.
 *
 * @ApiResource(
 *      routePrefix="/carpool_standard",
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "post"={
 *              "method"="POST",
 *              "path"="/users",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          },
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Standard"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class User
{
    /**
     * @var string The id of this user
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string the operator identifier
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $operator;

    /**
     * @var string User's alias
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $alias;

    /**
     * @var string user's first name
     *
     * @Groups({"read", "write"})
     */
    private $firstName;

    /**
     * @var string user's last name
     *
     * @Groups({"read", "write"})
     */
    private $lastName;

    /**
     * @var int user's grade from 1 to 5
     *
     * @Groups({"read", "write"})
     */
    private $grade;

    /**
     * @var string user's profile picture absolute URL
     *
     * @Groups({"read", "write"})
     */
    private $picture;

    /**
     * @var string User's gender. [ F, M, O ] 'O' stands for 'Other'.
     *
     * @Groups({"read", "write"})
     */
    private $gender;

    /**
     * @var bool
     *
     * @Groups({"read", "write"})
     */
    private $verifiedIdentity;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getVerifiedIdentity(): ?bool
    {
        return $this->verifiedIdentity;
    }

    public function setVerifiedIdentity(bool $verifiedIdentity): self
    {
        $this->verifiedIdentity = $verifiedIdentity;

        return $this;
    }
}
