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
use App\User\Entity\User;
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
 *             "security"="is_granted('solidary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "getMySolidaries"={
 *              "method"="GET",
 *              "path"="/solidaries/mySolidaries",
 *              "normalization_context"={"groups"={"readSolidary"}},
 *              "security"="is_granted('solidary_list_self',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "postUl"={
 *              "method"="POST",
 *              "path"="/solidaries/postUl",
 *              "security_post_denormalize"="is_granted('solidary_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('solidary_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "contactsList"={
 *              "method"="GET",
 *              "path"="/solidaries/{id}/contactsList",
 *              "normalization_context"={"groups"={"asksList"}},
 *              "security"="is_granted('solidary_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "put"={
 *             "security"="is_granted('solidary_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "delete"={
 *             "security"="is_granted('solidary_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 *
 *  Exemples for regular :
 *
 *  "days":  {
 *      "mon": 1,
 *      "tue": 1,
 *      "wed": 0,
 *      "thu": 1,
 *      "fri": 1,
 *      "sat": 0,
 *      "sun": 0
 *    },
 *  "outwardTimes": {
 *     "mon": "08:00",
 *     "tue": "08:00",
 *     "wed": null,
 *     "thu": "09:00",
 *     "fri": "08:00",
 *     "sat": null,
 *     "sun": null
 *  },
 *  "returnTimes": {
 *     "mon": "18:00",
 *     "tue": "18:00",
 *     "wed": null,
 *     "thu": "19:00",
 *     "fri": "18:00",
 *     "sat": null,
 *     "sun": null
 *  }
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
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
     * @Groups({"readSolidary"})
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
     * @Groups({"writeSolidary", "readSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryUserStructure;

    /**
     * @var Proposal The proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writeSolidary"})
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
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need", cascade={"persist","remove"})
     * @Groups({"readSolidary","writeSolidary"})
     * @MaxDepth(1)
     */
    private $needs;

    /**
     * @var ArrayCollection|null Solidary solutions.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidarySolution", mappedBy="solidary", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"writeSolidary"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $solidarySolutions;

    /**
     * @var ArrayCollection|null Solidary matchings.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryMatching", mappedBy="solidary", cascade={"remove"}, orphanRemoval=true)
     * @Groups({"writeSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryMatchings;

    /**
     * @var float Progression of this solidary
     * @Groups({"readSolidary", "writeSolidary"})
     */
    private $progression;

    /**
     * @var array List of the Asks of this solidary (special route)
     * @Groups({"asksList","writeSolidary", "readSolidary"})
     */
    private $asksList;

    /**
     * @var SolidaryUser SolidaryUser associated ti the ask
     * @Groups ({"writeSolidary", "readSolidary"})
     * @MaxDepth(1)
     */
    private $solidaryUser;

    /**
     * @var Array|null Address of the user who create the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $homeAddress;

    /**
    * @var Array|null Origin address of the solidary
     * @Groups ({"writeSolidary", "readSolidary"})
    */
    private $origin;

    /**
     * @var Array|null Destination address of the solidary
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $destination;

    /**
     * @var \DateTimeInterface outward date and time of the solidary demand PUNCTUAL (and only DATE for REGULAR)
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $outwardDatetime;

    /**
     * @var \DateTimeInterface|null outward deadline date and time of the solidary demand
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $outwardDeadlineDatetime;

    /**
     * @var \DateTimeInterface|null return date and time of the solidary demand PUNCTUAL (and only DATE for REGULAR)
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $returnDatetime;

    /**
     * @var \DateTimeInterface|null Return deadline date and time of the solidary demand
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $returnDeadlineDatetime;

    /**
     * @var User The source user for the solidaryUser
     *
     * @Groups({"writeSolidary"})
     */
    private $user;

    /**
     * @var String|null Email of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $email;

    /**
     * @var String|null Password of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $password;

    /**
     * @var String|null Telephone of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $telephone;

    /**
     * @var String|null familyname of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $familyName;

    /**
     * @var String|null given name of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $givenName;
    
    /**
     * @var Int|null Gender of the user who ask for the solidary demand (1=female, 2=male, 3=nc)
     * @Groups ({"writeSolidary"})
     */
    private $gender;

    /**
     * @var \DateTimeInterface|null Birthdate of the user who ask for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $birthDate;

    /**
     * @var Array|null proofs needed for the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $proofs;

    /**
     * @var String|null structure of the solidary demand
     * @Groups ({"writeSolidary"})
     */
    private $structure;

    /**
     * @var Int|null frequency of the solidary demand
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $frequency;

    /**
     * @var Array|null Days for the solidary if it's regular
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $days;

    /**
     * @var Array|null Outward times for the solidary if it's regular
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $outwardTimes;

    /**
     * @var Array|null Return times for the solidary if it's regular
     * @Groups ({"writeSolidary", "readSolidary"})
     */
    private $returnTimes;
    
    /**
    * @var Int|null margin time of the solidary demand
    * @Groups ({"writeSolidary", "readSolidary"})
    */
    private $marginDuration;

    /**
     * @var boolean|null The user can be a driver.
     *
     * @Groups({"writeSolidary", "readSolidary"})
     */
    private $driver;

    /**
     * @var boolean|null The user can be a passenger.
     *
     * @Groups({"writeSolidary", "readSolidary"})
     */
    private $passenger;

    /**
    * @var String|null Label to display for the solidary subject+origin+destination
    *
    * @Groups({"readSolidary"})
    */
    private $displayLabel;

    /**
    * @var String|null Name of the last action associted to the solidary
    *
    * @Groups({ "readSolidary"})
    */
    private $lastAction;

    /**
     * @var User|null The last User who made an action on that solidary
     *
     * @Groups({"readSolidary"})
     */
    private $operator;

    /**
     * @var Array|null Solutions associated to this demand
     * @Groups ({"readSolidary"})
     */
    private $solutions;
    
    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->needs = new ArrayCollection();
        $this->solidarySolutions = new ArrayCollection();
        $this->solidaryMatchings = new ArrayCollection();
        $this->proofs = [];
        $this->origin = [];
        $this->destination = [];
        $this->days = [];
        $this->homeAddress = [];
        $this->solutions = [];
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

    public function setDeadlineDate(?\DateTimeInterface $deadlineDate): self
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

    public function getSolidaryUser(): ?SolidaryUser
    {
        return $this->solidaryUser;
    }
    
    public function setSolidaryUser(?SolidaryUser $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;
        
        return $this;
    }


    public function getHomeAddress(): ?array
    {
        return $this->homeAddress;
    }
    
    public function setHomeAddress(?array $homeAddress): self
    {
        $this->homeAddress = $homeAddress;
        
        return $this;
    }

    public function getOrigin(): ?array
    {
        return $this->origin;
    }
    
    public function setOrigin($origin): self
    {
        $this->origin = $origin;
        
        return $this;
    }

    public function getDestination(): ?array
    {
        return $this->destination;
    }
    
    public function setDestination($destination): self
    {
        $this->destination = $destination;
        
        return $this;
    }

    public function getOutwardDatetime(): ?\DateTimeInterface
    {
        return $this->outwardDatetime;
    }

    public function setOutwardDatetime(\DateTimeInterface $outwardDatetime): self
    {
        $this->outwardDatetime = $outwardDatetime;

        return $this;
    }

    public function getOutwardDeadlineDatetime(): ?\DateTimeInterface
    {
        return $this->outwardDeadlineDatetime;
    }

    public function setOutwardDeadlineDatetime(?\DateTimeInterface $outwardDeadlineDatetime): self
    {
        $this->outwardDeadlineDatetime = $outwardDeadlineDatetime;

        return $this;
    }

    public function getReturnDatetime(): ?\DateTimeInterface
    {
        return $this->returnDatetime;
    }

    public function setReturnDatetime(?\DateTimeInterface $returnDatetime): self
    {
        $this->returnDatetime = $returnDatetime;

        return $this;
    }

    public function getReturnDeadlineDatetime(): ?\DateTimeInterface
    {
        return $this->returnDeadlineDatetime;
    }

    public function setReturnDeadlineDatetime(?\DateTimeInterface $returnDeadlineDatetime): self
    {
        $this->returnDeadlineDatetime = $returnDeadlineDatetime;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getProofs(): array
    {
        return $this->proofs;
    }
    
    public function setProofs(?array $proofs): self
    {
        $this->proofs = $proofs;
        
        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }
    
    public function setStructure(?string $structure): self
    {
        $this->structure = $structure;
        
        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }
    
    public function setFrequency(?int $frequency): self
    {
        $this->frequency = $frequency;
        
        return $this;
    }

    public function getDays(): ?array
    {
        return $this->days;
    }
    
    public function setDays(?array $days): self
    {
        $this->days = $days;
        
        return $this;
    }

    public function getOutwardTimes(): ?array
    {
        return $this->outwardTimes;
    }
    
    public function setOutwardTimes(?array $outwardTimes): self
    {
        $this->outwardTimes = $outwardTimes;
        
        return $this;
    }

    public function getReturnTimes(): ?array
    {
        return $this->returnTimes;
    }
    
    public function setReturnTimes(?array $returnTimes): self
    {
        $this->returnTimes = $returnTimes;
        
        return $this;
    }

    public function getMarginDuration(): ?int
    {
        return $this->marginDuration;
    }

    public function setMarginDuration(?int $marginDuration): self
    {
        $this->marginDuration = $marginDuration;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->driver;
    }
    
    public function setDriver(bool $isDriver): self
    {
        $this->driver = $isDriver;
        
        return $this;
    }
    
    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }
    
    public function setPassenger(bool $isPassenger): self
    {
        $this->passenger = $isPassenger;
        
        return $this;
    }

    public function getDisplayLabel(): ?string
    {
        return $this->displayLabel;
    }
    
    public function setDisplayLabel(?string $displayLabel): self
    {
        $this->displayLabel = $displayLabel;
        
        return $this;
    }

    public function getLastAction(): ?string
    {
        return $this->lastAction;
    }
    
    public function setLastAction(?string $lastAction): self
    {
        $this->lastAction = $lastAction;
        
        return $this;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function setOperator(?User $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getSolutions(): ?array
    {
        return $this->solutions;
    }
    
    public function setSolutions(?array $solutions): self
    {
        $this->solutions = $solutions;
        
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
