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
 **************************/

namespace App\Solidary\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Geography\Entity\Territory;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

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
 *             "security"="is_granted('structure_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('structure_create',object)"
 *          },
 *          "structure_geolocation"={
 *              "method"="GET",
 *              "path"="/structures/geolocation",
 *              "normalization_context"={"groups"={"readSolidary"}},
 *              "security"="is_granted('structure_list',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('structure_read',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('structure_update',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('structure_delete',object)"
 *          }
 *      }
 * )
 * ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 * ApiFilter(SearchFilter::class, properties={"name":"partial"})
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Structure
{
    
    /**
     * @var int The id of this structure.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","readSolidary","writeSolidary","readUser"})
     */
    private $id;

    /**
     * @var string Name of the structure.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","readSolidary","writeSolidary","readUser"})
     */
    private $name;

    /**
     * @var string The email of the structure.
     *
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","writeSolidary","readUser"})
     */
    private $email;

    /**
     * @var string|null The telephone number of the structure.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","writeSolidary","readUser"})
     */
    private $telephone;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readStructure"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readStructure"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface Morning min range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMinRangeTime;

    /**
     * @var \DateTimeInterface Morning max range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMaxRangeTime;

    /**
     * @var \DateTimeInterface Afternoon min range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMinRangeTime;

    /**
     * @var \DateTimeInterface Afternoon max range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMaxRangeTime;

    /**
     * @var \DateTimeInterface Evening min range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMinRangeTime;

    /**
     * @var \DateTimeInterface Evening max range time.
     *
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMaxRangeTime;

    /**
     * @var \DateTimeInterface Morning min time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMinTime;
    
    /**
     * @var \DateTimeInterface Morning max time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMaxTime;
    
    /**
     * @var \DateTimeInterface Afternoon min time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMinTime;
    
    /**
     * @var \DateTimeInterface Afternoon max time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMaxTime;
    
    /**
     * @var \DateTimeInterface Evening min time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMinTime;
    
    /**
     * @var \DateTimeInterface Evening max time.
     *
     * @ORM\Column(type="time")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMaxTime;
    
    /**
     * @var bool Available on monday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMon;
    
    /**
     * @var bool Available on monday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMon;
    
    /**
     * @var bool Available on monday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMon;
    
    /**
     * @var bool Available on tuesday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mTue;
    
    /**
     * @var bool Available on tuesday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aTue;
    
    /**
     * @var bool Available on tuesday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eTue;
    
    /**
     * @var bool Available on wednesday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mWed;
    
    /**
     * @var bool Available on wednesday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aWed;
    
    /**
     * @var bool Available on wednesday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eWed;
    
    /**
     * @var bool Available on thursday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mThu;
    
    /**
     * @var bool Available on thursday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aThu;
    
    /**
     * @var bool Available on thursday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eThu;
    
    /**
     * @var bool Available on friday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mFri;
    
    /**
     * @var bool Available on friday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aFri;
    
    /**
     * @var bool Available on friday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eFri;
    
    /**
     * @var bool Available on saturday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSat;
    
    /**
     * @var bool Available on saturday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSat;
    
    /**
     * @var bool Available on saturday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSat;
    
    /**
     * @var bool Available on sunday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSun;
    
    /**
     * @var bool Available on sunday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSun;
    
    /**
     * @var bool Available on sunday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSun;

    /**
     * @var Structure Parent structure.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="structures")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $structure;

    /**
     * @var ArrayCollection|null Child structures.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Structure", mappedBy="structure", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $structures;

    /**
     * @var ArrayCollection|null The solidary user for this structure.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryUserStructure", mappedBy="structure", cascade={"remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     */
    private $solidaryUserStructures;

    /**
     * @var ArrayCollection|null The subjects for this structure.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Subject", mappedBy="structure", cascade={"persist"}, orphanRemoval=true)
     * @Groups({"readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     */
    private $subjects;

    /**
     * @var ArrayCollection|null The special needs for this structure.
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need", inversedBy="structures", cascade={"persist"})
     * @Groups({"readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     */
    private $needs;

    /**
     * @var ArrayCollection|null The relay points related to the structure.
     *
     * @ORM\OneToMany(targetEntity="\App\RelayPoint\Entity\RelayPoint", mappedBy="structure", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     */
    private $relayPoints;

    /**
     * @var ArrayCollection|null The solidary records for this structure.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\StructureProof", mappedBy="structure", cascade={"persist"}, orphanRemoval=true)
     * @Groups({"readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $structureProofs;

    /**
     * @var ArrayCollection|null A Structure can have multiple entry in Operate
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Operate", mappedBy="structure")
     * @MaxDepth(1)
     */
    private $operates;

    /**
     * @var ArrayCollection|null The Territories linked to this Structure
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory", inversedBy="structures")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $territories;

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
        return $this->subjects->getValues();
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
        if ($this->needs->contains($structureProof)) {
            $this->needs->removeElement($structureProof);
        }

        return $this;
    }

    /**
    * @return ArrayCollection|Operate[]
    */
    public function getOperates(): ArrayCollection
    {
        return $this->operates;
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
        return $this->territories;
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

    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
