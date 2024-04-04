<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Carpool\Entity\Proposal;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A mass matching person, imported from a mass matching file.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"mass"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      }
 * )
 */
class MassPerson
{
    /**
     * @var int the id of this person
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"mass","massCompute"})
     */
    private $id;

    /**
     * @var null|string the given id of the person
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"mass"})
     *
     * @Groups({"mass","massCompute"})
     */
    private $givenId;

    /**
     * @var null|string the first name of the person
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $givenName;

    /**
     * @var null|string the family name of the person
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $familyName;

    /**
     * @var null|\DateTimeInterface the birth date of the person
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @var null|int The gender of the person (1=female, 2=male, 3=nc)
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $gender;

    /**
     * @var null|string the email address of the person
     *
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $email;

    /**
     * @var null|string the telephone number of the person
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @var Address the personal address of the person
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(groups={"mass"})
     *
     * @Assert\Valid
     *
     * @Groups({"mass","massCompute"})
     */
    private $personalAddress;

    /**
     * @var Address the work address of the person
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank(groups={"mass"})
     *
     * @Assert\Valid
     *
     * @Groups({"mass","massCompute"})
     */
    private $workAddress;

    /**
     * @var int the total distance of the direction in meter
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $distance;

    /**
     * @var int the total duration of the direction in milliseconds
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $duration;

    /**
     * @var float the minimum longitude of the bounding box of the direction
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $bboxMinLon;

    /**
     * @var float the minimum latitude of the bounding box of the direction
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $bboxMinLat;

    /**
     * @var float the maximum longitude of the bounding box of the direction
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $bboxMaxLon;

    /**
     * @var float the maximum latitude of the bounding box of the direction
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $bboxMaxLat;

    /**
     * @var null|int the initial bearing of the direction in degrees
     *
     * @ORM\Column(type="integer",nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $bearing;

    /**
     * @var Mass the original mass file of the person
     *
     * @Assert\NotBlank
     *
     * @ORM\ManyToOne(targetEntity="\App\Match\Entity\Mass", cascade={"persist"}, inversedBy="persons")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @MaxDepth(1)
     */
    private $mass;

    /**
     * @var null|ArrayCollection the potential matchings if the person is driver
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassMatching", mappedBy="massPerson1", cascade={"persist"})
     *
     * @MaxDepth(1)
     *
     * @Groups({"mass","massCompute"})
     */
    private $matchingsAsDriver;

    /**
     * @var null|ArrayCollection the potential matchings if the person is passenger
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassMatching", mappedBy="massPerson2", cascade={"persist"})
     *
     * @MaxDepth(1)
     *
     * @Groups({"mass","massCompute"})
     */
    private $matchingsAsPassenger;

    /**
     * @var null|\DateTimeInterface the outward time
     *
     * @Assert\Time()
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $outwardTime;

    /**
     * @var null|\DateTimeInterface the return time
     *
     * @Assert\Time()
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $returnTime;

    /**
     * @var bool the person accepts to be a driver
     *
     * @Assert\Type("bool")
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $driver;

    /**
     * @var bool the person accepts to be a passenger
     *
     * @Assert\Type("bool")
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"mass","massCompute"})
     */
    private $passenger;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"read"})
     */
    private $updatedDate;

    /**
     * @var null|User The User created based on this MassPerson
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", inversedBy="massPerson")
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @MaxDepth(1)
     *
     * @Groups({"read"})
     */
    private $user;

    /**
     * @var null|Proposal The Proposal created based on this MassPerson journey (only the outward for round trip)
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Proposal")
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @MaxDepth(1)
     *
     * @Groups({"read"})
     */
    private $proposal;

    /**
     * @var null|ArrayCollection The MassPTJourneys linked to this mass person
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassPTJourney", mappedBy="massPerson", cascade={"persist"})
     *
     * @MaxDepth(1)
     *
     * @Groups({"pt"})
     */
    private $massPTJourneys;

    /**
     * @var string The clear password of the user when migrated (not persisted)
     *
     * @Groups({"write"})
     */
    private $clearPassword;

    public function __construct()
    {
        $this->matchingsAsDriver = new ArrayCollection();
        $this->matchingsAsPassenger = new ArrayCollection();
        $this->massPTJourneys = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGivenId(): string
    {
        return $this->givenId;
    }

    public function setGivenId(string $givenId): self
    {
        $this->givenId = $givenId;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;
        if ('' == $this->givenName) {
            $this->givenName = null;
        }

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;
        if ('' == $this->familyName) {
            $this->familyName = null;
        }

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

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

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

    public function getPersonalAddress(): Address
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(Address $address): self
    {
        $this->personalAddress = $address;

        return $this;
    }

    public function getWorkAddress(): Address
    {
        return $this->workAddress;
    }

    public function setWorkAddress(Address $address): self
    {
        $this->workAddress = $address;

        return $this;
    }

    public function getMass(): Mass
    {
        return $this->mass;
    }

    public function setMass(?Mass $mass): self
    {
        $this->mass = $mass;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getBboxMinLon(): ?float
    {
        return $this->bboxMinLon;
    }

    public function setBboxMinLon(?float $bboxMinLon): self
    {
        $this->bboxMinLon = $bboxMinLon;

        return $this;
    }

    public function getBboxMinLat(): ?float
    {
        return $this->bboxMinLat;
    }

    public function setBboxMinLat(?float $bboxMinLat)
    {
        $this->bboxMinLat = $bboxMinLat;

        return $this;
    }

    public function getBboxMaxLon(): ?float
    {
        return $this->bboxMaxLon;
    }

    public function setBboxMaxLon(?float $bboxMaxLon): self
    {
        $this->bboxMaxLon = $bboxMaxLon;

        return $this;
    }

    public function getBboxMaxLat(): ?float
    {
        return $this->bboxMaxLat;
    }

    public function setBboxMaxLat(?float $bboxMaxLat): self
    {
        $this->bboxMaxLat = $bboxMaxLat;

        return $this;
    }

    public function getBearing(): ?int
    {
        return $this->bearing;
    }

    public function setBearing(?int $bearing): self
    {
        $this->bearing = $bearing;

        return $this;
    }

    public function getMatchingsAsDriver()
    {
        return $this->matchingsAsDriver->getValues();
    }

    public function getMatchingsAsPassenger()
    {
        return $this->matchingsAsPassenger->getValues();
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?string $outwardTime): self
    {
        if ($outwardTime) {
            $this->outwardTime = \DateTime::createFromFormat('H:i:s', $outwardTime);
        }

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?string $returnTime): self
    {
        if ($returnTime) {
            $this->returnTime = \DateTime::createFromFormat('H:i:s', $returnTime);
        }

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->driver;
    }

    public function setDriver(bool $isDriver): self
    {
        $this->driver = $isDriver;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }

    public function setPassenger(bool $isPassenger): self
    {
        $this->passenger = $isPassenger;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function getMassPTJourneys()
    {
        return $this->massPTJourneys->getValues();
    }

    public function addMassPTJourney(MassPTJourney $massPTJourney): self
    {
        if (!$this->massPTJourneys->contains($massPTJourney)) {
            $this->massPTJourneys->add($massPTJourney);
        }

        return $this;
    }

    public function removeMassPTJourneys(MassPTJourney $massPTJourney): self
    {
        if ($this->massPTJourneys->contains($massPTJourney)) {
            $this->massPTJourneys->removeElement($massPTJourney);
        }

        return $this;
    }

    public function getClearPassword(): ?string
    {
        return $this->clearPassword;
    }

    public function setClearPassword(?string $clearPassword): self
    {
        $this->clearPassword = $clearPassword;

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
