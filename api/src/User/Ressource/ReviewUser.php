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
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A User who left or is subject to a review
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReviewUser
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this ReviewUser.
     *
     * @Groups({"readReview","readPublicProfile"})
     */
    private $id;

    /**
     * @var string The given name of this ReviewUser
     *
     * @Groups({"readReview","readPublicProfile"})
     */
    private $givenName;

    /**
     * @var string The short family name of this ReviewUser
     *
     * @Groups({"readReview","readPublicProfile"})
     */
    private $shortFamilyName;

    /**
     * @var string The avatar of this ReviewUser
     *
     * @Groups({"readReview","readPublicProfile"})
     */
    private $avatar;
   
    public function __construct(int $id = null)
    {
        $this->id = self::DEFAULT_ID;
        if (!is_null($id)) {
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

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;
        
        return $this;
    }

    public function getShortFamilyName(): string
    {
        return $this->shortFamilyName;
    }

    public function setShortFamilyName(string $shortFamilyName): self
    {
        $this->shortFamilyName = $shortFamilyName;
        
        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        
        return $this;
    }
}
