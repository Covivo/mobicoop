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
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Carpool\Entity\Proposal;
use App\Geography\Entity\Address;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary record.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}},
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('solidary_list',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_create',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('solidary_read',object)"
 *          },
 *          "contactsList"={
 *              "method"="GET",
 *              "path"="/solidaries/{id}/contactsList",
 *              "normalization_context"={"groups"={"asksList"}},
 *              "security"="is_granted('solidary_read',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('solidary_update',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('solidary_delete',object)"
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Solidary
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int $id The id of this solidary record.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readSolidary","writeSolidary","readSolidarySearch"})
     */
    private $id;

    /**
     * @var int Ask status (0 = asked; 1 = refused; 2 = pending, 3 = looking for solution; 4 = follow up; 5 = closed).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $status;

    /**
     * @var string Detail for regular ask.
     *
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $regularDetail;

    /**
     * @var \DateTimeInterface Deadline date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $deadlineDate;

    /**
     * @var \DateTimeInterface Creation date of the solidary record.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readSolidary"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary record.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readSolidary"})
     */
    private $updatedDate;

    /**
     * @var SolidaryUserStructure The SolidaryUserStructure related with the solidary record.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\SolidaryUserStructure", inversedBy="solidaries", cascade={"persist","remove"})
     * @Groups({"readSolidary", "writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryUserStructure;

    /**
     * @var Proposal The proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $proposal;

    /**
     * @var Subject Subject of the solidary record.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Subject", inversedBy="solidaries", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $subject;

    /**
     * @var ArrayCollection|null The special needs for this solidary record.
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need")
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $needs;

    /**
     * @var ArrayCollection|null Solidary solutions.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidarySolution", mappedBy="solidary", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $solidarySolutions;

    /**
     * @var ArrayCollection|null Solidary matchings.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="solidary", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryMatchings;

    /**
     * @var float Progression of this solidary
     * @Groups({"readSolidary"})
     */
    private $progression;

    /**
     * @var array List of the Asks of this solidary (special route)
     * @Groups({"asksList"})
     */
    private $asksList;

    /**
     * @var SolidaryUser SolidaryUser associated ti the ask
     * @Groups ({"writeSolidary"})
     */
    private $solidaryUser;

    /**
     * @var Address Origin address of the solidary
     * @Groups ({"writeSolidary"})
     */
    private $origin;

    /**
     * @var Address Destination address of the solidary
     * @Groups ({"writeSolidary"})
     */
    private $destination;

    /**
     * @var \DateTimeInterface outward date and time of the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $outwardDatetime;

    /**
     * @var \DateTimeInterface outward deadline date and time of the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $outwardDeadlineDatetime;

    /**
     * @var \DateTimeInterface return date and time of the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $returnDatetime;

    /**
     * @var \DateTimeInterface return deadline date and time of the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $returnDeadlineDatetime;
    
    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->needs = new ArrayCollection();
        $this->solidarySolutions = new ArrayCollection();
        $this->solidaryMatchings = new ArrayCollection();
        $this->proofs = new ArrayCollection();
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

    public function getRegularDetail(): ?string
    {
        return $this->regularDetail;
    }

    public function setRegularDetail(string $regularDetail): self
    {
        $this->regularDetail = $regularDetail;

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

    public function getSolidaryUserStructure(): ?SolidaryUserStructure
    {
        return $this->solidaryUserStructure;
    }

    public function setSolidaryUserStructure(?SolidaryUserStructure $solidaryUserStructure): self
    {
        $this->solidaryUserStructure = $solidaryUserStructure;
        
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

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getNeeds()
    {
        return $this->needs->getValues();
    }

    public function addNeed(Need $need): self
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
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

    public function getSolidarySolutions()
    {
        return $this->solidarySolutions->getValues();
    }
    
    public function addSolidarySolution(SolidarySolution $solidarySolution): self
    {
        if (!$this->solidarySolutions->contains($solidarySolution)) {
            $this->solidarySolutions[] = $solidarySolution;
        }
        
        return $this;
    }
    
    public function removeSolidarySolution(SolidarySolution $solidarySolution): self
    {
        if ($this->solidarySolutions->contains($solidarySolution)) {
            $this->solidarySolutions->removeElement($solidarySolution);
        }
        
        return $this;
    }

    public function getSolidaryMatchings()
    {
        return $this->solidaryMatchings->getValues();
    }
    
    public function addSolidaryMatching(SolidaryMatching $solidaryMatching): self
    {
        if (!$this->solidaryMatchings->contains($solidaryMatching)) {
            $this->solidaryMatchings[] = $solidaryMatching;
        }
        
        return $this;
    }
    
    public function removeSolidaryMatching(SolidaryMatching $solidaryMatching): self
    {
        if ($this->solidaryMatchings->contains($solidaryMatching)) {
            $this->solidaryMatchings->removeElement($solidaryMatching);
        }
        
        return $this;
    }

    public function getProgression(): ?string
    {
        return $this->progression;
    }

    public function setProgression(?string $progression): self
    {
        $this->progression = $progression;

        return $this;
    }

    public function getAsksList(): ?array
    {
        return $this->asksList;
    }

    public function setAsksList(?array $asksList): self
    {
        $this->asksList = $asksList;

        return $this;
    }

    public function getSolidaryUser(): SolidaryUser
    {
        return $this->solidaryUser;
    }
    
    public function setSolidaryUser(?SolidaryUser $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;
        
        return $this;
    }

    public function getOrigin(): Address
    {
        return $this->origin;
    }
    
    public function setOrigin(?Address $origin): self
    {
        $this->origin = $origin;
        
        return $this;
    }

    public function getDestination(): Address
    {
        return $this->destination;
    }
    
    public function setDestination(?Address $destination): self
    {
        $this->destination = $destination;
        
        return $this;
    }

    public function getOurtwardDatetime(): ?\DateTimeInterface
    {
        return $this->ourtwardDatetime;
    }

    public function setOurtwardDatetime(\DateTimeInterface $ourtwardDatetime): self
    {
        $this->ourtwardDatetime = $ourtwardDatetime;

        return $this;
    }

    public function getOurtwardDeadlineDatetime(): ?\DateTimeInterface
    {
        return $this->ourtwardDeadlineDatetime;
    }

    public function setOurtwardDeadlineDatetime(\DateTimeInterface $ourtwardDeadlineDatetime): self
    {
        $this->ourtwardDeadlineDatetime = $ourtwardDeadlineDatetime;

        return $this;
    }

    public function getReturnDatetime(): ?\DateTimeInterface
    {
        return $this->returnDatetime;
    }

    public function setReturnDatetime(\DateTimeInterface $returnDatetime): self
    {
        $this->returnDatetime = $returnDatetime;

        return $this;
    }

    public function getReturnDeadlineDatetime(): ?\DateTimeInterface
    {
        return $this->returnDeadlineDatetime;
    }

    public function setReturnDeadlineDatetime(\DateTimeInterface $returnDeadlineDatetime): self
    {
        $this->returnDeadlineDatetime = $returnDeadlineDatetime;

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
