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

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Resource;

/**
 * A user.
 */
class User implements Resource
{
    
    /**
     * @var int The id of this user.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     * 
     * @Groups({"post","put"})
     */
    private $iri;
    
    /**
     * @var string|null The first name of the user.
     *
     * @Groups({"post","put"})
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @Groups({"post","put"})
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\NotBlank
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string|null The encoded password of the user.
     *
     * @Groups({"post","put"})
     */
    private $password;
    
    /**
     * @var string|null The gender of the user.
     *
     * @Groups({"post","put"})
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @Groups({"post","put"})
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\Date()
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @Groups({"post","put"})
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationDistance;
    
    /**
     * @var UserAddress[]|null A user may have many names addresses.
     */
    private $userAddresses;
    
    /**
     * @var Proposal[]|null The proposals made by this user.
     */
    private $proposals;
    
    /**
     * @var Solicitation[]|null The solicitations made by this user.
     */
    private $solicitations;
    
    /**
     * @var Solicitation[]|null The solicitations where the user is involved as a driver.
     */
    private $solicitationsOffer;
    
    /**
     * @var Solicitation[]|null The solicitations where the user is involved as a passenger.
     */
    private $solicitationsRequest;
    
    public function __construct($id=null)
    {
        if ($id) $this->setIri("/users/".$id);
        $this->userAddresses = new ArrayCollection();
    }
        
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }
    
    public function getEmail(): ?string
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
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function setGivenName(?string $givenName)
    {
        $this->givenName = $givenName;
    }

    public function setFamilyName(?string $familyName)
    {
        $this->familyName = $familyName;
    }
    
    public function setEmail(?string $email)
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
     * @return Collection|Solicitation[]
     */
    public function getSolicitations(): Collection
    {
        return $this->solicitations;
    }
    
    public function addSolicitation(Solicitation $solicitation): self
    {
        if (!$this->solicitations->contains($solicitation)) {
            $this->solicitations[] = $solicitation;
            $solicitation->setUser($this);
        }
        
        return $this;
    }
    
    public function removeSolicitation(Solicitation $solicitation): self
    {
        if ($this->solicitations->contains($solicitation)) {
            $this->solicitations->removeElement($solicitation);
            // set the owning side to null (unless already changed)
            if ($solicitation->getUser() === $this) {
                $solicitation->setUser(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsOffer(): Collection
    {
        return $this->solicitationsOffer;
    }
    
    public function addSolicitationsOffer(Solicitation $solicitationsOffer): self
    {
        if (!$this->solicitationsOffer->contains($solicitationsOffer)) {
            $this->solicitationsOffer[] = $solicitationsOffer;
            $solicitationsOffer->setUserOffer($this);
        }
        
        return $this;
    }
    
    public function removeSolicitationsOffer(Solicitation $solicitationsOffer): self
    {
        if ($this->solicitationsOffer->contains($solicitationsOffer)) {
            $this->solicitationsOffer->removeElement($solicitationsOffer);
            // set the owning side to null (unless already changed)
            if ($solicitationsOffer->getUserOffer() === $this) {
                $solicitationsOffer->setUserOffer(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsRequest(): Collection
    {
        return $this->solicitationsRequest;
    }
    
    public function addSolicitationsRequest(Solicitation $solicitationsRequest): self
    {
        if (!$this->solicitationsRequest->contains($solicitationsRequest)) {
            $this->solicitationsRequest[] = $solicitationsRequest;
            $solicitationsRequest->setUserRequest($this);
        }
        
        return $this;
    }
    
    public function removeSolicitationsRequest(Solicitation $solicitationsRequest): self
    {
        if ($this->solicitationsRequest->contains($solicitationsRequest)) {
            $this->solicitationsRequest->removeElement($solicitationsRequest);
            // set the owning side to null (unless already changed)
            if ($solicitationsRequest->getUserRequest() === $this) {
                $solicitationsRequest->setUserRequest(null);
            }
        }
        
        return $this;
    }
}
