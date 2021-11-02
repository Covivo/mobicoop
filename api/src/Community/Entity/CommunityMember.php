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

namespace App\Community\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Community Member : necessary infos about a community member
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunityMember
{
    /**
     * @var int
     * @Groups({"readCommunityMember"})
    */
    private $id;

    /**
     * @var string
     * @Groups({"readCommunityMember"})
    */
    private $firstName;

    /**
     * @var string
     * @Groups({"readCommunityMember"})
    */
    private $shortFamilyName;

    /**
     * @var bool
     * @Groups({"readCommunityMember"})
    */
    private $referrer;

    /**
     * @var bool
     * @Groups({"readCommunityMember"})
    */
    private $moderator;

    /**
     * @var string
     * @Groups({"readCommunityMember"})
     */
    private $avatar;

    public function __construct()
    {
        $this->referrer = false;
        $this->moderator = false;
        $this->avatar = "";
    }
    
    public function getid(): ?int
    {
        return $this->id;
    }

    public function setid(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getShortFamilyName(): ?string
    {
        return $this->shortFamilyName;
    }

    public function setShortFamilyName(?string $shortFamilyName): self
    {
        $this->shortFamilyName = $shortFamilyName;

        return $this;
    }

    public function isReferrer(): ?bool
    {
        return $this->referrer;
    }

    public function setReferrer(?bool $referrer): self
    {
        $this->referrer = $referrer;

        return $this;
    }
    
    public function isModerator(): ?bool
    {
        return $this->moderator;
    }

    public function setModerator(?bool $moderator): self
    {
        $this->moderator = $moderator;

        return $this;
    }
    
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }
}
