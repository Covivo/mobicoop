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

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary volunteer.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *         "get"={
 *             "security"="is_granted('solidary_volunteer_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_volunteer_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('solidary_volunteer_read',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('solidary_volunteer_update',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('solidary_volunteer_delete',object)"
 *          }
 *
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryVolunteer
{
    const DEFAULT_ID = 999999999999;
    const TYPE = "volunteer";

    /**
     * @var int The id of this solidary user.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $id;

    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $email;

    /**
     * @var string The encoded password of the user.
     * @Groups({"writeSolidary"})
     */
    private $password;

    /**
     * @var int|null The gender of the user (1=female, 2=male, 3=nc)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $gender;

    /**
     * @var string|null The telephone number of the user.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $telephone;

    /**
     * @var string|null The first name of the user.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the user.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $familyName;

    /**
     * @var \DateTimeInterface|null The birth date of the user.
     * @Groups({"readSolidary","writeSolidary"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;

    /**
     * @var boolean|null The user accepts to receive news about the platform.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $newsSubscription;

    /**
     * @var User The user associated with the solidaryUser.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $user;

    /**
     * @var array The home address of this User
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $homeAddress;

    /**
     * @var string|null A comment about the solidaryUser.
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var bool If the candidate is validated or not
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $validatedCandidate;

    /**
     * @var array The diaries associated to this user
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $diaries;

    /**
     * @var array The solidaries of this user
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $solidaries;

    /**
     * @var \DateTimeInterface|null Morning min time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMinTime;
    
    /**
     * @var \DateTimeInterface|null Morning max time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMaxTime;
    
    /**
     * @var \DateTimeInterface|null Afternoon min time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMinTime;
    
    /**
     * @var \DateTimeInterface|null Afternoon max time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMaxTime;
    
    /**
     * @var \DateTimeInterface|null Evening min time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMinTime;
    
    /**
     * @var \DateTimeInterface|null Evening max time.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMaxTime;
    
    /**
     * @var bool|null Available on monday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMon;
    
    /**
     * @var bool|null Available on monday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMon;
    
    /**
     * @var bool|null Available on monday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMon;
    
    /**
     * @var bool|null Available on tuesday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mTue;
    
    /**
     * @var bool|null Available on tuesday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aTue;
    
    /**
     * @var bool|null Available on tuesday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eTue;
    
    /**
     * @var bool|null Available on wednesday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mWed;
    
    /**
     * @var bool|null Available on wednesday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aWed;
    
    /**
     * @var bool|null Available on wednesday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eWed;
    
    /**
     * @var bool|null Available on thursday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mThu;
    
    /**
     * @var bool|null Available on thursday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aThu;
    
    /**
     * @var bool|null Available on thursday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eThu;
    
    /**
     * @var bool|null Available on friday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mFri;
    
    /**
     * @var bool|null Available on friday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aFri;
    
    /**
     * @var bool|null Available on friday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eFri;
    
    /**
     * @var bool|null Available on saturday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSat;
    
    /**
     * @var bool|null Available on saturday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSat;
    
    /**
     * @var bool|null Available on saturday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSat;
    
    /**
     * @var bool|null Available on sunday morning.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSun;
    
    /**
     * @var bool|null Available on sunday afternoon.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSun;
    
    /**
     * @var bool|null Available on sunday evening.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSun;

    /**
     * @var Structure The solidary structures of this user only in POST context
     * @Groups({"writeSolidary"})
     */
    private $structure;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->diaries = [];
        $this->solidaries = [];
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

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
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHomeAddress(): ?array
    {
        return $this->homeAddress;
    }

    public function setHomeAddress(?array $homeAddress): self
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        
        return $this;
    }

    public function isValidatedCandidate(): ?bool
    {
        return $this->validatedCandidate;
    }

    public function setValidatedCandidate(?bool $validatedCandidate): self
    {
        $this->validatedCandidate = $validatedCandidate;

        return $this;
    }

    public function getDiaries(): ?array
    {
        return $this->diaries;
    }

    public function setDiaries(?array $diaries): self
    {
        $this->diaries = $diaries;

        return $this;
    }

    public function getSolidaries(): ?array
    {
        return $this->solidaries;
    }

    public function setSolidaries(?array $solidaries): self
    {
        $this->solidaries = $solidaries;

        return $this;
    }

    public function getMMinTime(): ?\DateTimeInterface
    {
        return $this->mMinTime;
    }

    public function setMMinTime(?\DateTimeInterface $mMinTime): self
    {
        $this->mMinTime = $mMinTime;

        return $this;
    }
    
    public function getMMaxTime(): ?\DateTimeInterface
    {
        return $this->mMaxTime;
    }

    public function setMMaxTime(?\DateTimeInterface $mMaxTime): self
    {
        $this->mMaxTime = $mMaxTime;

        return $this;
    }
    
    public function getAMinTime(): ?\DateTimeInterface
    {
        return $this->aMinTime;
    }

    public function setAMinTime(?\DateTimeInterface $aMinTime): self
    {
        $this->aMinTime = $aMinTime;

        return $this;
    }
    
    public function getAMaxTime(): ?\DateTimeInterface
    {
        return $this->aMaxTime;
    }

    public function setAMaxTime(?\DateTimeInterface $aMaxTime): self
    {
        $this->aMaxTime = $aMaxTime;

        return $this;
    }
    
    public function getEMinTime(): ?\DateTimeInterface
    {
        return $this->eMinTime;
    }

    public function setEMinTime(?\DateTimeInterface $eMinTime): self
    {
        $this->eMinTime = $eMinTime;

        return $this;
    }
    
    public function getEMaxTime(): ?\DateTimeInterface
    {
        return $this->eMaxTime;
    }

    public function setEMaxTime(?\DateTimeInterface $eMaxTime): self
    {
        $this->eMaxTime = $eMaxTime;

        return $this;
    }
    
    public function hasMMon(): ?bool
    {
        return $this->mMon;
    }
    
    public function setMMon(?bool $mMon): self
    {
        $this->mMon = $mMon;

        return $this;
    }

    public function hasAMon(): ?bool
    {
        return $this->aMon;
    }

    public function setAMon(?bool $aMon): self
    {
        $this->aMon = $aMon;

        return $this;
    }
    
    public function hasEMon(): ?bool
    {
        return $this->eMon;
    }
    
    public function setEMon(?bool $eMon): self
    {
        $this->eMon = $eMon;

        return $this;
    }
    
    public function hasMTue(): ?bool
    {
        return $this->mTue;
    }
    
    public function setMTue(?bool $mTue): self
    {
        $this->mTue = $mTue;

        return $this;
    }
    
    public function hasATue(): ?bool
    {
        return $this->aTue;
    }
   
    public function setATue(?bool $aTue): self
    {
        $this->aTue = $aTue;

        return $this;
    }
    
    public function hasETue(): ?bool
    {
        return $this->eTue;
    }
   
    public function setETue(?bool $eTue): self
    {
        $this->eTue = $eTue;

        return $this;
    }
    
    public function hasMWed(): ?bool
    {
        return $this->mWed;
    }
   
    public function setMWed(?bool $mWed): self
    {
        $this->mWed = $mWed;

        return $this;
    }
    
    public function hasAWed(): ?bool
    {
        return $this->aWed;
    }
   
    public function setAWed(?bool $aWed): self
    {
        $this->aWed = $aWed;

        return $this;
    }
    
    public function hasEWed(): ?bool
    {
        return $this->eWed;
    }
   
    public function setEWed(?bool $eWed): self
    {
        $this->eWed = $eWed;

        return $this;
    }
    
    public function hasMThu(): ?bool
    {
        return $this->mThu;
    }
   
    public function setMThu(?bool $mThu): self
    {
        $this->mThu = $mThu;

        return $this;
    }
    
    public function hasAThu(): ?bool
    {
        return $this->aThu;
    }
   
    public function setAThu(?bool $aThu): self
    {
        $this->aThu = $aThu;

        return $this;
    }
    
    public function hasEThu(): ?bool
    {
        return $this->eThu;
    }
   
    public function setEThu(?bool $eThu): self
    {
        $this->eThu = $eThu;

        return $this;
    }
    
    public function hasMFri(): ?bool
    {
        return $this->mFri;
    }
   
    public function setMFri(?bool $mFri): self
    {
        $this->mFri = $mFri;

        return $this;
    }
    
    public function hasAFri(): ?bool
    {
        return $this->aFri;
    }
   
    public function setAFri(?bool $aFri): self
    {
        $this->aFri = $aFri;

        return $this;
    }
    
    public function hasEFri(): ?bool
    {
        return $this->eFri;
    }
   
    public function setEFri(?bool $eFri): self
    {
        $this->eFri = $eFri;

        return $this;
    }
    
    public function hasMSat(): ?bool
    {
        return $this->mSat;
    }
   
    public function setMSat(?bool $mSat): self
    {
        $this->mSat = $mSat;

        return $this;
    }
    
    public function hasASat(): ?bool
    {
        return $this->aSat;
    }
   
    public function setASat(?bool $aSat): self
    {
        $this->aSat = $aSat;

        return $this;
    }
    
    public function hasESat(): ?bool
    {
        return $this->eSat;
    }
   
    public function setESat(?bool $eSat): self
    {
        $this->eSat = $eSat;

        return $this;
    }
    
    public function hasMSun(): ?bool
    {
        return $this->mSun;
    }
   
    public function setMSun(?bool $mSun): self
    {
        $this->mSun = $mSun;

        return $this;
    }
    
    public function hasASun(): ?bool
    {
        return $this->aSun;
    }
   
    public function setASun(?bool $aSun): self
    {
        $this->aSun = $aSun;

        return $this;
    }
    
    public function hasESun(): ?bool
    {
        return $this->eSun;
    }

    public function setESun(?bool $eSun): self
    {
        $this->eSun = $eSun;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
