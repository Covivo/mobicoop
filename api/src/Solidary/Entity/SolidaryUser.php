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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary user.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readSolidaryUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryUser"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class SolidaryUser
{
    /**
     * @var int The id of this solidary user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("readSolidaryUser")
     */
    private $id;

    /**
     * @var bool If this solidary user is a beneficiary
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $beneficiary;

    /**
     * @var bool If this solidary user is a volunteer
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $volunteer;

    /**
     * @var \DateTimeInterface Morning min time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mMinTime;
    
    /**
     * @var \DateTimeInterface Morning max time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mMaxTime;
    
    /**
     * @var \DateTimeInterface Afternoon min time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aMinTime;
    
    /**
     * @var \DateTimeInterface Afternoon max time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aMaxTime;
    
    /**
     * @var \DateTimeInterface Evening min time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eMinTime;
    
    /**
     * @var \DateTimeInterface Evening max time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eMaxTime;
    
    /**
     * @var bool Available on monday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mMon;
    
    /**
     * @var bool Available on monday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aMon;
    
    /**
     * @var bool Available on monday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eMon;
    
    /**
     * @var bool Available on tuesday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mTue;
    
    /**
     * @var bool Available on tuesday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aTue;
    
    /**
     * @var bool Available on tuesday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eTue;
    
    /**
     * @var bool Available on wednesday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mWed;
    
    /**
     * @var bool Available on wednesday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aWed;
    
    /**
     * @var bool Available on wednesday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eWed;
    
    /**
     * @var bool Available on thursday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mThu;
    
    /**
     * @var bool Available on thursday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aThu;
    
    /**
     * @var bool Available on thursday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eThu;
    
    /**
     * @var bool Available on friday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mFri;
    
    /**
     * @var bool Available on friday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aFri;
    
    /**
     * @var bool Available on friday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eFri;
    
    /**
     * @var bool Available on saturday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mSat;
    
    /**
     * @var bool Available on saturday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aSat;
    
    /**
     * @var bool Available on saturday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eSat;
    
    /**
     * @var bool Available on sunday morning.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $mSun;
    
    /**
     * @var bool Available on sunday afternoon.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $aSun;
    
    /**
     * @var bool Available on sunday evening.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $eSun;

    /**
     * @var Address The center address of the accepted perimeter.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $address;

    /**
     * @var int|null The maximum distance in metres allowed from the center address.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $maxDistance;

    /**
     * @var bool The solidaryUser has a vehicle.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $vehicle;

    /**
     * @var User The user associated with the solidaryUser.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     * @ApiSubresource
     */
    private $user;

    /**
     * @var string A comment about the solidaryUser.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $comment;

    /**
     * @var ArrayCollection|null The special needs proposed by the solidaryUser.
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need")
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     */
    private $needs;

    /**
     * @var ArrayCollection|null solidaryUser proofs.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Proof", mappedBy="solidary_user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readSolidaryUser","writeSolidaryUser"})
     * @ApiSubresource
     * @MaxDepth(1)
     */
    private $proofs;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUser"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->needs = new ArrayCollection();
        $this->proofs = new ArrayCollection();
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

    public function setVehicle(bool $vehicle): self
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

    public function getProofs()
    {
        return $this->proofs->getValues();
    }
    
    public function addProof(Proof $proof): self
    {
        if (!$this->proofs->contains($proof)) {
            $this->proofs[] = $proof;
            $proof->setSolidaryUser($this);
        }
        
        return $this;
    }
    
    public function removeProof(Proof $proof): self
    {
        if ($this->proofs->contains($proof)) {
            $this->proofs->removeElement($proof);
            // set the owning side to null (unless already changed)
            if ($proof->getSolidaryUser() === $this) {
                $proof->setSolidaryUser(null);
            }
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
