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
 *          "normalization_context"={"groups"={"readProfileSummary","readPublicProfile"}, "enable_max_depth"="true"},
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
class ProfileSummary
{
    const PHONE_DISPLAY_RESTRICTED = 1;
    const PHONE_DISPLAY_ALL = 2;
    
    /**
     * @var int The id of the User
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $id;

    /**
     * @var string The given name of the User
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $givenName;

    /**
     * @var string The shorten family name of the User
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $shortFamilyName;

    /**
     * @var int The age of the User
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $age;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all).
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $phoneDisplay;

    /**
     * @var string|null The telephone number of the user.
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $telephone;

    /**
     * @var string|null Avatar of the user.
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $avatar;

    /**
     * @var int|null Nomber of carpool already done
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $carpoolRealized;

    /**
     * @var int|null Answer rate in percent
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $answerPct;

    /**
     * @var \DateTimeInterface User created date
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Last user activity date
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $lastActivityDate;

    /**
     * @var boolean|null If the User is experienced
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $experienced;

    /**
     * @var int|null The savedCo2 of this user in grams
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $savedCo2;

    /**
     * @var int|null The number of earned badges by the User
     *
     * @Groups({"readProfileSummary","readPublicProfile"})
     */
    private $numberOfBadges;

    public function __construct($id=null)
    {
        if (!is_null($id)) {
            $this->id = $id;
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

    public function setAge(?int $age): self
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
}
