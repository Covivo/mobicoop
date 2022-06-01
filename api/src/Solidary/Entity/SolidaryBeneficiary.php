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
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;

/**
 * A solidary beneficiary.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *         "get"={
 *             "security"="is_granted('solidary_beneficiary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_beneficiary_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidary_beneficiaries",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadCol"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_beneficiary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('solidary_beneficiary_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *             "security"="is_granted('solidary_beneficiary_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *             "security"="is_granted('solidary_beneficiary_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidary_beneficiaries/{id}",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadItem"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_beneficiary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/solidary_beneficiaries/{id}",
 *              "method"="PATCH",
 *              "read"=false,
 *              "normalization_context"={"groups"={"aReadItem"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_solidary_beneficiary_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryBeneficiary
{
    public const DEFAULT_ID = 999999999999;
    public const TYPE = "beneficiary";
    public const AUTHORIZED_GENERIC_FILTERS = ['familyName','givenName','email'];
    public const VALIDATED_CANDIDATE_FILTER = 'validatedCandidate';

    /**
     * @var int The id of this solidary user.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $id;

    /**
     * @var string The email of the user.
     *
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $email;

    /**
     * @var string The encoded password of the user.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $password;

    /**
     * @var int|null The gender of the user (1=female, 2=male, 3=nc)
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $gender;

    /**
     * @var string|null The telephone number of the user.
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $telephone;

    /**
     * @var string|null The first name of the user.
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the user.
     * @Groups({"aReadCol","aReadItem","readSolidary","writeSolidary"})
     */
    private $familyName;

    /**
     * @var \DateTimeInterface|null The birth date of the user.
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
     * @var boolean|null The user accepts to receive news about the platform.
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     */
    private $newsSubscription;

    /**
     * @var User The user associated with the solidaryUser.
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $user;

    /**
     * @var array The home address of this beneficiary
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     */
    private $homeAddress;

    /**
     * @var bool If he has a vehicule
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $vehicule;

    /**
     * @var string|null A comment about the solidaryUser.
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var array The proofs associated to this user
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
     */
    private $proofs;

    /**
     * @var bool|null If the candidate is validated or not
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
     * @var array The solidary structures of this user
     * @Groups({"aReadCol","aReadItem","readSolidary"})
     */
    private $structures;

    /**
     * @var Structure|null The solidary structures of this user only in POST context
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

    /**
     * @var string|null The avatar of the solidary beneficiary
     *
     * @Groups({"aReadItem"})
     */
    private $avatar;

    /**
     * @var int|null The userId of the solidary user
     * @Groups({"aReadItem"})
     */
    private $userId;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->proofs = [];
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

    public function setEmail(?string $email): self
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

    public function hasVehicule(): ?bool
    {
        return $this->vehicule;
    }

    public function setVehicule(?bool $vehicule): self
    {
        $this->vehicule = $vehicule;

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
