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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Relation between a Solidary User, a Structure and a Proof
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readSolidaryUserStructure"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryUserStructure"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class SolidaryUserStructure
{
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 1;

    /**
     * @var int The id of this SolidaryStructureProof.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     */
    private $id;
    
    /**
     * @var SolidaryUser Solidary User linked to this structure
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\SolidaryUser", inversedBy="solidaryUserStructures", cascade={"persist","remove"})
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     * @MaxDepth(1)
     */
    private $solidaryUser;
    
    /**
     * @var Structure Structure.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="solidaryUserStructures", cascade={"persist","remove"})
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure","readSolidaryUser","writeSolidaryUser"})
     * @MaxDepth(1)
     */
    private $structure;

    /**
     * @var ArrayCollection The ask history items linked with the ask.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Proof", mappedBy="solidaryUserStructure", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $proofs;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     */
    private $createdDate;

    /**
     * @var int Status of this Solidary User for this structure (0 : pending, 1 : accepted, 2 : refused)
     *
     * @ORM\Column(type="integer", options={"default" : 0})
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Acceptation date of this Solidary User for this structure
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     */
    private $acceptedDate;

    /**
     * @var \DateTimeInterface Refusal date of this Solidary User for this structure.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidaryUserStructure","writeSolidaryUserStructure"})
     */
    private $refusedDate;

    public function __construct($id=null)
    {
        $this->id = $id;
        $this->proofs = new ArrayCollection();
        $this->status = 0;
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
    
    public function getSolidaryUser(): ?SolidaryUser
    {
        return $this->solidaryUser;
    }
    
    public function setSolidaryUser(SolidaryUser $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;
        
        return $this;
    }
    
    public function getStructure(): ?Structure
    {
        return $this->structure;
    }
    
    public function setStructure(Structure $structure): self
    {
        $this->structure = $structure;
        
        return $this;
    }

    public function getProofs()
    {
        return $this->proofs->getValues();
    }

    public function addProof(Proof $proof): self
    {
        if (!$this->proofs->contains($proof)) {
            $this->proofs->add($proof);
            $proof->setSolidaryUserStructure($this);
        }

        return $this;
    }

    public function removeProof(Proof $proof): self
    {
        if ($this->proofs->contains($proof)) {
            $this->proofs->removeElement($proof);
            // set the owning side to null (unless already changed)
            if ($proof->getSolidaryUser() === $this) {
                $proof->setSolidaryUserStructure(null);
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

    public function getStatus(): ?int
    {
        return $this->status;
    }
    
    public function setStatus(int $status): self
    {
        $this->status = $status;
        
        return $this;
    }

    public function getAcceptedDate(): ?\DateTimeInterface
    {
        return $this->acceptedDate;
    }

    public function setAcceptedDate(\DateTimeInterface $acceptedDate): self
    {
        $this->acceptedDate = $acceptedDate;

        return $this;
    }

    public function getRefusedDate(): ?\DateTimeInterface
    {
        return $this->refusedDate;
    }

    public function setRefusedDate(\DateTimeInterface $refusedDate): self
    {
        $this->refusedDate = $refusedDate;

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
}
