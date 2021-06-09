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
use App\Solidary\Controller\CreateProofAction;
use App\Solidary\Admin\Controller\UploadProofAction;

/**
 * A solidary proof related to a solidary record or a solidaryUser
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readProof"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeProof"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('proof_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "controller"=CreateProofAction::class,
 *             "deserialize"=false,
 *             "security_post_denormalize"="is_granted('proof_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/proofs",
 *              "controller"=UploadProofAction::class,
 *              "deserialize"=false,
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security_post_denormalize"="is_granted('proof_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('proof_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *             "security"="is_granted('proof_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *             "security"="is_granted('proof_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "label"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"label":"partial"})
 * @Vich\Uploadable
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
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
     */
    private $id;

    /**
     * @var string The value entered by the user.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readProof","writeProof"})
     */
    private $value;

    /**
     * @var StructureProof Structure proof.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\StructureProof", inversedBy="proofs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readProof","writeProof"})
     * @MaxDepth(1)
     */
    private $structureProof;

    /**
     * @var string The final file name of the proof.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readProof","writeProof"})
     */
    private $fileName;
    
    /**
     * @var string The original file name of the proof.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readProof","writeProof"})
     */
    private $originalName;

    /**
     * @var int The size in bytes of the file.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readProof","writeProof"})
     */
    private $size;
    
    /**
     * @var string The mime type of the file.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readProof","writeProof"})
     */
    private $mimeType;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="proof", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     * @Groups({"readProof","writeProof"})
     */
    private $file;

    /**
     * @var SolidaryUserStructure SolidaryUser Structure relation
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\SolidaryUserStructure", inversedBy="proofs", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readProof","writeProof"})
     * @MaxDepth(1)
     */
    private $solidaryUserStructure;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readProof"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readProof"})
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
    
    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getSolidaryUserStructure(): ?SolidaryUserStructure
    {
        return $this->solidaryUserStructure;
    }
    
    public function setSolidaryUserStructure(?SolidaryUserStructure $solidaryUserStructure): self
    {
        $this->solidaryUserStructure = $solidaryUserStructure;

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
