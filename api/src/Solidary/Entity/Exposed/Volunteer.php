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

namespace App\Solidary\Entity\Exposed;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Geography\Entity\Address;
use App\Solidary\Entity\Structure;

/**
 * A solidary volunteer.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readVolunteer"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeVolunteer"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/volunteers",
 *              "normalization_context"={"groups"={"readVolunteer"}},
 *              "security"="is_granted('solidary_volunteer_list',object)"
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/volunteers",
 *              "security_post_denormalize"="is_granted('solidary_volunteer_create',object)"
 *          }
 *      },
 *      itemOperations={
 *         "get"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"readVolunteer"}},
 *              "path"="/volunteers/{id}",
 *              "security"="is_granted('solidary_volunteer_read',object)"
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "normalization_context"={"groups"={"writeVolunteer"}},
 *              "path"="/volunteers/{id}"
 *          }
 *          ,"delete"
 *      }
 * )
 */
class Volunteer
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this volunteer.
     *
     * @ApiProperty(identifier=true)
     * @Groups("readVolunteer")
     */
    private $id;

    /**
     * @var string The given name of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $givenName;

    /**
     * @var string The family name of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $familyName;

    /**
     * @var string The email of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $email;

    /**
     * @var string The password of this volunteer
     * @Groups({"writeVolunteer"})
     */
    private $password;

    /**
     * @var int|null The gender of this volunteer (1=female, 2=male, 3=nc)
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $gender;

    /**
     * @var \DateTimeInterface|null The birth date of this volunteer.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $birthDate;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all).
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $phoneDisplay;

    /**
     * @var \DateTimeInterface Morning min time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mMinTime;
    
    /**
     * @var \DateTimeInterface Morning max time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mMaxTime;
    
    /**
     * @var \DateTimeInterface Afternoon min time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aMinTime;
    
    /**
     * @var \DateTimeInterface Afternoon max time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aMaxTime;
    
    /**
     * @var \DateTimeInterface Evening min time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eMinTime;
    
    /**
     * @var \DateTimeInterface Evening max time.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eMaxTime;
    
    /**
     * @var bool Available on monday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mMon;
    
    /**
     * @var bool Available on monday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aMon;
    
    /**
     * @var bool Available on monday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eMon;
    
    /**
     * @var bool Available on tuesday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mTue;
    
    /**
     * @var bool Available on tuesday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aTue;
    
    /**
     * @var bool Available on tuesday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eTue;
    
    /**
     * @var bool Available on wednesday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mWed;
    
    /**
     * @var bool Available on wednesday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aWed;
    
    /**
     * @var bool Available on wednesday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eWed;
    
    /**
     * @var bool Available on thursday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mThu;
    
    /**
     * @var bool Available on thursday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aThu;
    
    /**
     * @var bool Available on thursday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eThu;
    
    /**
     * @var bool Available on friday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mFri;
    
    /**
     * @var bool Available on friday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aFri;
    
    /**
     * @var bool Available on friday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eFri;
    
    /**
     * @var bool Available on saturday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mSat;
    
    /**
     * @var bool Available on saturday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aSat;
    
    /**
     * @var bool Available on saturday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eSat;
    
    /**
     * @var bool Available on sunday morning.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $mSun;
    
    /**
     * @var bool Available on sunday afternoon.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $aSun;
    
    /**
     * @var bool Available on sunday evening.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $eSun;

    /**
     * @var Address The center address of the accepted perimeter.
     *
     * @Assert\NotBlank
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $address;

    /**
     * @var int|null The maximum distance in metres allowed from the center address.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $maxDistance;

    /**
     * @var bool The volunteer has a vehicle.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $vehicle;

    /**
     * @var int Structure of the volunteer.
     *
     * @Assert\NotBlank
     * @Groups({"readVolunteer","writeVolunteer"})
     * @MaxDepth(1)
     */
    private $structure;

    /**
     * @var string A comment about the volunteer.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $comment;

    /**
     * @var array|null The special needs proposed by the volunteer.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $needs;

    /**
     * @var array|null Volunteer proofs.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     * @ApiSubresource
     * @MaxDepth(1)
     */
    private $proofs;

    /**
     * @var int The user id attached to this volunteer.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $userId;
    
    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readVolunteer"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @Groups({"readVolunteer"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->needs = [];
        $this->proofs = [];
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

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getEmail(): string
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

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

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
    
    public function getMMinTime(): ?\DateTimeInterface
    {
        return $this->mMinTime;
    }

    public function setMMinTime(\DateTimeInterface $mMinTime): self
    {
        $this->mMinTime = $mMinTime;

        return $this;
    }
    
    public function getMMaxTime(): ?\DateTimeInterface
    {
        return $this->mMaxTime;
    }

    public function setMMaxTime(\DateTimeInterface $mMaxTime): self
    {
        $this->mMaxTime = $mMaxTime;

        return $this;
    }
    
    public function getAMinTime(): ?\DateTimeInterface
    {
        return $this->aMinTime;
    }

    public function setAMinTime(\DateTimeInterface $aMinTime): self
    {
        $this->aMinTime = $aMinTime;

        return $this;
    }
    
    public function getAMaxTime(): ?\DateTimeInterface
    {
        return $this->aMaxTime;
    }

    public function setAMaxTime(\DateTimeInterface $aMaxTime): self
    {
        $this->aMaxTime = $aMaxTime;

        return $this;
    }
    
    public function getEMinTime(): ?\DateTimeInterface
    {
        return $this->eMinTime;
    }

    public function setEMinTime(\DateTimeInterface $eMinTime): self
    {
        $this->eMinTime = $eMinTime;

        return $this;
    }
    
    public function getEMaxTime(): ?\DateTimeInterface
    {
        return $this->eMaxTime;
    }

    public function setEMaxTime(\DateTimeInterface $eMaxTime): self
    {
        $this->eMaxTime = $eMaxTime;

        return $this;
    }
    
    public function hasMMon(): ?bool
    {
        return $this->mMon;
    }
    
    public function setMMon(bool $mMon): self
    {
        $this->mMon = $mMon;

        return $this;
    }

    public function hasAMon(): ?bool
    {
        return $this->aMon;
    }

    public function setAMon(bool $aMon): self
    {
        $this->aMon = $aMon;

        return $this;
    }
    
    public function hasEMon(): ?bool
    {
        return $this->eMon;
    }
    
    public function setEMon(bool $eMon): self
    {
        $this->eMon = $eMon;

        return $this;
    }
    
    public function hasMTue(): ?bool
    {
        return $this->mTue;
    }
    
    public function setMTue(bool $mTue): self
    {
        $this->mTue = $mTue;

        return $this;
    }
    
    public function hasATue(): ?bool
    {
        return $this->aTue;
    }
   
    public function setATue(bool $aTue): self
    {
        $this->aTue = $aTue;

        return $this;
    }
    
    public function hasETue(): ?bool
    {
        return $this->eTue;
    }
   
    public function setETue(bool $eTue): self
    {
        $this->eTue = $eTue;

        return $this;
    }
    
    public function hasMWed(): ?bool
    {
        return $this->mWed;
    }
   
    public function setMWed(bool $mWed): self
    {
        $this->mWed = $mWed;

        return $this;
    }
    
    public function hasAWed(): ?bool
    {
        return $this->aWed;
    }
   
    public function setAWed(bool $aWed): self
    {
        $this->aWed = $aWed;

        return $this;
    }
    
    public function hasEWed(): ?bool
    {
        return $this->eWed;
    }
   
    public function setEWed(bool $eWed): self
    {
        $this->eWed = $eWed;

        return $this;
    }
    
    public function hasMThu(): ?bool
    {
        return $this->mThu;
    }
   
    public function setMThu(bool $mThu): self
    {
        $this->mThu = $mThu;

        return $this;
    }
    
    public function hasAThu(): ?bool
    {
        return $this->aThu;
    }
   
    public function setAThu(bool $aThu): self
    {
        $this->aThu = $aThu;

        return $this;
    }
    
    public function hasEThu(): ?bool
    {
        return $this->eThu;
    }
   
    public function setEThu(bool $eThu): self
    {
        $this->eThu = $eThu;

        return $this;
    }
    
    public function hasMFri(): ?bool
    {
        return $this->mFri;
    }
   
    public function setMFri(bool $mFri): self
    {
        $this->mFri = $mFri;

        return $this;
    }
    
    public function hasAFri(): ?bool
    {
        return $this->aFri;
    }
   
    public function setAFri(bool $aFri): self
    {
        $this->aFri = $aFri;

        return $this;
    }
    
    public function hasEFri(): ?bool
    {
        return $this->eFri;
    }
   
    public function setEFri(bool $eFri): self
    {
        $this->eFri = $eFri;

        return $this;
    }
    
    public function hasMSat(): ?bool
    {
        return $this->mSat;
    }
   
    public function setMSat(bool $mSat): self
    {
        $this->mSat = $mSat;

        return $this;
    }
    
    public function hasASat(): ?bool
    {
        return $this->aSat;
    }
   
    public function setASat(bool $aSat): self
    {
        $this->aSat = $aSat;

        return $this;
    }
    
    public function hasESat(): ?bool
    {
        return $this->eSat;
    }
   
    public function setESat(bool $eSat): self
    {
        $this->eSat = $eSat;

        return $this;
    }
    
    public function hasMSun(): ?bool
    {
        return $this->mSun;
    }
   
    public function setMSun(bool $mSun): self
    {
        $this->mSun = $mSun;

        return $this;
    }
    
    public function hasASun(): ?bool
    {
        return $this->aSun;
    }
   
    public function setASun(bool $aSun): self
    {
        $this->aSun = $aSun;

        return $this;
    }
    
    public function hasESun(): ?bool
    {
        return $this->eSun;
    }

    public function setESun(bool $eSun): self
    {
        $this->eSun = $eSun;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getMaxDistance(): ?int
    {
        return $this->maxDistance;
    }

    public function setMaxDistance(int $maxDistance): self
    {
        $this->maxDistance = $maxDistance;

        return $this;
    }

    public function hasVehicle(): ?bool
    {
        return $this->vehicle;
    }

    public function setVehicle(bool $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getStructure(): ?int
    {
        return $this->structure;
    }

    public function setStructure(?int $structure): self
    {
        $this->structure = $structure;

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

    public function getNeeds(): ?array
    {
        return $this->needs;
    }

    public function setNeeds(?array $needs): self
    {
        $this->needs = $needs;

        return $this;
    }


    public function getProofs(): ?array
    {
        return $this->proofs;
    }
    
    public function setProofs(?array $proofs): self
    {
        $this->proofs = $proofs;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
    
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        
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

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
