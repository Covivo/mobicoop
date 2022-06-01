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
 */

namespace App\Match\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Community\Entity\Community;
use App\Geography\Entity\Address;
use App\Match\Controller\CreateMassImportAction;
use App\Match\Controller\MassAnalyzeAction;
use App\Match\Controller\MassComputeAction;
use App\Match\Controller\MassMatchAction;
use App\Match\Controller\MassReAnalyzeAction;
use App\Match\Controller\MassReMatchAction;
use App\Match\Controller\MassWorkingPlacesAction;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * A mass matching file import.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","mass"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write","massPost","massMigrate"}},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('mass_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/masses",
 *              "deserialize"=false,
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=CreateMassImportAction::class,
 *              "defaults"={"_api_receive"=false},
 *              "security_post_denormalize"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"mass"}},
 *              "security"="is_granted('mass_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('mass_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "analyze"={
 *              "method"="GET",
 *              "path"="/masses/{id}/analyze",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=MassAnalyzeAction::class,
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "reanalyze"={
 *              "method"="GET",
 *              "path"="/masses/{id}/reanalyze",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=MassReAnalyzeAction::class,
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "compute"={
 *              "method"="GET",
 *              "path"="/masses/{id}/compute",
 *              "normalization_context"={"groups"={"massCompute"}},
 *              "controller"=MassComputeAction::class,
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "match"={
 *              "method"="GET",
 *              "path"="/masses/{id}/match",
 *              "normalization_context"={"groups"={"massMatch"}},
 *              "controller"=MassMatchAction::class,
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context"={
 *                  "tags"={"Mobimatch"},
 *                  "parameters"={
 *                     {
 *                         "name" = "maxDetourDurationPercent",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour duration percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "maxDetourDistancePercent",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour distance percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "minOverlapRatio",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "float",
 *                         "description" = "The minimum overlap ratio between bouding boxes to try a match (default:0)"
 *                     },
 *                     {
 *                         "name" = "maxSuperiorDistanceRatio",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum superior distance ratio between A and B to try a match (default:1000)"
 *                     },
 *                     {
 *                         "name" = "bearingCheck",
 *                         "in" = "query",
 *                         "type" = "boolean",
 *                         "description" = "Check the bearings (default:true)"
 *                     },
 *                     {
 *                         "name" = "bearingRange",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The bearing range in degrees if check bearings (default:10)"
 *                     }
 *                   }
 *              }
 *          },
 *          "rematch"={
 *              "method"="GET",
 *              "path"="/masses/{id}/rematch",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=MassReMatchAction::class,
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context"={
 *                  "tags"={"Mobimatch"},
 *                  "parameters"={
 *                     {
 *                         "name" = "maxDetourDurationPercent",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour duration percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "maxDetourDistancePercent",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour distance percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "minOverlapRatio",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "float",
 *                         "description" = "The minimum overlap ratio between bouding boxes to try a match (default:0)"
 *                     },
 *                     {
 *                         "name" = "maxSuperiorDistanceRatio",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum superior distance ratio between A and B to try a match (default:1000)"
 *                     },
 *                     {
 *                         "name" = "bearingCheck",
 *                         "in" = "query",
 *                         "type" = "boolean",
 *                         "description" = "Check the bearings (default:true)"
 *                     },
 *                     {
 *                         "name" = "bearingRange",
 *                         "in" = "query",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The bearing range in degrees if check bearings (default:10)"
 *                     }
 *                   }
 *              }
 *          },
 *          "workingplaces"={
 *              "method"="GET",
 *              "path"="/masses/{id}/workingplaces",
 *              "normalization_context"={"groups"={"mass","massWorkingPlaces"}},
 *              "controller"=MassWorkingPlacesAction::class,
 *              "security"="is_granted('mass_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "migrate"={
 *              "method"="PUT",
 *              "path"="/masses/{id}/migrate",
 *              "security"="is_granted('mass_create',object)",
 *              "normalization_context"={"groups"={"massMigrate"}},
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "getPTPotential"={
 *              "method"="GET",
 *              "path"="/masses/{id}/getPTPotential",
 *              "normalization_context"={"groups"={"mass","pt"}},
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          },
 *          "computePTPotential"={
 *              "method"="GET",
 *              "path"="/masses/{id}/computePTPotential",
 *              "normalization_context"={"groups"={"massPTPotential"}},
 *              "security"="is_granted('mass_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mobimatch"}
 *              }
 *          }
 *      }
 * )
 * @Vich\Uploadable
 */
class Mass
{
    public const STATUS_INCOMING = 0;
    public const STATUS_VALID = 1;
    public const STATUS_INVALID = 2;
    public const STATUS_ANALYZING = 3;
    public const STATUS_ANALYZED = 4;
    public const STATUS_MATCHING = 5;
    public const STATUS_MATCHED = 6;
    public const STATUS_ERROR = 7;
    public const STATUS_MIGRATING = 8;
    public const STATUS_MIGRATED = 9;

    public const TYPE_ANONYMOUS = 0;
    public const TYPE_QUALIFIED = 1;
    public const TYPE_MIGRATION = 2;

    public const NB_WORKING_DAY = 228;
    public const EARTH_CIRCUMFERENCE_IN_KILOMETERS = 40070; // Of course it's a joke... put away the forks and pikes ;)
    public const FLAT_EARTH_CIRCUMFERENCE_IN_MILES = 78186;
    public const AVERAGE_EARTH_MOON_DISTANCE_IN_KILOMETERS = 384400;
    public const PARIS_NEW_YORK_CO2_IN_GRAM = 875700; // For 1 passenger

    /**
     * @var int the id of this import
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost", "massAnalyze","massMatch", "massCompute", "massMigrate", "massPTPotential"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int the status of this import
     *
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost", "massCompute", "massMigrate", "massPTPotential"})
     */
    private $status;

    /**
     * @var string the final file name of the import
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost","write", "massCompute"})
     */
    private $fileName;

    /**
     * @var string the original file name of the import
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost","write", "massCompute"})
     */
    private $originalName;

    /**
     * @var int the size in bytes of the import
     *
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost","write", "massCompute"})
     */
    private $size;

    /**
     * @var string the mime type of the import
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $mimeType;

    /**
     * @var \DateTimeInterface creation date of the import
     *
     * @ORM\Column(type="datetime")
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $updatedDate;

    /**
     * @var User the user that imports the file
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="masses")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups("write")
     */
    private $user;

    /**
     * @var \DateTimeInterface analyzed date of the import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $analyzingDate;

    /**
     * @var \DateTimeInterface analyzing start date of the import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $analyzedDate;

    /**
     * @var \DateTimeInterface calculation start date of the import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $calculationDate;

    /**
     * @var \DateTimeInterface calculated date of the import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost", "massCompute"})
     */
    private $calculatedDate;

    /**
     * @var null|array the persons concerned by the file
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassPerson", mappedBy="mass", cascade={"persist"})
     * Groups({"massCompute"})
     */
    private $persons;

    /**
     * @var int Number of persons in this Mass
     * @Groups({"mass", "massCompute", "massPTPotential"})
     */
    private $numberOfPersons;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="mass", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     */
    private $file;

    /**
     * @var null|int the user id associated with the file
     * @Groups({"write"})
     */
    private $userId;

    /**
     * @var array the errors
     * @Groups({"mass","massPost"})
     */
    private $errors;

    /**
     * @var array the abberant addresses
     * @Groups({"massCompute"})
     */
    private $aberrantAddresses;

    /**
     * @var array people's coordinates of this mass
     * @Groups({"massCompute"})
     */
    private $personsCoords;

    /**
     * @var array Working Places of this Mass
     * @Groups({"massCompute", "massWorkingPlaces"})
     */
    private $workingPlaces;

    /**
     * @var array computed data of this mass
     * @Groups({"massCompute"})
     */
    private $computedData;

    /**
     * @var MassMatrix Matrix of carpools
     * @Groups({"massCompute"})
     */
    private $massMatrix;

    /**
     * @var int Type of Mass (0 : Anonymous, 1 : Qualified)
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mass","massPost", "massAnalyze","massMatch", "massCompute", "massMigrate"})
     */
    private $massType;

    /**
     * @var bool If the checkbox about the legitimity of the import has been checked
     * @Groups({"mass"})
     */
    private $checkLegit;

    /**
     * @var \DateTimeInterface The date of the legitimacy check
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"mass", "massMigrate"})
     */
    private $dateCheckLegit;

    /**
     * @var Community The community created after the migration of this mass users
     *
     * @ORM\ManyToOne(targetEntity="App\Community\Entity\Community", inversedBy="mass")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"mass","massMigrate"})
     */
    private $community;

    /**
     * @var null|array The migrated users
     * @Groups({"massMigrate"})
     */
    private $migratedUsers;

    /**
     * @var \DateTimeInterface date of migration's beginning
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass"})
     */
    private $migrationDate;

    /**
     * @var \DateTimeInterface date of migration's end
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass"})
     */
    private $migratedDate;

    /**
     * @var bool Set the first address as the home address of the users that will be migrated
     * @Groups({"mass","massMigrate"})
     */
    private $setHomeAddress;

    /**
     * @var int The id of an existing community. The migrated users will be joining this community.
     *          If there is a communityId, the other community fields (name, desc etc...) will be ignored
     * @Groups({"mass","massMigrate"})
     */
    private $communityId;

    /**
     * @var string The name of the new community that will be created if we migrate the users.
     *             All the migrated user will join this new community.
     * @Groups({"mass","massMigrate"})
     */
    private $communityName;

    /**
     * @var string the short description of the community
     * @Groups({"mass","massMigrate"})
     */
    private $communityDescription;

    /**
     * @var string the full description of the community
     * @Groups({"mass","massMigrate"})
     */
    private $communityFullDescription;

    /**
     * @var Address Address of the community
     * @Groups({"mass","massMigrate"})
     */
    private $communityAddress;

    /**
     * @var \DateTimeInterface Date of getting the public transportation information from external API
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass"})
     */
    private $gettingPublicTransportationPotentialDate;

    /**
     * @var \DateTimeInterface Date of getting the public transportation information from external API end
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass", "massPTPotential", "massAnalyze", "massMatch", "massCompute", "massMigrate"})
     */
    private $gotPublicTransportationPotentialDate;

    /**
     * @var array Potential of Public Transport of this Mass
     * @Groups({"massPTPotential"})
     */
    private $publicTransportPotential;

    public function __construct($id = null)
    {
        $this->id = $id;
        $this->errors = [];
        $this->persons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType)
    {
        $this->mimeType = $mimeType;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAnalyzingDate(): ?\DateTimeInterface
    {
        return $this->analyzingDate;
    }

    public function setAnalyzingDate(?\DateTimeInterface $analyzingDate): self
    {
        $this->analyzingDate = $analyzingDate;

        return $this;
    }

    public function getAnalyzedDate(): ?\DateTimeInterface
    {
        return $this->analyzedDate;
    }

    public function setAnalyzedDate(?\DateTimeInterface $analyzedDate): self
    {
        $this->analyzedDate = $analyzedDate;

        return $this;
    }

    public function getCalculationDate(): ?\DateTimeInterface
    {
        return $this->calculationDate;
    }

    public function setCalculationDate(?\DateTimeInterface $calculationDate): self
    {
        $this->calculationDate = $calculationDate;

        return $this;
    }

    public function getCalculatedDate(): ?\DateTimeInterface
    {
        return $this->calculatedDate;
    }

    public function setCalculatedDate(?\DateTimeInterface $calculatedDate): self
    {
        $this->calculatedDate = $calculatedDate;

        return $this;
    }

    public function getPersons()
    {
        return $this->persons->getValues();
    }

    public function addPerson(MassPerson $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons->add($person);
            $person->setMass($this);
        }

        return $this;
    }

    public function removeAddress(MassPerson $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getMass() === $this) {
                $person->setMass(null);
            }
        }

        return $this;
    }

    public function getNumberOfPersons(): ?int
    {
        return (!is_null($this->getPersons())) ? count($this->getPersons()) : 0;
    }

    public function setNumberOfPersons(?int $numberOfPersons): self
    {
        $this->numberOfPersons = $numberOfPersons;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file)
    {
        $this->file = $file;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function setErrors(?array $errors)
    {
        $this->errors = $errors;
    }

    public function getAberrantAddresses(): ?array
    {
        return $this->aberrantAddresses;
    }

    public function setAberrantAddresses(?array $aberrantAddresses)
    {
        $this->aberrantAddresses = $aberrantAddresses;
    }

    public function preventSerialization()
    {
        $this->setFile(null);
    }

    public function getPersonsCoords(): ?array
    {
        return $this->personsCoords;
    }

    public function setPersonsCoords(?array $personsCoords)
    {
        $this->personsCoords = $personsCoords;
    }

    public function getWorkingPlaces(): ?array
    {
        return $this->workingPlaces;
    }

    public function setWorkingPlaces(array $workingplaces): self
    {
        $this->workingPlaces = $workingplaces;

        return $this;
    }

    public function addWorkingPlaces(array $workingplace): self
    {
        if (!$this->workingPlaces->contains($workingplace)) {
            $this->workingPlaces->add($workingplace);
        }

        return $this;
    }

    public function setLonWorkingPlace(?float $lonWorkingPlace)
    {
        $this->lonWorkingPlace = $lonWorkingPlace;
    }

    public function getComputedData(): ?array
    {
        return $this->computedData;
    }

    public function setComputedData(?array $computedData)
    {
        $this->computedData = $computedData;
    }

    public function getMassMatrix(): ?MassMatrix
    {
        return $this->massMatrix;
    }

    public function setMassMatrix(?MassMatrix $massMatrix)
    {
        $this->massMatrix = $massMatrix;
    }

    public function getMassType(): ?int
    {
        return $this->massType;
    }

    public function setMassType(?int $massType)
    {
        $this->massType = $massType;
    }

    public function isCheckLegit(): ?bool
    {
        return $this->checkLegit;
    }

    public function setCheckLegit(?bool $checkLegit): self
    {
        $this->checkLegit = $checkLegit;

        return $this;
    }

    public function getDateCheckLegit(): ?\DateTimeInterface
    {
        return $this->dateCheckLegit;
    }

    public function setDateCheckLegit(?\DateTimeInterface $dateCheckLegit): self
    {
        $this->dateCheckLegit = $dateCheckLegit;

        return $this;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getMigratedUsers(): ?array
    {
        return $this->migratedUsers;
    }

    public function setMigratedUsers(?array $migratedUsers): self
    {
        $this->migratedUsers = $migratedUsers;

        return $this;
    }

    public function getMigrationDate(): ?\DateTimeInterface
    {
        return $this->migrationDate;
    }

    public function setMigrationDate(?\DateTimeInterface $migrationDate): self
    {
        $this->migrationDate = $migrationDate;

        return $this;
    }

    public function getMigratedDate(): ?\DateTimeInterface
    {
        return $this->migratedDate;
    }

    public function setMigratedDate(?\DateTimeInterface $migratedDate): self
    {
        $this->migratedDate = $migratedDate;

        return $this;
    }

    public function hasSetHomeAddress(): ?bool
    {
        return $this->setHomeAddress;
    }

    public function setSetHomeAddress(?bool $setHomeAddress)
    {
        $this->setHomeAddress = $setHomeAddress;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }

    public function setCommunityId(?int $communityId)
    {
        $this->communityId = $communityId;
    }

    public function getCommunityName(): ?string
    {
        return $this->communityName;
    }

    public function setCommunityName(?string $communityName)
    {
        $this->communityName = $communityName;
    }

    public function getCommunityDescription(): ?string
    {
        return $this->communityDescription;
    }

    public function setCommunityDescription(?string $communityDescription)
    {
        $this->communityDescription = $communityDescription;
    }

    public function getCommunityFullDescription(): ?string
    {
        return $this->communityFullDescription;
    }

    public function setCommunityFullDescription(?string $communityFullDescription)
    {
        $this->communityFullDescription = $communityFullDescription;
    }

    public function getCommunityAddress(): ?Address
    {
        return $this->communityAddress;
    }

    public function setCommunityAddress(?Address $communityAddress)
    {
        $this->communityAddress = $communityAddress;
    }

    public function getGettingPublicTransportationPotentialDate(): ?\DateTimeInterface
    {
        return $this->gettingPublicTransportationPotentialDate;
    }

    public function setGettingPublicTransportationPotentialDate(?\DateTimeInterface $gettingPublicTransportationPotentialDate): self
    {
        $this->gettingPublicTransportationPotentialDate = $gettingPublicTransportationPotentialDate;

        return $this;
    }

    public function getGotPublicTransportationPotentialDate(): ?\DateTimeInterface
    {
        return $this->gotPublicTransportationPotentialDate;
    }

    public function setGotPublicTransportationPotentialDate(?\DateTimeInterface $gotPublicTransportationPotentialDate): self
    {
        $this->gotPublicTransportationPotentialDate = $gotPublicTransportationPotentialDate;

        return $this;
    }

    public function getPublicTransportPotential(): ?array
    {
        return $this->publicTransportPotential;
    }

    public function setPublicTransportPotential(array $publicTransportPotential): self
    {
        $this->publicTransportPotential = $publicTransportPotential;

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
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
