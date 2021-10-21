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

namespace App\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
// use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Action\Entity\Action;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySolution;
use App\User\Entity\User;

/**
 * Diary for a user.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","readSolidary","readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Diary
{
    /**
     * @var int $id The id of this diary action.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","readUser","readSolidary"})
     */
    private $id;

    /**
     * @var Action The action.
     *
     * @ORM\ManyToOne(targetEntity="\App\Action\Entity\Action")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write","readUser","readSolidary"})
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var string A comment about the action.
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read","write","readUser"})
     */
    private $comment;

    /**
     * @var int|null The progression in percent if the action can be related to a solidary record.
     * Duplicated from the action entity, to keep the original value if the progression changes in the action entity.
     *
     * @ORM\Column(type="decimal", precision=6, scale=2)
     * @Groups({"read","write","readUser"})
     */
    private $progression;
        
    /**
     * @var User The user related with the action.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="diaries")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var User The Author of the action.
     * Can be the user itself or an admin (i.e. register from front)
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="diariesAuthor")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $author;

    /**
     * @var Solidary|null The solidary record if the action concerns a solidary record.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\Solidary")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"read","write", "readSolidary"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
     * @var SolidarySolution|null The solidary solution if the action concerns a solidary record solution.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\SolidarySolution")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"read","write", "readSolidary"})
     * @MaxDepth(1)
     */
    private $solidarySolution;

    /**
     * @var \DateTimeInterface Creation date of the diary action.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the diary action.
     * Special need for this entity : we need to know the last action made in a diary for a solidary record, so we have to know the last date between createdDate and updatedDate.
     * To do so, we will use the updatedDate, so it is mandatory and will be populated with the createdDate at insert time.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $updatedDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
    
    public function setAction(?Action $action): self
    {
        $this->action = $action;
        
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

    public function getProgression(): string
    {
        return $this->progression;
    }

    public function setProgression(string $progression): self
    {
        $this->progression = $progression;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        
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

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }
    
    public function setSolidarySolution(?SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;
        
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
        $this->setUpdatedDate($this->getCreatedDate());
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
