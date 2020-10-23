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
 * A ProfileSummary of a User
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readProfileSummary"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ProfileSummary
{
    /**
     * @var int The id of the User
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readProfileSummary"})
     */
    private $id;

    /**
     * @var string The given name of the User
     *
     * @Groups({"readProfileSummary"})
     */
    private $givenName;

    /**
     * @var string The shorten family name of the User
     *
     * @Groups({"readProfileSummary"})
     */
    private $shortFamilyName;

    /**
     * @var int The age of the User
     *
     * @Groups({"readProfileSummary"})
     */
    private $age;

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

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;
        
        return $this;
    }

    public function getShortFamilyName(): ?string
    {
        return $this->shortFamilyName;
    }

    public function setShortFamilyName(string $shortFamilyName): self
    {
        $this->shortFamilyName = $shortFamilyName;
        
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        
        return $this;
    }
}
