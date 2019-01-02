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

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Geography\Entity\Address;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Ask;

/**
 * A user.
 *
 * Users should not be fully removed, if a user wants to remove its account it should be anonymized, unless he has no interactions with other users.
 *
 * @ORM\Entity
 * @UniqueEntity("email")
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(SearchFilter::class, properties={"email":"exact"})
 * @ApiFilter(OrderFilter::class, properties={"id", "givenName", "familyName", "email", "gender", "nationality", "birthDate"}, arguments={"orderParameterName"="order"})
 */
class User
{
    const MAX_DEVIATION_TIME = 600;
    const MAX_DEVIATION_DISTANCE = 10000;
    
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ANONYMIZED = 3;
    
    /**
     * @var int The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var int User status (1 = active; 2 = disabled; 3 = anonymized).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;
    
    /**
     * @var string|null The first name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @ORM\Column(type="string", length=100, unique=true)
     * @Groups({"read","write"})
     */
    private $email;
    
    /**
     * @var string The encoded password of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("write")
     */
    private $password;
    
    /**
     * @var string|null The gender of the user.
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"read","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={
     *             "type"="string",
     *             "enum"={"female", "male"}
     *         }
     *     }
     * )
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @Assert\Date()
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDeviationDistance;
    
    /**
     * @var boolean|null The user accepts any route as a passenger from its origin to the destination.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $anyRouteAsPassenger;
    
    /**
     * @var boolean|null The user accepts any transportation mode.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $multiTransportMode;
    
    /**
     * @var Address[]|null A user may have many addresses.
     *
     * @ORM\OneToMany(targetEntity="Address::class", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $addresses;
    
    /**
     * @var Car[]|null A user may have many cars.
     *
     * @ORM\OneToMany(targetEntity="Car::class", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $cars;

    /**
     * @var Proposal[]|null The proposals made by this user.
     *
     * @ORM\OneToMany(targetEntity="Proposal::class", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $proposals;

    /**
     * @var Ask[]|null The asks made by this user.
     *
     * @ORM\OneToMany(targetEntity="Ask::class", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $asks;
    
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getStatus(): ?int
    {
        return $this->status;
    }
    
    public function setStatus(int $status): self
    {
        $this->status = $status;
        
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
    
    public function getGender(): ?string
    {
        return $this->gender;
    }
    
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }
    
    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;
        
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        
        return $this;
    }

    public function getMaxDeviationTime(): int
    {
        return (!is_null($this->maxDeviationTime) ? $this->maxDeviationTime : self::MAX_DEVIATION_TIME);
    }
    
    public function setMaxDeviationTime(?int $maxDeviationTime): self
    {
        $this->maxDeviationTime = $maxDeviationTime;
        
        return $this;
    }

    public function getMaxDeviationDistance(): int
    {
        return (!is_null($this->maxDeviationDistance) ? $this->maxDeviationDistance : self::MAX_DEVIATION_DISTANCE);
    }
    
    public function setMaxDeviationDistance(?int $maxDeviationDistance): self
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
        
        return $this;
    }
    
    public function getAnyRouteAsPassenger(): bool
    {
        return (!is_null($this->anyRouteAsPassenger) ? $this->anyRouteAsPassenger : true);
    }
    
    public function setAnyRouteAsPassenger(?bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;
        
        return $this;
    }
    
    public function getAddresses(): ArrayCollection
    {
        return $this->addresses;
    }
    
    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }
        
        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }
        
        return $this;
    }
    
    public function getCars(): ArrayCollection
    {
        return $this->cars;
    }
    
    public function addCar(Car $car): self
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }
        
        return $this;
    }
    
    public function removeCar(Car $car): self
    {
        if ($this->cars->contains($car)) {
            $this->cars->removeElement($car);
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }
        
        return $this;
    }
    
    public function getProposals(): ArrayCollection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    public function getAsks(): ArrayCollection
    {
        return $this->asks;
    }

    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks->add($ask);
            $ask->setUser($this);
        }

        return $this;
    }

    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getUser() === $this) {
                $ask->setUser(null);
            }
        }

        return $this;
    }
}
