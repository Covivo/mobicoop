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
use App\Carpool\Entity\Matching;

/**
 * Matchings saved by an admin for a solidary record.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class SolidaryMatching
{
    /**
     * @var int $id The id of this solidary matching.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("readSolidary")
     */
    private $id;

    /**
     * @var Matching The matching.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $matching;
        
    /**
     * @var Solidary The solidary record.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Solidary", inversedBy="solidaryMatchings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
     * @var Volunteer The volunteer if needed.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\Volunteer")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $volunteer;

    /**
     * @var string A comment about the solidary matching.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $comment;

    /**
     * @var \DateTimeInterface Creation date of the solidary record.
     *
     * @ORM\Column(type="datetime")
     * @Groups("readSolidary")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("readSolidary")
     */
    private $updatedDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }
    
    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;
        
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

    public function getVolunteer(): Volunteer
    {
        return $this->volunteer;
    }
    
    public function setVolunteer(?Volunteer $volunteer): self
    {
        $this->volunteer = $volunteer;
        
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
