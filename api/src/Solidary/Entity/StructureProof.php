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

/**
 * Proof documents for a solidary structure.
 * Can be applied to solidary user.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readUser","readSolidary","userStructure","readStructureProofs"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('structure_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('structure_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('structure_read',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('structure_update',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('structure_delete',object)"
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "label"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"label":"partial"})
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StructureProof
{
    const TYPE_REQUESTER = 1;
    const TYPE_VOLUNTEER = 2;
    
    /**
     * @var int The id of this structure proof.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs"})
     */
    private $id;

    /**
     * @var string Label of the proof. This label will appear in the relevant form.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $label;

    /**
     * @var int Proof user type (1 = solidary requester, 2 = volunteer)
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $type;

    /**
     * @var int Position of the proof ask in the form
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $position;

    /**
     * @var bool The proof is a checkbox.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $checkbox;

    /**
     * @var bool The proof is a input.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $input;

    /**
     * @var bool The proof is a select.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $selectbox;

    /**
     * @var bool The proof is a radio button.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $radio;

    /**
     * @var string Text options for radio or select (separated by semicolon, in the same order than values).
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $options;

    /**
     * @var string Values for radio or select (separated by semicolon, in the same order than options).
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $acceptedValues;

    /**
     * @var bool The proof is a file.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $file;

    /**
     * @var bool Is the proof mandatory ?
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","readUser","readSolidary","writeSolidary","userStructure","readStructureProofs","readProof"})
     */
    private $mandatory;

    /**
     * @var Structure Structure of the proof.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="structureProofs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $structure;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readStructureProofs"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readStructureProofs"})
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null The proofs using this structure proof.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Proof", mappedBy="structureProof", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"writeSolidary"})
     * @MaxDepth(1)
     */
    private $proofs;

    /**
     * @var bool The structure proof is removable (not removable if it is used for a solidary record).
     *
     * @Groups("aRead")
     */
    private $removable;

    public function __construct()
    {
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
    
    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isCheckbox(): ?bool
    {
        return $this->checkbox;
    }
    
    public function setCheckbox(?bool $isCheckbox): self
    {
        $this->checkbox = $isCheckbox;
        
        return $this;
    }

    public function isInput(): ?bool
    {
        return $this->input;
    }
    
    public function setInput(?bool $isInput): self
    {
        $this->input = $isInput;
        
        return $this;
    }

    public function isSelectbox(): ?bool
    {
        return $this->selectbox;
    }
    
    public function setSelectbox(?bool $isSelectbox): self
    {
        $this->selectbox = $isSelectbox;
        
        return $this;
    }

    public function isRadio(): ?bool
    {
        return $this->radio;
    }
    
    public function setRadio(?bool $isRadio): self
    {
        $this->radio = $isRadio;
        
        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }
    
    public function setOptions(?string $options)
    {
        $this->options = $options;
    }

    public function getAcceptedValues(): ?string
    {
        return $this->acceptedValues;
    }
    
    public function setAcceptedValues(?string $acceptedValues)
    {
        $this->acceptedValues = $acceptedValues;
    }

    public function isFile(): ?bool
    {
        return $this->file;
    }
    
    public function setFile(?bool $isFile): self
    {
        $this->file = $isFile;
        
        return $this;
    }

    public function isMandatory(): ?bool
    {
        return $this->mandatory;
    }
    
    public function setMandatory(?bool $mandatory): self
    {
        $this->mandatory = $mandatory;
        
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

    public function getProofs()
    {
        return $this->proofs->getValues();
    }
    
    public function addProof(Proof $proof): self
    {
        if (!$this->proofs->contains($proof)) {
            $this->proofs->add($proof);
            $proof->setStructureProof($this);
        }
        
        return $this;
    }
    
    public function removeProof(Proof $proof): self
    {
        if ($this->proofs->contains($proof)) {
            $this->proofs->removeElement($proof);
            // set the owning side to null (unless already changed)
            if ($proof->getStructureProof() === $this) {
                $proof->setStructureProof(null);
            }
        }
        
        return $this;
    }

    public function isRemovable(): ?bool
    {
        return count($this->getProofs())==0;
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
