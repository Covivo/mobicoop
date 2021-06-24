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
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary matching
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
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryMatching
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this subject.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidary","writeSolidary","readSolidarySearch"})
     */
    private $id;

    /**
     * @var Matching|null The carpool matching if there is any
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="solidaryMatching", cascade={"persist","remove"})
     * @Groups({"read","results",})
     * @MaxDepth(1)
     */
    private $matching;

    /**
     * @var SolidaryUser The solidary User if needed.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\SolidaryUser", inversedBy="solidaryMatchings")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryUser;

    /**
     * @var Solidary The solidary User if needed.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\Solidary", inversedBy="solidaryMatchings")
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidary;

    /**
     * @var Criteria|null Criteria of this SolidaryAsk
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", inversedBy="solidaryMatching", cascade={"persist","remove"})
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $criteria;

    /**
     * @var SolidarySolution|null SolidarySolution of this SolidaryMatching
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidarySolution", mappedBy="solidaryMatching", cascade={"persist","remove"})
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidarySolution;

    /**
     * @var SolidaryMatching|null The linked solidary matching for return trips.
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryMatching", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $solidaryMatchingLinked;

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

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }

    public function setMatching(Matching $matching): self
    {
        $this->matching = $matching;

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

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getSolidaryMatchingLinked(): ?self
    {
        return $this->solidaryMatchingLinked;
    }

    public function setSolidaryMatchingLinked(?self $solidaryMatchingLinked): self
    {
        $this->solidaryMatchingLinked = $solidaryMatchingLinked;

        // set (or unset) the owning side of the relation if necessary
        $newSolidaryMatchingLinked = $solidaryMatchingLinked === null ? null : $this;
        if (!is_null($solidaryMatchingLinked) && $newSolidaryMatchingLinked !== $solidaryMatchingLinked->getSolidaryMatchingLinked()) {
            $solidaryMatchingLinked->setSolidaryMatchingLinked($newSolidaryMatchingLinked);
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
