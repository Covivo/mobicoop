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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Carpool\Entity\Proposal;
use App\User\Entity\User;

/**
 * A solidary record.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Solidary
{
    /**
     * @var int $id The id of this solidary record.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Ask status (0 = asked; 1 = refused; 2 = pending, 3 = looking for solution; 4 = follow up; 5 = closed).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var bool Social assist.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $assisted;

//    /**
//     * @var string Structure of the solidary record.
//     *
//     * @Assert\NotBlank
//     * @ORM\Column(type="string", length=255)
//     * @Groups({"read","write"})
//     */
//    private $structure;
//
//    /**
//     * @var string Subject of the solidary record.
//     *
//     * @Assert\NotBlank
//     * @ORM\Column(type="string", length=255)
//     * @Groups({"read","write"})
//     */
//    private $subject;
//
//    /**
//     * @var Proposal The proposal.
//     *
//     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
//     * @ORM\JoinColumn(nullable=false)
//     * @Groups({"read","write"})
//     * @MaxDepth(1)
//     */
//    private $proposal;
//
//    /**
//     * @var User The user related with the solidary record.
//     *
//     * @Assert\NotBlank
//     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="solidaries")
//     * @ORM\JoinColumn(nullable=false)
//     * @Groups({"read"})
//     * @MaxDepth(1)
//     */
//    private $user;
//
//    /**
//     * @var \DateTimeInterface Creation date of the solidary record.
//     *
//     * @ORM\Column(type="datetime")
//     * @Groups("read")
//     */
//    private $createdDate;
//
//    /**
//     * @var \DateTimeInterface Updated date of the solidary record.
//     *
//     * @ORM\Column(type="datetime", nullable=true)
//     * @Groups("read")
//     */
//    private $updatedDate;

    public function getId(): int
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

    public function isAssisted(): ?bool
    {
        return $this->assisted;
    }
    
    public function setAssisted(bool $isAssisted): self
    {
        $this->assisted = $isAssisted;
        
        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(string $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getProposal(): Proposal
    {
        return $this->proposal;
    }
    
    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;
        
        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        
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
