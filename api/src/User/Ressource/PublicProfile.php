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
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PublicProfile
{
    public const SMOKE_NO = 0;
    public const SMOKE_NOT_IN_CAR = 1;
    public const SMOKE = 2;

    /**
     * @var int The id of the User
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPublicProfile"})
     */
    private $id;

    /**
     * @var ProfileSummary Pofile Summary of the User
     * @Groups({"readPublicProfile"})
     */
    private $profileSummary;

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
     * @var bool|null True if the review system is enabled
     * @Groups({"readPublicProfile"})
     */
    private $reviewActive;

    /**
     * @var array|null Reviews about this user
     *
     * @Groups({"readPublicProfile"})
     */
    private $reviews;

    /**
     * @var array|null Badges won by this user
     *
     * @Groups({"readPublicProfile"})
     */
    private $badges;

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

    public function isReviewActive(): ?bool
    {
        return $this->reviewActive;
    }

    public function setReviewActive(?bool $reviewActive): self
    {
        $this->reviewActive = $reviewActive;

        return $this;
    }

    public function getProfileSummary(): ?ProfileSummary
    {
        return $this->profileSummary;
    }

    public function setProfileSummary(ProfileSummary $profileSummary): self
    {
        $this->profileSummary = $profileSummary;

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

    public function getReviews(): ?array
    {
        return $this->reviews;
    }

    public function setReviews(?array $reviews): self
    {
        $this->reviews = $reviews;

        return $this;
    }

    public function getBadges(): ?array
    {
        return $this->badges;
    }

    public function setBadges(?array $badges): self
    {
        $this->badges = $badges;

        return $this;
    }
}
