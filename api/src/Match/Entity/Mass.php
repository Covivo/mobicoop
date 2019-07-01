<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Match\Controller\CreateMassImportAction;
use App\Match\Controller\MassAnalyzeAction;
use App\Match\Controller\MassMatchAction;
use App\Match\Controller\MassComputeAction;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * A mass matching file import.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","mass"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/masses",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=CreateMassImportAction::class,
 *              "defaults"={"_api_receive"=false},
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"mass"}},
 *          },
 *          "delete",
 *          "analyze"={
 *              "method"="GET",
 *              "path"="/masses/{id}/analyze",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=MassAnalyzeAction::class
 *          },
 *          "compute"={
 *              "method"="GET",
 *              "path"="/masses/{id}/compute",
 *              "normalization_context"={"groups"={"mass"}},
 *              "controller"=MassComputeAction::class
 *          },
 *          "match"={
 *              "method"="GET",
 *              "path"="/masses/{id}/match",
 *              "normalization_context"={"groups"={"massPost"}},
 *              "controller"=MassMatchAction::class,
 *              "swagger_context"={
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
 *      }
 * )
 * @Vich\Uploadable
 */
class Mass
{
    const STATUS_INCOMING = 0;
    const STATUS_VALID = 1;
    const STATUS_INVALID = 2;
    const STATUS_ANALYZING = 3;
    const STATUS_ANALYZED = 4;
    const STATUS_MATCHING = 5;
    const STATUS_MATCHED = 6;
    const STATUS_ERROR = 7;

    const NB_WORKING_DAY = 228;
    const EARTH_CIRCUMFERENCE_IN_KILOMETERS = 40070;
    const FLAT_EARTH_CIRCUMFERENCE_IN_MILES = 78186;
    const AVERAGE_EARTH_MOON_DISTANCE_IN_KILOMETERS = 384400;
    const PARIS_NEW_YORK_CO2_IN_GRAM = 875700; // For 1 passenger

    /**
     * @var int The id of this import.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost", "massAnalyze","massMatch"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int The status of this import.
     *
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost"})
     */
    private $status;

    /**
     * @var string The final file name of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost","write"})
     */
    private $fileName;

    /**
     * @var string The original file name of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost","write"})
     */
    private $originalName;

    /**
     * @var int The size in bytes of the import.
     *
     * @ORM\Column(type="integer")
     * @Groups({"mass","massPost","write"})
     */
    private $size;

    /**
     * @var string The mime type of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","massPost"})
     */
    private $mimeType;

    /**
     * @var \DateTimeInterface Creation date of the import.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"mass","massPost"})
     */
    private $createdDate;

    /**
     * @var User The user that imports the file.
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="masses")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("write")
     */
    private $user;

    /**
     * @var \DateTimeInterface Analyzed date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost"})
     */
    private $analyzingDate;

    /**
     * @var \DateTimeInterface Analyzing start date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost"})
     */
    private $analyzedDate;

    /**
     * @var \DateTimeInterface Calculation start date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost"})
     */
    private $calculationDate;

    /**
     * @var \DateTimeInterface Calculated date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mass","massPost"})
     */
    private $calculatedDate;

    /**
     * @var array|null The persons concerned by the file.
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassPerson", mappedBy="mass", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"mass"})
     */
    private $persons;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="mass", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     */
    private $file;

    /**
     * @var int|null The user id associated with the file.
     * @Groups({"write"})
     */
    private $userId;

    /**
     * @var array The errors.
     * @Groups({"mass","massPost"})
     */
    private $errors;

    /**
     * @var array people's coordinates of this mass.
     * @Groups({"mass"})
     */
    private $personsCoords;

    /**
     * @var float Working place latitude of the people of this mass.
     * @Groups({"mass"})
     */
    private $latWorkingPlace;

    /**
     * @var float Working place longitude of the people of this mass.
     * @Groups({"mass"})
     */
    private $lonWorkingPlace;

    /**
     * @var array Computed data of this mass.
     * @Groups({"mass"})
     */
    private $computedData;

    /**
     * @var MassMatrix Matrix of carpools
     * @Groups({"mass"})
     */
    private $massMatrix;

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

    public function preventSerialization()
    {
        $this->setFile(null);
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

    public function getPersonsCoords(): ?array
    {
        return $this->personsCoords;
    }

    public function setPersonsCoords(?array $personsCoords)
    {
        $this->personsCoords = $personsCoords;
    }

    public function getLatWorkingPlace(): ?float
    {
        return $this->latWorkingPlace;
    }

    public function setLatWorkingPlace(?float $latWorkingPlace)
    {
        $this->latWorkingPlace = $latWorkingPlace;
    }

    public function getLonWorkingPlace(): ?float
    {
        return $this->lonWorkingPlace;
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
}
