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

namespace App\Solidary\Entity\Exposed;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Geography\Entity\Address;
use App\Solidary\Entity\Structure;

/**
 * A solidary volunteer.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readVolunteer"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeVolunteer"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Volunteer
{
    
    /**
     * @var int The id of this volunteer.
     *
     * @ApiProperty(identifier=true)
     * @Groups("readVolunteer")
     */
    private $id;


    /**
     * @var string The given name of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $givenName;

    /**
     * @var string The family name of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $familyName;

    /**
     * @var string The email of this volunteer
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $email;

    /**
     * @var string The password of this volunteer
     * @Groups({"writeVolunteer"})
     */
    private $password;

    /**
     * @var int|null The gender of this volunteer (1=female, 2=male, 3=nc)
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $gender;

    /**
     * @var \DateTimeInterface|null The birth date of this volunteer.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $birthDate;

    /**
     * @var int phone display configuration (1 = restricted (default); 2 = all).
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $phoneDisplay;


    /**
     * @var Address The center address of the accepted perimeter.
     *
     * @Assert\NotBlank
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $address;

    /**
     * @var int|null The maximum distance in metres allowed from the center address.
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $maxDistance;

    /**
     * @var bool The volunteer has a vehicle.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $vehicle;

    /**
     * @var Structure Structure of the volunteer.
     *
     * @Assert\NotBlank
     * @Groups({"readVolunteer","writeVolunteer"})
     * @MaxDepth(1)
     */
    private $structure;

    /**
     * @var string A comment about the volunteer.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $comment;

    /**
     * @var array|null The special needs proposed by the volunteer.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     */
    private $needs;

    /**
     * @var array|null Volunteer proofs.
     *
     * @Groups({"readVolunteer","writeVolunteer"})
     * @ApiSubresource
     * @MaxDepth(1)
     */
    private $proofs;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readVolunteer"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @Groups({"readVolunteer"})
     */
    private $updatedDate;

    public function __construct()
    {
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

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

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

    public function getPhoneDisplay(): ?int
    {
        return $this->phoneDisplay;
    }

    public function setPhoneDisplay(?int $phoneDisplay): self
    {
        $this->phoneDisplay = $phoneDisplay;

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

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

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

    public function getNeeds(): ?array
    {
        return $this->needs;
    }

    public function setNeeds(?array $needs): self
    {
        $this->needs = $needs;

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
}
