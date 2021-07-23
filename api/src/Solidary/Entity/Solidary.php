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
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use App\Carpool\Entity\Criteria;
use App\Geography\Entity\Address;
use DateTime;

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
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidaries",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadCol"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_actions_get"={
 *              "path"="/admin/solidaries/actions",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aReadCol"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_solidary_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/solidaries",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aReadCreated"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_solidary_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
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
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/solidaries/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aReadItem"}},
 *              "security"="is_granted('admin_solidary_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/solidaries/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aReadItem"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_solidary_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "givenName":"partial",
 *          "familyName":"partial",
 *          "solidaryUserStructure.solidaryUser.user.givenName":"partial",
 *          "solidaryUserStructure.solidaryUser.user.familyName":"partial"
 *      }
 * )
 * @ApiFilter(
 *      RangeFilter::class,
 *      properties={
 *          "progression"
 *      }
 * )
 * @ApiFilter(
 *      OrderFilter::class,
 *      properties={
 *          "id",
 *          "givenName",
 *          "familyName",
 *          "telephone",
 *          "subject",
 *          "progression",
 *          "lastActionDate",
 *          "solidaryUserStructure.solidaryUser.user.givenName",
 *          "solidaryUserStructure.solidaryUser.user.familyName",
 *          "solidaryUserStructure.solidaryUser.user.telephone",
 *          "subject.label",
 *          "proposal.criteria.fromDate"
 *      },
 *      arguments={"orderParameterName"="order"}
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
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class Solidary
{
    const DEFAULT_ID = 999999999999;

    const STATUS_ASKED = 0;
    const STATUS_REFUSED = 1;
    const STATUS_PENDING = 2;
    const STATUS_LOOKING_FOR_SOLUTION = 3;
    const STATUS_FOLLOW_UP = 4;
    const STATUS_CLOSED = 5;
    const STATUS_CLOSED_FOR_EDITION = 6;

    const STATUSES = [
        self::STATUS_ASKED,
        self::STATUS_REFUSED,
        self::STATUS_PENDING,
        self::STATUS_LOOKING_FOR_SOLUTION,
        self::STATUS_FOLLOW_UP,
        self::STATUS_CLOSED
    ];

    const PUNCTUAL_OUTWARD_DATE_CHOICE_DATE = 1;    // chosen date
    const PUNCTUAL_OUTWARD_DATE_CHOICE_7 = 2;       // in the next 7 days
    const PUNCTUAL_OUTWARD_DATE_CHOICE_15 = 3;      // in the next 15 days
    const PUNCTUAL_OUTWARD_DATE_CHOICE_30 = 4;      // in the next 30 days

    const PUNCTUAL_OUTWARD_DATE_CHOICES = [
        self::PUNCTUAL_OUTWARD_DATE_CHOICE_DATE,
        self::PUNCTUAL_OUTWARD_DATE_CHOICE_7,
        self::PUNCTUAL_OUTWARD_DATE_CHOICE_15,
        self::PUNCTUAL_OUTWARD_DATE_CHOICE_30
    ];

    const PUNCTUAL_TIME_CHOICE_TIME = 1;    // chosen time
    const PUNCTUAL_TIME_CHOICE_M = 2;       // structure morning range
    const PUNCTUAL_TIME_CHOICE_A = 3;       // structure afternoon range
    const PUNCTUAL_TIME_CHOICE_E = 4;       // structure evening range

    const PUNCTUAL_TIME_CHOICES = [
        self::PUNCTUAL_TIME_CHOICE_TIME,
        self::PUNCTUAL_TIME_CHOICE_M,
        self::PUNCTUAL_TIME_CHOICE_A,
        self::PUNCTUAL_TIME_CHOICE_E
    ];

    const PUNCTUAL_RETURN_DATE_CHOICE_NULL = 1;     // no return
    const PUNCTUAL_RETURN_DATE_CHOICE_1 = 2;        // one hour later
    const PUNCTUAL_RETURN_DATE_CHOICE_2 = 3;        // 2 hours later
    const PUNCTUAL_RETURN_DATE_CHOICE_3 = 4;        // 3 hours later
    const PUNCTUAL_RETURN_DATE_CHOICE_DATE = 5;     // chosen date and time

    const PUNCTUAL_RETURN_DATE_CHOICES = [
        self::PUNCTUAL_RETURN_DATE_CHOICE_NULL,
        self::PUNCTUAL_RETURN_DATE_CHOICE_1,
        self::PUNCTUAL_RETURN_DATE_CHOICE_2,
        self::PUNCTUAL_RETURN_DATE_CHOICE_3,
        self::PUNCTUAL_RETURN_DATE_CHOICE_DATE
    ];

    /**
     * @var int $id The id of this solidary record.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aReadItem","aReadCol","aReadCreated","readSolidary","writeSolidary","readSolidarySearch"})
     */
    private $id;

    /**
     * @var int Status of the record (0 = asked; 1 = refused; 2 = pending, 3 = looking for solution; 4 = follow up; 5 = closed, 6 = closed for update).
     *
     * @Assert\NotBlank(groups={"writeSolidary"})
     * @ORM\Column(type="smallint")
     * @Groups({"aReadItem","aReadCol","readSolidary","writeSolidary"})
     */
    private $status;

    /**
     * @var int Original frequency of the proposal (a punctual proposal could be transformed to a regular proposal in case of a flexible demand).
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $frequency;

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
     * @var Solidary|null Original solidary record if updated solidary record.
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\Solidary", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups("aRead")
     * @MaxDepth(1)
     */
    private $solidary;

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
     * @var ArrayCollection|null Diary entry.
     * Ordered desc to get the last entry first.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Diary", mappedBy="solidary", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $diaries;

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
     * The proposal is set as nullable but is in fact mandatory : we create the solidary record *before* the proposal for technical reasons.
     * The proposal will then be set a short time after the solidary record is created.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @Groups({"writeSolidary"})
     * @MaxDepth(1)
     */
    private $proposal;

    /**
     * @var Subject Subject of the solidary record.
     *
     * @Assert\NotBlank(groups={"writeSolidary"})
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Subject", inversedBy="solidaries", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"aRead","readSolidary","writeSolidary"})
     */
    private $subject;

    /**
     * @var ArrayCollection|null The special needs for this solidary record.
     *
     * @ORM\ManyToMany(targetEntity="\App\Solidary\Entity\Need", cascade={"persist","remove"})
     * @Groups({"aReadItem","readSolidary","writeSolidary"})
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
     * @var int Punctual outward date choice.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("aReadItem")
     */
    private $punctualOutwardDateChoice;

    /**
     * @var int Punctual outward time choice.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("aReadItem")
     */
    private $punctualOutwardTimeChoice;

    /**
     * @var int Punctual return date choice.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("aReadItem")
     */
    private $punctualReturnDateChoice;

    /**
     * @var int Regular date choice.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("aReadItem")
     */
    private $regularDateChoice;

    /**
     * @var float Progression of this solidary
     *
     * @ORM\Column(type="decimal", precision=6, scale=2)
     * @Groups({"aReadCol","aReadItem","readSolidary", "writeSolidary"})
     */
    private $progression;

    /**
     * @var array List of the Asks of this solidary (special route)
     * @Groups({"asksList","writeSolidary", "readSolidary"})
     */
    private $asksList;

    /**
     * @var SolidaryUser SolidaryUser associated to the ask
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
        $this->diaries = new ArrayCollection();
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

    public function __clone()
    {
        $this->needs = new ArrayCollection();
        $this->setProposal(null);
        $this->updatedDate = null;
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

    public function getSolidary(): ?self
    {
        return $this->solidary;
    }
    
    public function setSolidary(?self $solidary): self
    {
        $this->solidary = $solidary;
                
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

    public function getProposal(): ?Proposal
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

    public function getDiaries()
    {
        return $this->diaries->getValues();
    }

    public function getProgression(): float
    {
        return $this->progression;
    }

    public function setProgression(float $progression): self
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

    public function getPunctualOutwardDateChoice(): ?int
    {
        return $this->punctualOutwardDateChoice;
    }
    
    public function setPunctualOutwardDateChoice(?int $punctualOutwardDateChoice): self
    {
        $this->punctualOutwardDateChoice = $punctualOutwardDateChoice;
        
        return $this;
    }

    public function getPunctualOutwardTimeChoice(): ?int
    {
        return $this->punctualOutwardTimeChoice;
    }
    
    public function setPunctualOutwardTimeChoice(?int $punctualOutwardTimeChoice): self
    {
        $this->punctualOutwardTimeChoice = $punctualOutwardTimeChoice;
        
        return $this;
    }

    public function getPunctualReturnDateChoice(): ?int
    {
        return $this->punctualReturnDateChoice;
    }
    
    public function setPunctualReturnDateChoice(?int $punctualReturnDateChoice): self
    {
        $this->punctualReturnDateChoice = $punctualReturnDateChoice;
        
        return $this;
    }

    public function getRegularDateChoice(): ?int
    {
        return $this->regularDateChoice;
    }
    
    public function setRegularDateChoice(?int $regularDateChoice): self
    {
        $this->regularDateChoice = $regularDateChoice;
        
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




    // ADMIN CUSTOM PROPERTIES

    /**
     * @var int|null Solidary id of the parent solidary record
     * @Groups("aReadItem")
     */
    public function getAdminsolidaryId(): ?int
    {
        return $this->getSolidary() ? $this->getSolidary()->getId() : null;
    }
    
    /**
     * @var string|null Subject of the solidary record
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminsubject(): ?string
    {
        return $this->getSubject()->getLabel();
    }

    /**
     * @var int|null Subject id of the solidary record
     * @Groups("aReadItem")
     */
    public function getAdminsubjectId(): ?int
    {
        return $this->getSubject()->getId();
    }

    /**
     * @var int|null Id of the beneficiary
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminuserId(): int
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getId();
    }

    /**
     * @var string|null Given name of the beneficiary
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdmingivenName(): ?string
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getGivenName();
    }

    /**
     * @var string|null Family name of the beneficiary
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminfamilyName(): ?string
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getFamilyName();
    }

    /**
     * @var string|null Email of the beneficiary
     * @Groups("aReadItem")
     */
    public function getAdminemail(): ?string
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getEmail();
    }

    /**
     * @var string|null Avatar of the beneficiary
     * @Groups("aReadItem")
     */
    public function getAdminavatar(): ?string
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getAvatar();
    }

    /**
     * @var int|null Gender of the beneficiary
     * @Groups("aReadItem")
     */
    public function getAdmingender(): ?int
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getGender();
    }

    /**
     * @var DateTimeInterface|null Birthdate of the beneficiary
     * @Groups("aReadItem")
     */
    public function getAdminbirthDate(): ?\DateTimeInterface
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getBirthDate();
    }

    /**
     * @var bool|null News subscription for the beneficiary
     * @Groups("aReadItem")
     */
    public function hasAdminnewsSubscription(): bool
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->hasNewsSubscription() ? true : false;
    }

    /**
     * @var array|null Home address of the beneficiary
     * @Groups("aReadItem")
     */
    public function getAdminhomeAddress(): ?array
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getHomeAddress()->jsonSerialize();
    }
    
    /**
     * @var string|null Given name of the operator
     * @Groups("aReadItem")
     */
    private $adminoperatorGivenName;
    public function getAdminoperatorGivenName(): ?string
    {
        return $this->adminoperatorGivenName;
    }
    public function setAdminoperatorGivenName(string $adminoperatorGivenName): self
    {
        $this->adminoperatorGivenName = $adminoperatorGivenName;

        return $this;
    }

    /**
     * @var string|null Family name of the operator
     * @Groups("aReadItem")
     */
    private $adminoperatorFamilyName;
    public function getAdminoperatorFamilyName(): ?string
    {
        return $this->adminoperatorFamilyName;
    }
    public function setAdminoperatorFamilyName(string $adminoperatorFamilyName): self
    {
        $this->adminoperatorFamilyName = $adminoperatorFamilyName;

        return $this;
    }

    /**
     * @var string|null Avatar of the operator
     * @Groups("aReadItem")
     */
    private $adminoperatorAvatar;
    public function getAdminoperatorAvatar(): ?string
    {
        return $this->adminoperatorAvatar;
    }
    public function setAdminoperatorAvatar(string $adminoperatorAvatar): self
    {
        $this->adminoperatorAvatar = $adminoperatorAvatar;

        return $this;
    }

    /**
     * @var string|null Telephone of the beneficiary
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdmintelephone(): ?string
    {
        return $this->getSolidaryUserStructure()->getSolidaryUser()->getUser()->getTelephone();
    }

    /**
     * @var string|null Structure of the solidary record
     * @Groups("aReadItem")
     */
    public function getAdminstructure(): ?string
    {
        return $this->getSolidaryUserStructure()->getStructure()->getName();
    }

    /**
     * @var string|null Structure id of the solidary record
     * @Groups("aReadItem")
     */
    public function getAdminstructureId(): ?int
    {
        return $this->getSolidaryUserStructure()->getStructure()->getId();
    }

    /**
     * @var int Mode of the solidary record
     * @Groups("aReadItem")
     */
    public function getAdminmode(): int
    {
        return $this->getSolidaryUserStructure()->getStructure()->getMode() ? $this->getSolidaryUserStructure()->getStructure()->getMode() : 0;
    }

    /**
     * @var string|null Last action for the solidary record
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminlastAction(): ?string
    {
        return $this->getDiaries()[0]->getAction()->getName();
    }

    /**
     * @var DateTime|null Last action date for the solidary record
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminlastActionDate(): ?DateTime
    {
        return $this->getDiaries()[0]->getCreatedDate();
    }

    /**
     * @var Address|null Origin for the solidary record
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdminorigin(): ?Address
    {
        return $this->getProposal()->getWaypoints()[0]->getAddress();
    }

    /**
     * @var Address|null Destination for the solidary record
     * @Groups({"aReadCol", "aReadItem"})
     */
    public function getAdmindestination(): ?Address
    {
        foreach ($this->getProposal()->getWaypoints() as $waypoint) {
            if ($waypoint->isDestination()) {
                return $waypoint->getAddress();
            }
        }
        return null;
    }

    /**
     * @var string Proposal type for the solidary record
     * @Groups({"aReadCol","aReadItem"})
     */
    public function getAdminproposalType(): string
    {
        return $this->getProposal()->getType() == Proposal::TYPE_ONE_WAY ? 'oneway' : 'roundtrip';
    }

    /**
     * @var int Original frequency for the solidary record
     * @Groups("aReadItem")
     */
    public function getAdminfrequency(): int
    {
        if ($this->frequency == $this->getProposal()->getCriteria()->getFrequency()) {
            // original and proposal frequency are the same
            return $this->getProposal()->getCriteria()->getFrequency();
        } else {
            // original frequency is punctual, but proposal frequency is regular => flexible
            return Criteria::FREQUENCY_FLEXIBLE;
        }
    }

    /**
     * @var DateTime Start date for the solidary record
     * @Groups({"aReadItem", "aReadCol"})
     */
    public function getAdminfromDate(): ?DateTime
    {
        return $this->getProposal()->getCriteria()->getFromDate();
    }

    /**
     * @var DateTime|null End date for the solidary record
     * @Groups("aReadItem")
     */
    public function getAdmintoDate(): ?DateTime
    {
        return $this->getProposal()->getCriteria()->getToDate();
    }

    /**
     * @var DateTime Outward date for the solidary record, if punctual
     * @Groups("aReadItem")
     */
    public function getAdminoutwardDate(): ?DateTime
    {
        return $this->getProposal()->getCriteria()->getFromDate();
    }

    /**
     * @var DateTime|null Return date for the solidary record, if punctual
     * @Groups("aReadItem")
     */
    public function getAdminreturnDate(): ?DateTime
    {
        return $this->getProposal()->getProposalLinked() ? $this->getProposal()->getProposalLinked()->getCriteria()->getFromDate() : null;
    }

    /**
     * @var DateTime Outward time for the solidary record, if punctual
     * @Groups("aReadItem")
     */
    private $adminoutwardTime;
    public function getAdminoutwardTime(): ?DateTime
    {
        return $this->adminoutwardTime;
    }
    public function setAdminoutwardTime(DateTime $adminoutwardTime): self
    {
        $this->adminoutwardTime = $adminoutwardTime;

        return $this;
    }

    /**
     * @var DateTime|null Return time for the solidary record, if punctual
     * @Groups("aReadItem")
     */
    private $adminreturnTime;
    public function getAdminreturnTime(): ?DateTime
    {
        return $this->adminreturnTime;
    }
    public function setAdminreturnTime(DateTime $adminreturnTime): self
    {
        $this->adminreturnTime = $adminreturnTime;

        return $this;
    }

    /**
     * @var DateTime Outward min time for the solidary record, if flexible
     * @Groups("aReadItem")
     */
    private $adminoutwardMinTime;
    public function getAdminoutwardMinTime(): ?DateTime
    {
        return $this->adminoutwardMinTime;
    }
    public function setAdminoutwardMinTime(DateTime $adminoutwardMinTime): self
    {
        $this->adminoutwardMinTime = $adminoutwardMinTime;

        return $this;
    }

    /**
     * @var DateTime Outward max time for the solidary record, if flexible
     * @Groups("aReadItem")
     */
    private $adminoutwardMaxTime;
    public function getAdminoutwardMaxTime(): ?DateTime
    {
        return $this->adminoutwardMaxTime;
    }
    public function setAdminoutwardMaxTime(DateTime $adminoutwardMaxTime): self
    {
        $this->adminoutwardMaxTime = $adminoutwardMaxTime;

        return $this;
    }

    /**
     * @var DateTime Return min time for the solidary record, if flexible
     * @Groups("aReadItem")
     */
    private $adminreturnMinTime;
    public function getAdminreturnMinTime(): ?DateTime
    {
        return $this->adminreturnMinTime;
    }
    public function setAdminreturnMinTime(DateTime $adminreturnMinTime): self
    {
        $this->adminreturnMinTime = $adminreturnMinTime;

        return $this;
    }

    /**
     * @var DateTime Return max time for the solidary record, if flexible
     * @Groups("aReadItem")
     */
    private $adminreturnMaxTime;
    public function getAdminreturnMaxTime(): ?DateTime
    {
        return $this->adminreturnMaxTime;
    }
    public function setAdminreturnMaxTime(DateTime $adminreturnMaxTime): self
    {
        $this->adminreturnMaxTime = $adminreturnMaxTime;

        return $this;
    }

    /**
     * @var array Regular schedule for the solidary record
     * @Groups("aReadItem")
     */
    private $adminschedules;
    public function getAdminschedules(): ?array
    {
        return $this->adminschedules;
    }
    public function setAdminschedules(array $adminschedules): self
    {
        $this->adminschedules = $adminschedules;

        return $this;
    }

    /**
     * @var DateTime|null Creation date for the solidary record
     * @Groups("aReadItem")
     */
    public function getAdmincreatedDate(): ?DateTime
    {
        return $this->getCreatedDate();
    }

    /**
     * @var array Diary entries for the solidary record
     * @Groups("aReadItem")
     */
    private $admindiary;
    public function getAdmindiary(): array
    {
        return $this->admindiary;
    }
    public function setAdmindiary(array $admindiary): self
    {
        $this->admindiary = $admindiary;

        return $this;
    }

    /**
     * @var array Carpools for the solidary record
     * @Groups("aReadItem")
     */
    private $admincarpools;
    public function getAdmincarpools(): array
    {
        return $this->admincarpools;
    }
    public function setAdmincarpools(array $admincarpools): self
    {
        $this->admincarpools = $admincarpools;

        return $this;
    }

    /**
     * @var array Transporters for the solidary record
     * @Groups("aReadItem")
     */
    private $admintransporters;
    public function getAdmintransporters(): array
    {
        return $this->admintransporters;
    }
    public function setAdmintransporters(array $admintransporters): self
    {
        $this->admintransporters = $admintransporters;

        return $this;
    }

    /**
     * @var array Solutions for the solidary record
     * @Groups("aReadItem")
     */
    private $adminsolutions;
    public function getAdminsolutions(): array
    {
        return $this->adminsolutions;
    }
    public function setAdminsolutions(array $adminsolutions): self
    {
        $this->adminsolutions = $adminsolutions;

        return $this;
    }

    /**
     * @var array Proofs for the solidary record
     * @Groups("aReadItem")
     */
    private $adminproofs;
    public function getAdminproofs(): ?array
    {
        return $this->adminproofs;
    }
    public function setAdminproofs(array $adminproofs): self
    {
        $this->adminproofs = $adminproofs;

        return $this;
    }
}
