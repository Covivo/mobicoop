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
class ProfileSummary implements ResourceInterface, \JsonSerializable
{
    public const PHONE_DISPLAY_RESTRICTED = 1;
    public const PHONE_DISPLAY_ALL = 2;

    /**
     * @var int The id of the User
     */
    private $id;

    /**
     * @var string The given name of the User
     */
    private $givenName;

    /**
     * @var string The shorten family name of the User
     */
    private $shortFamilyName;

    /**
     * @var int The age of the User
     */
    private $age;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all)
     */
    private $phoneDisplay;

    /**
     * @var null|string the telephone number of the user
     */
    private $telephone;

    /**
     * @var null|string avatar of the user
     */
    private $avatar;

    /**
     * @var null|int Nomber of carpool already done
     */
    private $carpoolRealized;

    /**
     * @var int User created date
     */
    private $answerPct;

    /**
     * @var \DateTimeInterface Nomber of carpool already done
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Last user activity date
     */
    private $lastActivityDate;

    /**
     * @var null|bool If the User is experienced
     */
    private $experienced;

    /**
     * @var null|int The savedCo2 of this user in grams
     */
    private $savedCo2;

    /**
     * @var null|bool True if the identity has been validated
     */
    private $verifiedIdentity;

    /**
     * @var null|int The number of earned badges by the User
     */
    private $numberOfBadges;

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

    public function isExperienced(): ?bool
    {
        return $this->experienced;
    }

    public function setExperienced(?bool $experienced): self
    {
        $this->experienced = $experienced;

        return $this;
    }

    public function getSavedCo2(): ?int
    {
        return $this->savedCo2;
    }

    public function setSavedCo2(?int $savedCo2): self
    {
        $this->savedCo2 = $savedCo2;

        return $this;
    }

    public function getNumberOfBadges(): ?int
    {
        return $this->numberOfBadges;
    }

    public function setNumberOfBadges(?int $numberOfBadges): self
    {
        $this->numberOfBadges = $numberOfBadges;

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
            'givenName' => $this->getGivenName(),
            'shortFamilyName' => $this->getShortFamilyName(),
            'age' => $this->getAge(),
            'phoneDisplay' => $this->getPhoneDisplay(),
            'telephone' => $this->getTelephone(),
            'avatar' => $this->getAvatar(),
            'carpoolRealized' => $this->getCarpoolRealized(),
            'answerPct' => $this->getAnswerPct(),
            'lastActivityDate' => $this->getLastActivityDate(),
            'createdDate' => $this->getCreatedDate(),
            'experienced' => $this->isExperienced(),
            'savedCo2' => $this->getSavedCo2(),
            'numberOfBadges' => $this->getNumberOfBadges(),
            'verifiedIdentity' => $this->getVerifiedIdentity(),
        ];
    }
}
