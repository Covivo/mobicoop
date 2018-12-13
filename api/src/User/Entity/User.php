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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Ask;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\PersistentCollection;

/**
 * A user.
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
    /**
     * @var int $id The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
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
     * @var \DateTimeInterface|null $birthDate The birth date of the user.
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
     * @var UserAddress[]|null A user may have many names addresses.
     *
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="user", cascade={"persist","remove"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     *
     */
    private $userAddresses;

    /**
     * @var Proposal[]|null The proposals made by this user.
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Proposal", mappedBy="user")
     * @ApiSubresource(maxDepth=1)
     */
    private $proposals;

    /**
     * @var Ask[]|null The asks made by this user.
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Ask", mappedBy="user")
     * @ApiSubresource(maxDepth=1)
     */
    private $asks;

    /**
     * @var Ask[]|null The asks where the user is involved as a driver.
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Ask", mappedBy="userOffer")
     * @ApiSubresource(maxDepth=1)
     */
    private $asksOffer;

    /**
     * @var Ask[]|null The asks where the user is involved as a passenger.
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Ask", mappedBy="userRequest")
     * @ApiSubresource(maxDepth=1)
     */
    private $asksRequest;
    
    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
        $this->asksOffer = new ArrayCollection();
        $this->asksRequest = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getMaxDeviationTime(): ?int
    {
        return $this->maxDeviationTime;
    }

    public function getMaxDeviationDistance(): ?int
    {
        return $this->maxDeviationDistance;
    }

    public function getUserAddresses()
    {
        return $this->userAddresses;
    }

    public function setGivenName(?string $givenName)
    {
        $this->givenName = $givenName;
    }

    public function setFamilyName(?string $familyName)
    {
        $this->familyName = $familyName;
    }
    
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

    public function setGender(?string $gender)
    {
        $this->gender = $gender;
    }

    public function setNationality(?string $nationality)
    {
        $this->nationality = $nationality;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setTelephone(?string $telephone)
    {
        $this->telephone = $telephone;
    }

    public function setMaxDeviationTime(?int $maxDeviationTime)
    {
        $this->maxDeviationTime = $maxDeviationTime;
    }

    public function setMaxDeviationDistance(?int $maxDeviationDistance)
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
    }

    public function setUserAddresses(?array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
    public function addUserAddress(UserAddress $userAddress)
    {
        $userAddress->setUser($this);
        $this->userAddresses->add($userAddress);
    }
    
    public function removeUserAddress(UserAddress $userAddress)
    {
        $this->userAddresses->removeElement($userAddress);
        $userAddress->setUser(null);
    }

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
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

    /**
     * @return Collection|Ask[]
     */
    public function getAsks(): Collection
    {
        return $this->asks;
    }

    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks[] = $ask;
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

    /**
     * @return Collection|Ask[]
     */
    public function getAsksOffer(): Collection
    {
        return $this->asksOffer;
    }

    public function addAsksOffer(Ask $asksOffer): self
    {
        if (!$this->asksOffer->contains($asksOffer)) {
            $this->asksOffer[] = $asksOffer;
            $asksOffer->setUserOffer($this);
        }

        return $this;
    }

    public function removeAsksOffer(Ask $asksOffer): self
    {
        if ($this->asksOffer->contains($asksOffer)) {
            $this->asksOffer->removeElement($asksOffer);
            // set the owning side to null (unless already changed)
            if ($asksOffer->getUserOffer() === $this) {
                $asksOffer->setUserOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ask[]
     */
    public function getAsksRequest(): Collection
    {
        return $this->asksRequest;
    }

    public function addAsksRequest(Ask $asksRequest): self
    {
        if (!$this->asksRequest->contains($asksRequest)) {
            $this->asksRequest[] = $asksRequest;
            $asksRequest->setUserRequest($this);
        }

        return $this;
    }

    public function removeAsksRequest(Ask $asksRequest): self
    {
        if ($this->asksRequest->contains($asksRequest)) {
            $this->asksRequest->removeElement($asksRequest);
            // set the owning side to null (unless already changed)
            if ($asksRequest->getUserRequest() === $this) {
                $asksRequest->setUserRequest(null);
            }
        }

        return $this;
    }
}
