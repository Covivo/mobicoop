<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Geography\Entity\Territory;
use App\Image\Entity\Image;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary structure.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary","readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('structure_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('structure_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "structure_geolocation"={
 *              "method"="GET",
 *              "path"="/structures/geolocation",
 *              "normalization_context"={"groups"={"readSolidary"}},
 *              "security"="is_granted('structure_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/structures",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadCol"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_structure_list',object)"
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/structures",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_structure_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('structure_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *             "security"="is_granted('structure_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *             "security"="is_granted('structure_delete',object)",
 *             "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/structures/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_structure_read',object)"
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/structures/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_structure_update',object)"
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/structures/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_structure_delete',object)"
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Structure
{
    /**
     * @var int the id of this structure
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","aReadCol","readSolidary","writeSolidary","readUser"})
     */
    private $id;

    /**
     * @var string name of the structure
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aReadCol","aWrite","readSolidary","writeSolidary","readUser"})
     */
    private $name;

    /**
     * @var string the email of the structure
     *
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary","readUser"})
     */
    private $email;

    /**
     * @var null|string the telephone number of the structure
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aReadCol","aWrite","readSolidary","writeSolidary","readUser"})
     */
    private $telephone;

    /**
     * @var bool auto approval of beneficiaries
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $beneficiaryAutoApproval;

    /**
     * @var null|int Solidary record create mode :
     *               - 0 or null : full mode
     *               - 1 : light mode
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aReadCol","aWrite"})
     */
    private $mode;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readStructure"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readStructure"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface morning min range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mMinRangeTime;

    /**
     * @var \DateTimeInterface morning max range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mMaxRangeTime;

    /**
     * @var \DateTimeInterface afternoon min range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aMinRangeTime;

    /**
     * @var \DateTimeInterface afternoon max range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aMaxRangeTime;

    /**
     * @var \DateTimeInterface evening min range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eMinRangeTime;

    /**
     * @var \DateTimeInterface evening max range time
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eMaxRangeTime;

    /**
     * @var \DateTimeInterface morning min time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mMinTime;

    /**
     * @var \DateTimeInterface morning max time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mMaxTime;

    /**
     * @var \DateTimeInterface afternoon min time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aMinTime;

    /**
     * @var \DateTimeInterface afternoon max time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aMaxTime;

    /**
     * @var \DateTimeInterface evening min time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eMinTime;

    /**
     * @var \DateTimeInterface evening max time
     *
     * @ORM\Column(type="time")
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eMaxTime;

    /**
     * @var bool available on monday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mMon;

    /**
     * @var bool available on monday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aMon;

    /**
     * @var bool available on monday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eMon;

    /**
     * @var bool available on tuesday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mTue;

    /**
     * @var bool available on tuesday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aTue;

    /**
     * @var bool available on tuesday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eTue;

    /**
     * @var bool available on wednesday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mWed;

    /**
     * @var bool available on wednesday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aWed;

    /**
     * @var bool available on wednesday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eWed;

    /**
     * @var bool available on thursday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mThu;

    /**
     * @var bool available on thursday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aThu;

    /**
     * @var bool available on thursday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eThu;

    /**
     * @var bool available on friday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mFri;

    /**
     * @var bool available on friday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aFri;

    /**
     * @var bool available on friday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eFri;

    /**
     * @var bool available on saturday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mSat;

    /**
     * @var bool available on saturday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aSat;

    /**
     * @var bool available on saturday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eSat;

    /**
     * @var bool available on sunday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $mSun;

    /**
     * @var bool available on sunday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $aSun;

    /**
     * @var bool available on sunday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readSolidary","writeSolidary"})
     */
    private $eSun;

    /**
     * @var Structure parent structure
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="structures")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $structure;

    /**
     * @var null|ArrayCollection child structures
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Structure", mappedBy="structure")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $structures;

    /**
     * @var null|ArrayCollection the solidary user for this structure
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryUserStructure", mappedBy="structure")
     * @MaxDepth(1)
     */
    private $solidaryUserStructures;

    /**
     * @var null|ArrayCollection the subjects for this structure
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Subject", mappedBy="structure", cascade={"persist"})
     * @Groups({"aRead","readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     */
    private $subjects;

    /**
     * @var null|ArrayCollection the special needs for this structure
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need", inversedBy="structures", cascade={"persist"})
     * @Groups({"aRead","readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     */
    private $needs;

    /**
     * @var null|ArrayCollection the relay points related to the structure
     *
     * @ORM\OneToMany(targetEntity="\App\RelayPoint\Entity\RelayPoint", mappedBy="structure", cascade={"persist"})
     * @MaxDepth(1)
     */
    private $relayPoints;

    /**
     * @var null|ArrayCollection the proofs for this structure
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\StructureProof", mappedBy="structure", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"aRead","readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $structureProofs;

    /**
     * @var null|ArrayCollection A Structure can have multiple entry in Operate
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Operate", mappedBy="structure", cascade={"persist"})
     * @MaxDepth(1)
     */
    private $operates;

    /**
     * @var null|ArrayCollection The Territories linked to this Structure
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory", inversedBy="structures")
     * @Groups({"aRead","readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $territories;

    /**
     * @var null|ArrayCollection the images of the structure
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="structure", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"aRead","readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $images;

    /**
     * @var null|string the signature of the structure
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aReadCol","aWrite","readSolidary","writeSolidary","readUser"})
     */
    private $signature;

    /**
     * @var array Operators for this structure (more direct than operates for admin context)
     * @Groups("aRead")
     */
    private $operators;

    /**
     * @var bool the structure is removable (not removable if it is used for a solidary record)
     *
     * @Groups("aRead")
     */
    private $removable;

    public function __construct()
    {
        $this->solidaries = new ArrayCollection();
        $this->structures = new ArrayCollection();
        $this->solidaryUserStructures = new ArrayCollection();
        $this->operates = new ArrayCollection();
        $this->subjects = new ArrayCollection();
        $this->needs = new ArrayCollection();
        $this->relayPoints = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->structureProofs = new ArrayCollection();
        $this->territories = new ArrayCollection();
        $this->operators = [];
        $this->images = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function hasBeneficiaryAutoApproval(): ?bool
    {
        return $this->beneficiaryAutoApproval ? true : false;
    }

    public function setBeneficiaryAutoApproval(bool $beneficiaryAutoApproval): self
    {
        $this->beneficiaryAutoApproval = $beneficiaryAutoApproval;

        return $this;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

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

    public function getMMinRangeTime(): ?\DateTimeInterface
    {
        return $this->mMinRangeTime;
    }

    public function setMMinRangeTime(\DateTimeInterface $mMinRangeTime): self
    {
        $this->mMinRangeTime = $mMinRangeTime;

        return $this;
    }

    public function getMMaxRangeTime(): ?\DateTimeInterface
    {
        return $this->mMaxRangeTime;
    }

    public function setMMaxRangeTime(\DateTimeInterface $mMaxRangeTime): self
    {
        $this->mMaxRangeTime = $mMaxRangeTime;

        return $this;
    }

    public function getAMinRangeTime(): ?\DateTimeInterface
    {
        return $this->aMinRangeTime;
    }

    public function setAMinRangeTime(\DateTimeInterface $aMinRangeTime): self
    {
        $this->aMinRangeTime = $aMinRangeTime;

        return $this;
    }

    public function getAMaxRangeTime(): ?\DateTimeInterface
    {
        return $this->aMaxRangeTime;
    }

    public function setAMaxRangeTime(\DateTimeInterface $aMaxRangeTime): self
    {
        $this->aMaxRangeTime = $aMaxRangeTime;

        return $this;
    }

    public function getEMinRangeTime(): ?\DateTimeInterface
    {
        return $this->eMinRangeTime;
    }

    public function setEMinRangeTime(\DateTimeInterface $eMinRangeTime): self
    {
        $this->eMinRangeTime = $eMinRangeTime;

        return $this;
    }

    public function getEMaxRangeTime(): ?\DateTimeInterface
    {
        return $this->eMaxRangeTime;
    }

    public function setEMaxRangeTime(\DateTimeInterface $eMaxRangeTime): self
    {
        $this->eMaxRangeTime = $eMaxRangeTime;

        return $this;
    }

    public function getMMinTime(): \DateTimeInterface
    {
        return $this->mMinTime;
    }

    public function setMMinTime(\DateTimeInterface $mMinTime): self
    {
        $this->mMinTime = $mMinTime;

        return $this;
    }

    public function getMMaxTime(): \DateTimeInterface
    {
        return $this->mMaxTime;
    }

    public function setMMaxTime(\DateTimeInterface $mMaxTime): self
    {
        $this->mMaxTime = $mMaxTime;

        return $this;
    }

    public function getAMinTime(): \DateTimeInterface
    {
        return $this->aMinTime;
    }

    public function setAMinTime(\DateTimeInterface $aMinTime): self
    {
        $this->aMinTime = $aMinTime;

        return $this;
    }

    public function getAMaxTime(): \DateTimeInterface
    {
        return $this->aMaxTime;
    }

    public function setAMaxTime(\DateTimeInterface $aMaxTime): self
    {
        $this->aMaxTime = $aMaxTime;

        return $this;
    }

    public function getEMinTime(): \DateTimeInterface
    {
        return $this->eMinTime;
    }

    public function setEMinTime(\DateTimeInterface $eMinTime): self
    {
        $this->eMinTime = $eMinTime;

        return $this;
    }

    public function getEMaxTime(): \DateTimeInterface
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

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getStructures()
    {
        return $this->structures->getValues();
    }

    public function addStructure(Structure $structure): self
    {
        if (!$this->structures->contains($structure)) {
            $this->structures->add($structure);
            $structure->setStructure($this);
        }

        return $this;
    }

    public function removeStructure(Structure $structure): self
    {
        if ($this->structures->contains($structure)) {
            $this->structures->removeElement($structure);
            // set the owning side to null (unless already changed)
            if ($structure->getStructure() === $this) {
                $structure->setStructure(null);
            }
        }

        return $this;
    }

    public function getSolidaryUserStructures()
    {
        return $this->solidaryUserStructures->getValues();
    }

    public function addSolidaryUsers(SolidaryUserStructure $solidaryUserStructure): self
    {
        if (!$this->solidaryUserStructures->contains($solidaryUserStructure)) {
            $this->solidaryUserStructures->add($solidaryUserStructure);
            $solidaryUserStructure->setStructure($this);
        }

        return $this;
    }

    public function removeSolidaryUser(SolidaryUserStructure $solidaryUserStructure): self
    {
        if ($this->solidaryUserStructures->contains($solidaryUserStructure)) {
            $this->solidaryUserStructures->removeElement($solidaryUserStructure);
        }

        return $this;
    }

    public function getSubjects()
    {
        // only return non private subjects
        return $this->subjects->filter(function (Subject $subject) {
            return !$subject->isPrivate();
        });
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->setStructure($this);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
            // set the owning side to null (unless already changed)
            if ($subject->getStructure() === $this) {
                $subject->setStructure(null);
            }
        }

        return $this;
    }

    public function getNeeds()
    {
        return $this->needs->getValues();
    }

    public function setNeeds(?ArrayCollection $needs): self
    {
        $this->needs = $needs;

        return $this;
    }

    public function addNeed(Need $need): self
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
        }

        return $this;
    }

    public function removeNeed(Need $need): self
    {
        if ($this->needs->contains($need)) {
            $this->needs->removeElement($need);
        }

        return $this;
    }

    public function getRelayPoints()
    {
        return $this->relayPoints->getValues();
    }

    public function addRelayPoint(RelayPoint $relayPoint): self
    {
        if (!$this->relayPoints->contains($relayPoint)) {
            $this->relayPoint[] = $relayPoint;
            $relayPoint->setStructure($this);
        }

        return $this;
    }

    public function removeRelayPoint(RelayPoint $relayPoint): self
    {
        if ($this->relayPoint->contains($relayPoint)) {
            $this->relayPoint->removeElement($relayPoint);
            // set the owning side to null (unless already changed)
            if ($relayPoint->getStructure() === $this) {
                $relayPoint->setStructure(null);
            }
        }

        return $this;
    }

    public function getStructureProofs()
    {
        return $this->structureProofs->getValues();
    }

    public function addStructureProof(StructureProof $structureProof): self
    {
        if (!$this->structureProofs->contains($structureProof)) {
            $this->structureProofs->add($structureProof);
        }

        return $this;
    }

    public function removeStructureProof(StructureProof $structureProof): self
    {
        if ($this->structureProofs->contains($structureProof)) {
            $this->structureProofs->removeElement($structureProof);
            // set the owning side to null (unless already changed)
            if ($structureProof->getStructure() === $this) {
                $structureProof->setStructure(null);
            }
        }

        return $this;
    }

    public function removeStructureProofs(): self
    {
        $this->structureProofs->clear();

        return $this;
    }

    /**
     * @return ArrayCollection|Operate[]
     */
    public function getOperates()
    {
        return $this->operates->getValues();
    }

    public function addOperate(Operate $operate): self
    {
        if (!$this->operates->contains($operate)) {
            $this->operates[] = $operate;
            $operate->setStructure($this);
        }

        return $this;
    }

    public function removeOperate(Operate $operate): self
    {
        if ($this->operates->contains($operate)) {
            $this->operates->removeElement($operate);
            // set the owning side to null (unless already changed)
            if ($operate->getStructure() === $this) {
                $operate->setStructure(null);
            }
        }

        return $this;
    }

    public function getTerritories()
    {
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories->add($territory);
        }

        return $this;
    }

    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }

        return $this;
    }

    public function getOperators()
    {
        foreach ($this->getOperates() as $operate) {
            $this->operators[] = [
                'id' => $operate->getUser()->getId(),
                'givenName' => $operate->getUser()->getGivenName(),
                'familyName' => $operate->getUser()->getFamilyName(),
                'email' => $operate->getUser()->getEmail(),
                'operatorDate' => $operate->getCreatedDate(),
            ];
        }

        return $this->operators;
    }

    public function setOperators(array $operators): self
    {
        $this->operators = $operators;

        return $this;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setStructure($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getStructure() === $this) {
                $image->setStructure(null);
            }
        }

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function isRemovable(): ?bool
    {
        return 0 == count($this->getSolidaryUserStructures());
    }

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
