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

namespace App\Import\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\User\Entity\User;
use App\Import\Controller\ImportImageUserController;

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
 *              "method"="GET",
 *              "path"="/user_imports/treat",
 *              "normalization_context"={"groups"={"read"}},
 *          },
 *          "match"={
 *              "method"="GET",
 *              "path"="/user_imports/match",
 *              "normalization_context"={"groups"={"read"}},
 *          },
 *          "import-users-from-v1"={
 *              "method"="GET",
 *              "path"="/import/images-from-v1/users",
 *              "controller"=ImportImageUserController::class,
 *          },
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 *
 */
class UserImport
{
    const DEFAULT_ID = 999999999999;

    const STATUS_IMPORTED = 0;          // the external user has been imported, no treatment has been made yet
    const STATUS_USER_PENDING = 1;      // the user treatment is pending
    const STATUS_USER_TREATED = 2;      // the user treatment has been made successfully
    const STATUS_USER_ERROR = 3;        // the user treatment has failed
    const STATUS_DIRECTION_PENDING = 5; // the direction treatment is pending
    const STATUS_DIRECTION_TREATED = 6; // the direction treatment has been made successfully
    const STATUS_DIRECTION_ERROR = 7;   // the direction treatment failed
    const STATUS_DEFAULTS_PENDING = 8;  // the defaults treatment is pending
    const STATUS_DEFAULTS_TREATED = 9;  // the defaults treatment has been made successfully
    const STATUS_DEFAULTS_ERROR = 10;   // the defaults treatment has failed
    const STATUS_MATCHING_PENDING = 11; // the matching treatment is pending
    const STATUS_MATCHING_TREATED = 12; // the matching treatment has been made successfully
    const STATUS_MATCHING_ERROR = 13;   // the matching treatment has failed

    /**
     * @var int The id of this imported user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var User|null User imported in the platform.
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var string|null The identifier of the external system.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $origin;

    /**
     * @var int Import status.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Creation date of the user import.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Update date of the user import.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface Start date of the user treatment.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentUserStartDate;

    /**
     * @var \DateTimeInterface End date of the user treatment.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentUserEndDate;

    /**
     * @var \DateTimeInterface Start date of the journey treatment.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentJourneyStartDate;

    /**
     * @var \DateTimeInterface End date of the journey treatment.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $treatmentJourneyEndDate;

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
