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

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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
 *             "security"="is_granted('solidary_volunteer_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_volunteer_register',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidary_volunteers",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadCol"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_volunteer_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('solidary_volunteer_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *             "security"="is_granted('solidary_volunteer_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *             "security"="is_granted('solidary_volunteer_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidary_volunteers/{id}",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadItem"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_volunteer_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/solidary_volunteers/{id}",
 *              "method"="PATCH",
 *              "read"=false,
 *              "normalization_context"={"groups"={"aReadItem"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_solidary_volunteer_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryVolunteer
{
    public const DEFAULT_ID = 999999999999;
    public const TYPE = 'volunteer';
    public const AUTHORIZED_GENERIC_FILTERS = ['familyName', 'givenName', 'email'];
    public const VALIDATED_CANDIDATE_FILTER = 'validatedCandidate';
    public const DAYS_SLOTS = ['mMon', 'aMon', 'eMon', 'mTue', 'aTue', 'eTue', 'mWed', 'aWed', 'eWed', 'mThu', 'aThu', 'eThu', 'mFri', 'aFri', 'eFri', 'mSat', 'aSat', 'eSat', 'mSun', 'aSun', 'eSun'];
    public const TIMES_SLOTS = ['mMinTime', 'mMaxTime', 'aMinTime', 'aMaxTime', 'eMinTime', 'eMaxTime'];

    /**
     * @var int the id of this solidary user
     *
     * @ApiProperty(identifier=true)
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $id;

    /**
     * @var string the email of the user
     *
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $email;

    /**
     * @var string the encoded password of the user
     * @Groups({"writeSolidary"})
     */
    private $password;

    /**
     * @var null|int The gender of the user (1=female, 2=male, 3=nc)
     * @Groups({"readSolidary","aReadItem","writeSolidary"})
     */
    private $gender;

    /**
     * @var null|string the telephone number of the user
     * @Groups({"readSolidary","aReadItem","writeSolidary"})
     */
    private $telephone;

    /**
     * @var null|string the first name of the user
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $givenName;

    /**
     * @var null|string the family name of the user
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $familyName;

    /**
     * @var null|\DateTimeInterface the birth date of the user
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;

    /**
     * @var null|bool the user accepts to receive news about the platform
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     */
    private $newsSubscription;

    /**
     * @var User the user associated with the solidaryUser
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $user;

    /**
     * @var array The home address of this User
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $homeAddress;

    /**
     * @var null|string a comment about the solidaryUser
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var array The proofs associated to this user
     * @Groups({"writeSolidary","aReadItem"})
     */
    private $proofs;

    /**
     * @var null|bool If the candidate is validated or not
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $validatedCandidate;

    /**
     * @var array The diaries associated to this user
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     */
    private $diaries;

    /**
     * @var array The solidaries of this user
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $solidaries;

    /**
     * @var null|\DateTimeInterface morning min time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mMinTime;

    /**
     * @var null|\DateTimeInterface morning max time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mMaxTime;

    /**
     * @var null|\DateTimeInterface afternoon min time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aMinTime;

    /**
     * @var null|\DateTimeInterface afternoon max time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aMaxTime;

    /**
     * @var null|\DateTimeInterface evening min time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eMinTime;

    /**
     * @var null|\DateTimeInterface evening max time
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eMaxTime;

    /**
     * @var null|bool available on monday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mMon;

    /**
     * @var null|bool available on monday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aMon;

    /**
     * @var null|bool available on monday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eMon;

    /**
     * @var null|bool available on tuesday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mTue;

    /**
     * @var null|bool available on tuesday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aTue;

    /**
     * @var null|bool available on tuesday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eTue;

    /**
     * @var null|bool available on wednesday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mWed;

    /**
     * @var null|bool available on wednesday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aWed;

    /**
     * @var null|bool available on wednesday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eWed;

    /**
     * @var null|bool available on thursday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mThu;

    /**
     * @var null|bool available on thursday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aThu;

    /**
     * @var null|bool available on thursday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eThu;

    /**
     * @var null|bool available on friday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mFri;

    /**
     * @var null|bool available on friday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aFri;

    /**
     * @var null|bool available on friday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eFri;

    /**
     * @var null|bool available on saturday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mSat;

    /**
     * @var null|bool available on saturday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aSat;

    /**
     * @var null|bool available on saturday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eSat;

    /**
     * @var null|bool available on sunday morning
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $mSun;

    /**
     * @var null|bool available on sunday afternoon
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $aSun;

    /**
     * @var null|bool available on sunday evening
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $eSun;

    /**
     * @var array The solidary structures of this user
     * @Groups({"aReadCol","aReadItem"})
     */
    private $structures;

    /**
     * @var Structure The solidary structures of this user only in POST context
     * @Groups({"writeSolidary"})
     */
    private $structure;

    /**
     * @var null|int the maximum distance in metres allowed from the center address
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $maxDistance;

    /**
     * @var bool the solidaryUser has a vehicle
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $vehicle;

    /**
     * @var null|array the special needs for this solidary record
     *
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $needs;

    /**
     * @var \DateTimeInterface creation date
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $updatedDate;

    /**
     * @var null|string The avatar of the solidary beneficiary
     *
     * @Groups({"aReadItem"})
     */
    private $avatar;

    /**
     * @var null|int The userId of the solidary user
     * @Groups({"aReadItem"})
     */
    private $userId;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->diaries = [];
        $this->solidaries = [];
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

    public function getProofs(): ?array
    {
        return $this->proofs;
    }

    public function setProofs(?array $proofs): self
    {
        $this->proofs = $proofs;

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

    public function getStructures(): ?array
    {
        return $this->structures;
    }

    public function setStructures(?array $structures): self
    {
        $this->structures = $structures;

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

    public function setVehicle(?bool $vehicle): self
    {
        $this->vehicle = $vehicle;

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
