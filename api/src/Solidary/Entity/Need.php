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

/**
 * A special need for a solidary record.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary","readNeeds"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "label"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"label":"partial"})
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Need
{
    
    /**
     * @var int The id of this need.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"readUser","readSolidary","readNeeds"})
     */
    private $id;

    /**
     * @var string Label of the need.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @MaxDepth(1)
     * @Groups({"readUser","readSolidary","writeSolidary","readNeeds"})
     */
    private $label;

    /**
     * @var bool The need is not publicly available.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @MaxDepth(1)
     * @Groups({"readUser","readSolidary","writeSolidary","readNeeds"})
     */
    private $private;

    /**
     * @var Solidary Solidary if the need was created for a specific solidary record.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Solidary")
     * @Groups({"readUser","readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
    * @var ArrayCollection|null The structures associated to the need.
    *
    * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Structure", mappedBy="needs")
    * @Groups({"readSolidary", "writeSolidary"})
    * @MaxDepth(1)
    */
    private $structures;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readNeeds"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readNeeds"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->structures = new ArrayCollection();
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

    public function isPrivate(): ?bool
    {
        return $this->private;
    }
    
    public function setPrivate(?bool $isPrivate): self
    {
        $this->private = $isPrivate;
        
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

    public function getStructures()
    {
        return $this->structures->getValues();
    }

    public function setStructures(?ArrayCollection $structures): self
    {
        $this->structures = $structures;

        return $this;
    }

    public function addStructure(Structure $structure): self
    {
        if (!$this->structures->contains($structure)) {
            $this->structures->add($structure);
        }
        return $this;
    }

    public function removeStructure(Structure $structure): self
    {
        if ($this->structures->contains($structure)) {
            $this->structures->removeElement($structure);
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
