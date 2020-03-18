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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * A solidary proof related to a solidary record or a solidaryUser
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readSolidary","readSolidaryUser}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "label"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"label":"partial"})
 * @Vich\Uploadable
 */
class Proof
{
    
    /**
     * @var int The id of this proof.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidary","readSolidaryUser"})
     */
    private $id;

    /**
     * @var string The value entered by the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     */
    private $value;

    /**
     * @var StructureProof Structure proof.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\StructureProof", inversedBy="proofs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     * @MaxDepth(1)
     */
    private $structureProof;

    /**
     * @var Solidary Solidary record if the proof concerns a solidary requester.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\StructureProof", inversedBy="proofs")
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
     * @var SolidaryUser if the proof concerns a solidary User.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\SolidaryUser", inversedBy="proofs")
     * @Groups({"readSolidary","writeSolidaryUser"})
     * @MaxDepth(1)
     */
    private $solidaryUser;

    /**
     * @var string The final file name of the proof.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     */
    private $fileName;
    
    /**
     * @var string The original file name of the proof.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     */
    private $originalName;

    /**
     * @var int The size in bytes of the file.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readSolidary","readSolidaryUser","writeSolidaryUser"})
     */
    private $size;
    
    /**
     * @var string The mime type of the file.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readSolidary","readSolidaryUser"})
     */
    private $mimeType;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="proof", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     */
    private $file;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readSolidaryUser"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readSolidaryUser"})
     */
    private $updatedDate;
    
    public function __construct($id=null)
    {
        $this->id = $id;
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
    
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getStructureProof(): ?StructureProof
    {
        return $this->structureProof;
    }

    public function setStructureProof(?StructureProof $structureProof): self
    {
        $this->structureProof = $structureProof;

        return $this;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }

    public function setSolidary(?Solidary $solidary): self
    {
        $this->solidary = $solidary;

        return $this;
    }

    public function getSolidaryUser(): ?SolidaryUser
    {
        return $this->solidaryUser;
    }

    public function setSolidaryUser(?SolidaryUser $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }
    
    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }
    
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }
    
    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }
    
    public function setSize(?int $size): self
    {
        $this->size = $size;
        
        return $this;
    }
    
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
    
    public function setMimeType(?string $mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }
    
    public function setFile(?File $file)
    {
        $this->file = $file;
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

    public function preventSerialization()
    {
        $this->setFile(null);
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
