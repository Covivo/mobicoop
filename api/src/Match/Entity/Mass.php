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
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/masses",
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
 *              "normalization_context"={"groups"={"mass"}},
 *              "controller"=MassAnalyzeAction::class
 *          },
 *          "match"={
 *              "method"="GET",
 *              "path"="/masses/{id}/match",
 *              "normalization_context"={"groups"={"mass"}},
 *              "controller"=MassMatchAction::class,
 *              "swagger_context"={
 *                  "parameters"={
 *                     {
 *                         "name" = "maxDetourDurationPercent",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour duration percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "maxDetourDistancePercent",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum detour distance percent (default:40)"
 *                     },
 *                     {
 *                         "name" = "minOverlapRatio",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "number",
 *                         "format" = "float",
 *                         "description" = "The minimum overlap ratio between bouding boxes to try a match (default:0)"
 *                     },
 *                     {
 *                         "name" = "maxSuperiorDistanceRatio",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The maximum superior distance ratio between A and B to try a match (default:1000)"
 *                     },
 *                     {
 *                         "name" = "bearingCheck",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "boolean",
 *                         "description" = "Check the bearings (default:true)"
 *                     },
 *                     {
 *                         "name" = "bearingRange",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "number",
 *                         "format" = "integer",
 *                         "description" = "The bearing range in degrees if check bearings (default:10)"
 *                     },
 *                     {
 *                         "name" = "doubleCheck",
 *                         "in" = "query",
 *                         "required" = "false",
 *                         "type" = "boolean",
 *                         "description" = "Check if B as a driver matches for A as a passenger if A as a driver already matches with B as a passenger (default:false)"
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
    const STATUS_ANALYZED = 3;
    const STATUS_TREATED = 4;

    /**
     * @var int The id of this import.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("mass")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int The status of this import.
     *
     * @ORM\Column(type="integer")
     * @Groups("mass")
     */
    private $status;

    /**
     * @var string The final file name of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","write"})
     */
    private $fileName;

    /**
     * @var string The original file name of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"mass","write"})
     */
    private $originalName;

    /**
     * @var int The size in bytes of the import.
     *
     * @ORM\Column(type="integer")
     * @Groups({"mass","write"})
     */
    private $size;

    /**
     * @var string The mime type of the import.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("mass")
     */
    private $mimeType;

    /**
     * @var \DateTimeInterface Creation date of the import.
     *
     * @ORM\Column(type="datetime")
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
     * @var \DateTimeInterface Analyze date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("mass")
     */
    private $analyzeDate;

    /**
     * @var \DateTimeInterface Calculation date of the import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("mass")
     */
    private $calculationDate;

    /**
     * @var ArrayCollection|null The persons concerned by the file.
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\MassPerson", mappedBy="mass", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups("mass")
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
     * @Groups("mass")
     */
    private $errors;

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

    public function getAnalyzeDate(): ?\DateTimeInterface
    {
        return $this->analyzeDate;
    }

    public function setAnalyzeDate(?\DateTimeInterface $analyzeDate): self
    {
        $this->analyzeDate = $analyzeDate;

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
}
