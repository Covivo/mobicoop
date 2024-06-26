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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary user.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary","readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *         "get"={
 *             "security"="is_granted('user_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('user_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('user_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 *
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(SearchFilter::class, properties={"user.email":"partial", "user.givenName":"partial", "user.familyName":"partial", "user.telephone" : "exact"})
 * @ApiFilter(OrderFilter::class, properties={"id", "user.givenName", "user.familyName", "user.email", "user.telephone"}, arguments={"orderParameterName"="order"})
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUser
{
    public const DEFAULT_MAX_DISTANCE = 20000; // meters
    public const AUTHORIZED_GENERIC_FILTERS = ['q'];

    /**
     * @var int the id of this solidary user
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readSolidary","writeSolidary","readUser"})
     */
    private $id;

    /**
     * @var bool If this solidary user is a beneficiary
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary","readUser"})
     */
    private $beneficiary;

    /**
     * @var bool If this solidary user is a volunteer
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary","readUser"})
     */
    private $volunteer;

    /**
     * @var \DateTimeInterface morning min time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMinTime;

    /**
     * @var \DateTimeInterface morning max time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMaxTime;

    /**
     * @var \DateTimeInterface afternoon min time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMinTime;

    /**
     * @var \DateTimeInterface afternoon max time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMaxTime;

    /**
     * @var \DateTimeInterface evening min time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMinTime;

    /**
     * @var \DateTimeInterface evening max time
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMaxTime;

    /**
     * @var bool available on monday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mMon;

    /**
     * @var bool available on monday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aMon;

    /**
     * @var bool available on monday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eMon;

    /**
     * @var bool available on tuesday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mTue;

    /**
     * @var bool available on tuesday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aTue;

    /**
     * @var bool available on tuesday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eTue;

    /**
     * @var bool available on wednesday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mWed;

    /**
     * @var bool available on wednesday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aWed;

    /**
     * @var bool available on wednesday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eWed;

    /**
     * @var bool available on thursday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mThu;

    /**
     * @var bool available on thursday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aThu;

    /**
     * @var bool available on thursday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eThu;

    /**
     * @var bool available on friday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mFri;

    /**
     * @var bool available on friday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aFri;

    /**
     * @var bool available on friday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eFri;

    /**
     * @var bool available on saturday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSat;

    /**
     * @var bool available on saturday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSat;

    /**
     * @var bool available on saturday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSat;

    /**
     * @var bool available on sunday morning
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $mSun;

    /**
     * @var bool available on sunday afternoon
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $aSun;

    /**
     * @var bool available on sunday evening
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $eSun;

    /**
     * @var Address the center address of the accepted perimeter
     *
     * @Assert\NotBlank
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $address;

    /**
     * @var null|int the maximum distance in metres allowed from the center address
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $maxDistance;

    /**
     * @var bool the solidaryUser has a vehicle
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $vehicle;

    /**
     * @var User the user associated with the solidaryUser
     *
     * @Assert\NotBlank
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", cascade={"persist"}, mappedBy="solidaryUser")
     *
     * @Groups({"readSolidary","writeSolidary"})
     *
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var string a comment about the solidaryUser
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var null|ArrayCollection the special needs proposed by the solidaryUser
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need", inversedBy="volunteers")
     *
     * @Groups({"readUser","readSolidary","writeSolidary"})
     *
     * @MaxDepth(1)
     */
    private $needs;

    /**
     * @var ArrayCollection The solidary user structures
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryUserStructure", mappedBy="solidaryUser", cascade={"persist"})
     *
     * @Groups({"readUser","readSolidary","writeSolidary"})
     *
     * @MaxDepth(1)
     */
    private $solidaryUserStructures;

    /**
     * @var null|ArrayCollection solidary matchings
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="solidaryUser")
     *
     * @Groups({"readSolidary","writeSolidary"})
     *
     * @MaxDepth(1)
     */
    private $solidaryMatchings;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readSolidary"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readSolidary"})
     */
    private $updatedDate;

    /**
     * @var null|string the first name of the user
     *
     * @Groups({"aReadCol"})
     */
    private $givenName;

    public function __construct()
    {
        $this->needs = new ArrayCollection();
        $this->solidaryUserStructures = new ArrayCollection();
        $this->solidaryMatchings = new ArrayCollection();
        $this->setMaxDistance(self::DEFAULT_MAX_DISTANCE);
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

    public function isBeneficiary(): ?bool
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(bool $beneficiary): self
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    public function isVolunteer(): ?bool
    {
        return $this->volunteer;
    }

    public function setVolunteer(bool $volunteer): self
    {
        $this->volunteer = $volunteer;

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

    public function setVehicle(?bool $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        $user->setSolidaryUser($this);

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

    public function getNeeds()
    {
        return $this->needs->getValues();
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

    public function getSolidaryUserStructures()
    {
        return $this->solidaryUserStructures->getValues();
    }

    public function addSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure): self
    {
        if (!$this->solidaryUserStructures->contains($solidaryUserStructure)) {
            $this->solidaryUserStructures->add($solidaryUserStructure);
            $solidaryUserStructure->setSolidaryUser($this);
        }

        return $this;
    }

    public function removeSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure): self
    {
        if ($this->solidaryUserStructures->contains($solidaryUserStructure)) {
            $this->solidaryUserStructures->removeElement($solidaryUserStructure);
            // set the owning side to null (unless already changed)
            if ($solidaryUserStructure->getSolidaryUser() === $this) {
                $solidaryUserStructure->setSolidaryUser(null);
            }
        }

        return $this;
    }

    public function getSolidaryMatchings()
    {
        return $this->solidaryMatchings->getValues();
    }

    public function addSolidaryMatching(SolidaryMatching $solidaryMatching): self
    {
        if (!$this->solidaryMatchings->contains($solidaryMatching)) {
            $this->solidaryMatchings[] = $solidaryMatching;
        }

        return $this;
    }

    public function removeSolidaryMatching(SolidarySolution $solidaryMatching): self
    {
        if ($this->solidaryMatchings->contains($solidaryMatching)) {
            $this->solidaryMatchings->removeElement($solidaryMatching);
        }

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
