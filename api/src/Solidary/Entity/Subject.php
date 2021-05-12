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
use App\Carpool\Entity\Proposal;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary subject.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('subject_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('subject_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('subject_read',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('subject_update',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('subject_delete',object)"
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "label"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"label":"partial"})
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Subject
{
    
    /**
     * @var int The id of this subject.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","readSolidary","writeSolidary","readSubjects"})
     */
    private $id;

    /**
     * @var string Label of the subject.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","readSolidary","writeSolidary","readSubjects"})
     */
    private $label;

    /**
     * @var Structure Structure of the subject.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="subjects")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writeSolidary"})
     * @MaxDepth(1)
     */
    private $structure;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readSubjects"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","readSubjects"})
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null The solidary records for this subject.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\Solidary", mappedBy="subject", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"writeSolidary"})
     */
    private $solidaries;

    /**
     * @var ArrayCollection|null The Proposals linked to this subject
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="subject")
     * @Groups({"writeSolidary"})
     */
    private $proposals;

    /**
     * @var bool The subject is removable (not removable if it is used for a solidary).
     *
     * @Groups("aRead")
     */
    private $removable;


    public function __construct()
    {
        $this->solidaries = new ArrayCollection();
        $this->proposals = new ArrayCollection();
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

    public function getSolidaries()
    {
        return $this->solidaries->getValues();
    }

    public function addSolidary(Solidary $solidary): self
    {
        if (!$this->solidaries->contains($solidary)) {
            $this->solidaries->add($solidary);
            $solidary->setSubject($this);
        }

        return $this;
    }

    public function removeSolidary(Solidary $solidary): self
    {
        if ($this->solidaries->contains($solidary)) {
            $this->solidaries->removeElement($solidary);
            // set the owning side to null (unless already changed)
            if ($solidary->getSubject() === $this) {
                $solidary->setSubject(null);
            }
        }

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals->getValues();
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setSubject($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getSubject() === $this) {
                $proposal->setSubject(null);
            }
        }

        return $this;
    }

    public function isRemovable(): ?bool
    {
        return count($this->getSolidaries())==0;
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
