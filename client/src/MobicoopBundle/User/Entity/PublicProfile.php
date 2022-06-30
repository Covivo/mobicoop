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
 */

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * A User Profile Summary.
 */
class PublicProfile implements ResourceInterface, \JsonSerializable
{
    public const SMOKE_NO = 0;
    public const SMOKE_NOT_IN_CAR = 1;
    public const SMOKE = 2;

    /**
     * @var int The id of the User
     */
    private $id;

    /**
     * @var ProfileSummary Pofile Summary of the User
     */
    private $profileSummary;

    /**
     * @var null|int Smoking preferences.
     *               0 = i don't smoke
     *               1 = i don't smoke in car
     *               2 = i smoke
     */
    private $smoke;

    /**
     * @var null|bool Music preferences.
     *                0 = no music
     *                1 = i listen to music or radio
     */
    private $music;

    /**
     * @var null|string music favorites
     */
    private $musicFavorites;

    /**
     * @var null|bool Chat preferences.
     *                0 = no chat
     *                1 = chat
     */
    private $chat;

    /**
     * @var null|string chat favorite subjects
     */
    private $chatFavorites;

    /**
     * @var null|bool True if the review system is enabled
     */
    private $reviewActive;

    /**
     * @var null|array Reviews about this user
     */
    private $reviews;

    /**
     * @var null|bool True if the identity has been verified
     */
    private $verifiedIdentity;

    /**
     * @var null|array Badges won by this user
     */
    private $badges;

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

    public function getVerifiedIdentity()
    {
        return $this->verifiedIdentity;
    }

    public function setVerifiedIdentity($verifiedIdentity)
    {
        $this->verifiedIdentity = $verifiedIdentity;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'givenName' => $this->getProfileSummary()->getGivenName(),
            'shortFamilyName' => $this->getProfileSummary()->getShortFamilyName(),
            'age' => $this->getProfileSummary()->getAge(),
            'phoneDisplay' => $this->getProfileSummary()->getPhoneDisplay(),
            'telephone' => $this->getProfileSummary()->getTelephone(),
            'avatar' => $this->getProfileSummary()->getAvatar(),
            'carpoolRealized' => $this->getProfileSummary()->getCarpoolRealized(),
            'answerPct' => $this->getProfileSummary()->getAnswerPct(),
            'smoke' => $this->getSmoke(),
            'music' => $this->hasMusic(),
            'musicFavorites' => $this->getMusicFavorites(),
            'chat' => $this->hasChat(),
            'chatFavorites' => $this->getChatFavorites(),
            'lastActivityDate' => $this->getProfileSummary()->getLastActivityDate(),
            'createdDate' => $this->getProfileSummary()->getCreatedDate(),
            'reviewActive' => $this->isReviewActive(),
            'reviews' => $this->getReviews(),
            'savedCo2' => $this->getProfileSummary()->getSavedCo2(),
            'badges' => $this->getBadges(),
            'experienced' => $this->getProfileSummary()->isExperienced(),
            'verifiedIdentity' => $this->getProfileSummary()->getVerifiedIdentity(),
        ];
    }
}
