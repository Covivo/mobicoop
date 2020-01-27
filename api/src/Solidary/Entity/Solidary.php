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
use App\Solidary\Controller\SolidaryProposalPost;
use Doctrine\Common\Collections\ArrayCollection;

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
 *      collectionOperations={
 *     "get",
 *     "post"={
 *              "method"="POST",
 *              "path"="/solidaries",
 *              "controller"=SolidaryProposalPost::class,
 *          },
 *     },
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

    /**
     * @var Structure Structure of the solidary record.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure", inversedBy="solidaries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $structure;

    /**
     * @var Subject Subject of the solidary record.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Subject", inversedBy="solidaries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $subject;

    /**
     * @var Proposal The proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $proposal;

    /**
     * @var User The user related with the solidary record.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="solidaries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read", "write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Deadline date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $deadlineDate;

    /**
     * @var string Detail for regular ask.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $regularDetail;

    /**
     * @var ArrayCollection|null The special needs for this solidary record.
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need")
     * @Groups({"read","write"})
     */
    private $needs;

    /**
     * @var \DateTimeInterface Creation date of the solidary record.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $updatedDate;

    public function __construct()
    {
        $this->needs = new ArrayCollection();
    }

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

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
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

    public function getDeadlineDate(): ?\DateTimeInterface
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(\DateTimeInterface $deadlineDate): self
    {
        $this->deadlineDate = $deadlineDate;

        return $this;
    }

    public function getRegularDetail(): ?string
    {
        return $this->regularDetail;
    }

    public function setRegularDetail(string $regularDetail): self
    {
        $this->regularDetail = $regularDetail;

        return $this;
    }

    public function getNeeds()
    {
        return $this->needs->getValues();
    }
    
    public function addNeed(Need $need): self
    {
        if (!$this->needs->contains($need)) {
            $this->needs[] = $need;
        }
        
        return $this;
    }
    
    public function removeNeed(Need $need): self
    {
        if ($this->needs->contains($need)) {
            $this->needs->removeElement($need);
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
