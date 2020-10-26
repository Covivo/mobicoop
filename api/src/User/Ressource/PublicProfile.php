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
 * A public profile of a User
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPublicProfile"}, "enable_max_depth"="true"},
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
class PublicProfile
{
    const PHONE_DISPLAY_RESTRICTED = 1;
    const PHONE_DISPLAY_ALL = 2;
    
    const SMOKE_NO = 0;
    const SMOKE_NOT_IN_CAR = 1;
    const SMOKE = 2;

    /**
     * @var int The id of the User
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPublicProfile"})
     */
    private $id;

    /**
     * @var string The given name of the User
     *
     * @Groups({"readPublicProfile"})
     */
    private $givenName;

    /**
     * @var string The shorten family name of the User
     *
     * @Groups({"readPublicProfile"})
     */
    private $shortFamilyName;

    /**
     * @var int The age of the User
     *
     * @Groups({"readPublicProfile"})
     */
    private $age;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all).
     *
     * @Groups({"readPublicProfile"})
     */
    private $phoneDisplay;

    /**
     * @var string|null The telephone number of the user.
     *
     * @Groups({"readPublicProfile"})
     */
    private $telephone;

    /**
     * @var string|null Avatar of the user.
     *
     * @Groups({"readPublicProfile"})
     */
    private $avatar;

    /**
     * @var int|null Nomber of carpool already done
     *
     * @Groups({"readPublicProfile"})
     */
    private $carpoolRealized;

    /**
     * @var int|null Answer rate in percent
     *
     * @Groups({"readPublicProfile"})
     */
    private $answerPct;

    /**
     * @var int|null Smoking preferences.
     * 0 = i don't smoke
     * 1 = i don't smoke in car
     * 2 = i smoke
     *
     * @Groups({"readPublicProfile"})
     */
    private $smoke;

    /**
     * @var boolean|null Music preferences.
     * 0 = no music
     * 1 = i listen to music or radio
     *
     * @Groups({"readPublicProfile"})
     */
    private $music;

    /**
     * @var string|null Music favorites.
     *
     * @Groups({"readPublicProfile"})
     */
    private $musicFavorites;

    /**
     * @var boolean|null Chat preferences.
     * 0 = no chat
     * 1 = chat
     *
     * @Groups({"readPublicProfile"})
     */
    private $chat;

    /**
     * @var string|null Chat favorite subjects.
     *
     * @Groups({"readPublicProfile"})
     */
    private $chatFavorites;

    /**
     * @var \DateTimeInterface User created date
     *
     * @Groups({"readPublicProfile"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Last user activity date
     *
     * @Groups({"readPublicProfile"})
     */
    private $lastActivityDate;

    /**
     * @var array|null Reviews about this user
     *
     * @Groups({"readPublicProfile"})
     */
    private $reviews;

    
    public function __construct($id=null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }

        $this->reviews = [];
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

    public function getPhoneDisplay(): ?int
    {
        return $this->phoneDisplay;
    }

    public function setPhoneDisplay(?int $phoneDisplay): self
    {
        $this->phoneDisplay = $phoneDisplay;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getCarpoolRealized(): ?int
    {
        return $this->carpoolRealized;
    }

    public function setCarpoolRealized(int $carpoolRealized): self
    {
        $this->carpoolRealized = $carpoolRealized;
        
        return $this;
    }

    public function getAnswerPct(): ?int
    {
        return $this->answerPct;
    }

    public function setAnswerPct(int $answerPct): self
    {
        $this->answerPct = $answerPct;
        
        return $this;
    }

    public function getSmoke(): ?int
    {
        return $this->smoke;
    }

    public function setSmoke(?int $smoke): self
    {
        $this->smoke = $smoke;

        return $this;
    }

    public function hasMusic(): ?bool
    {
        return $this->music;
    }

    public function setMusic(?bool $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getMusicFavorites(): ?string
    {
        return $this->musicFavorites;
    }

    public function setMusicFavorites(?string $musicFavorites): self
    {
        $this->musicFavorites = $musicFavorites;

        return $this;
    }

    public function hasChat(): ?bool
    {
        return $this->chat;
    }

    public function setChat(?bool $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getChatFavorites(): ?string
    {
        return $this->chatFavorites;
    }

    public function setChatFavorites(?string $chatFavorites): self
    {
        $this->chatFavorites = $chatFavorites;

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
    
    public function getLastActivityDate(): ?\DateTimeInterface
    {
        return $this->lastActivityDate;
    }

    public function setLastActivityDate(?\DateTimeInterface $lastActivityDate): self
    {
        $this->lastActivityDate = $lastActivityDate;

        return $this;
    }

    public function getReviews(): ?array
    {
        return $this->reviews;
    }

    public function setReviews(?array $reviews): self
    {
        $this->reviews = $reviews;

        return $this;
    }
}
