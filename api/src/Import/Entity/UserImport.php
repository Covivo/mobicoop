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
 */

namespace App\Import\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Import\Controller\ImportImageUserController;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A user imported from an external system.
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
 *          "treat"={
 *              "method"="POST",
 *              "path"="/user_imports/treat",
 *              "normalization_context"={"groups"={"read"}},
 *              "security"="is_granted('import_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Import"}
 *              }
 *          },
 *          "import-users-from-v1"={
 *              "method"="GET",
 *              "path"="/import/images-from-v1/users",
 *              "controller"=ImportImageUserController::class,
 *              "read"=false,
 *              "security"="is_granted('import_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Import"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('import_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Import"}
 *              }
 *          },
 *      }
 * )
 */
class UserImport
{
    public const DEFAULT_ID = 999999999999;

    public const STATUS_IMPORTED = 0;          // the external user has been imported, no treatment has been made yet
    public const STATUS_USER_PENDING = 1;      // the user treatment is pending
    public const STATUS_USER_TREATED = 2;      // the user treatment has been made successfully
    public const STATUS_USER_ERROR = 3;        // the user treatment has failed
    public const STATUS_DIRECTION_PENDING = 5; // the direction treatment is pending
    public const STATUS_DIRECTION_TREATED = 6; // the direction treatment has been made successfully
    public const STATUS_DIRECTION_ERROR = 7;   // the direction treatment failed
    public const STATUS_DEFAULTS_PENDING = 8;  // the defaults treatment is pending
    public const STATUS_DEFAULTS_TREATED = 9;  // the defaults treatment has been made successfully
    public const STATUS_DEFAULTS_ERROR = 10;   // the defaults treatment has failed
    public const STATUS_MATCHING_PENDING = 11; // the matching treatment is pending
    public const STATUS_MATCHING_TREATED = 12; // the matching treatment has been made successfully
    public const STATUS_MATCHING_ERROR = 13;   // the matching treatment has failed

    /**
     * @var int the id of this imported user
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var null|User user imported in the platform
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var null|string the identifier of the external system
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $origin;

    /**
     * @var int import status
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var \DateTimeInterface creation date of the user import
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface update date of the user import
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface start date of the user treatment
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentUserStartDate;

    /**
     * @var \DateTimeInterface end date of the user treatment
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentUserEndDate;

    /**
     * @var \DateTimeInterface start date of the journey treatment
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentJourneyStartDate;

    /**
     * @var \DateTimeInterface end date of the journey treatment
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentJourneyEndDate;

    /**
     * @var null|string the user id in the external system
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $userExternalId;

    public function __construct($id = null, $status = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        if (is_null($status)) {
            $status = self::STATUS_IMPORTED;
        }
        $this->setStatus($status);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getTreatmentUserStartDate(): ?\DateTimeInterface
    {
        return $this->treatmentUserStartDate;
    }

    public function setTreatmentUserStartDate(\DateTimeInterface $treatmentUserStartDate): self
    {
        $this->treatmentUserStartDate = $treatmentUserStartDate;

        return $this;
    }

    public function getTreatmentUserEndDate(): ?\DateTimeInterface
    {
        return $this->treatmentUserEndDate;
    }

    public function setTreatmentUserEndDate(\DateTimeInterface $treatmentUserEndDate): self
    {
        $this->treatmentUserEndDate = $treatmentUserEndDate;

        return $this;
    }

    public function getTreatmentJourneyStartDate(): ?\DateTimeInterface
    {
        return $this->treatmentJourneyStartDate;
    }

    public function setTreatmentJourneyStartDate(\DateTimeInterface $treatmentJourneyStartDate): self
    {
        $this->treatmentJourneyStartDate = $treatmentJourneyStartDate;

        return $this;
    }

    public function getTreatmentJourneyEndDate(): ?\DateTimeInterface
    {
        return $this->treatmentJourneyEndDate;
    }

    public function setTreatmentJourneyEndDate(\DateTimeInterface $treatmentJourneyEndDate): self
    {
        $this->treatmentJourneyEndDate = $treatmentJourneyEndDate;

        return $this;
    }

    public function getUserExternalId(): string
    {
        return $this->userExternalId;
    }

    public function setUserExternalId(string $userExternalId): self
    {
        $this->userExternalId = $userExternalId;

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
