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

namespace App\Travel\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel mode.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class TravelMode
{
    const TRAVEL_MODE_CAR = "CAR";
    const TRAVEL_MODE_BUS = "BUS";
    const TRAVEL_MODE_TRAMWAY = "TRAMWAY";
    const TRAVEL_MODE_COACH = "COACH";
    const TRAVEL_MODE_TRAIN = "TRAIN";
    const TRAVEL_MODE_TRAIN_LOCAL = "TRAIN_LOCAL";
    const TRAVEL_MODE_TRAIN_HIGH_SPEED = "TRAIN_HIGH_SPEED";
    const TRAVEL_MODE_BIKE = "BIKE";
    const TRAVEL_MODE_WALK = "WALK";
    const TRAVEL_MODE_SUBWAY = "SUBWAY";
    const TRAVEL_MODE_WAITING = "WAITING";
    const TRAVEL_UNKNOWN = "UNKNOWN";
    const TRAVEL_MODE_ON_DEMAND = "TOD";
    const TRAVEL_MODE_METRO = "METRO";
    const TRAVEL_MODE_TROLLEY_BUS = "TROLLEY_BUS";
	const TRAVEL_MODE_UNKNOWN = "UNKNOWN";

    private const TRAVEL_MODES = [
        self::TRAVEL_MODE_CAR => 1,
        self::TRAVEL_MODE_BUS => 2,
        self::TRAVEL_MODE_TRAMWAY => 3,
        self::TRAVEL_MODE_COACH => 4,
        self::TRAVEL_MODE_TRAIN => 5,
        self::TRAVEL_MODE_TRAIN_LOCAL => 6,
        self::TRAVEL_MODE_TRAIN_HIGH_SPEED => 7,
        self::TRAVEL_MODE_BIKE => 8,
        self::TRAVEL_MODE_WALK => 9,
        self::TRAVEL_MODE_SUBWAY => 10,
        self::TRAVEL_MODE_WAITING => 11,
        self::TRAVEL_MODE_ON_DEMAND => 12,
        self::TRAVEL_MODE_METRO => 13,
        self::TRAVEL_MODE_TROLLEY_BUS => 14,
		self::TRAVEL_MODE_UNKNOWN => 99

    ];

    private const TRAVEL_MODES_MDI_ICONS = [
        self::TRAVEL_MODE_CAR => "mdi-car",
        self::TRAVEL_MODE_BUS => "mdi-bus",
        self::TRAVEL_MODE_TRAMWAY => "mdi-tram",
        self::TRAVEL_MODE_COACH => "mdi-bus-side",
        self::TRAVEL_MODE_TRAIN => "mdi-train",
        self::TRAVEL_MODE_TRAIN_LOCAL => "mdi-train",
        self::TRAVEL_MODE_TRAIN_HIGH_SPEED => "mdi-train-variant",
        self::TRAVEL_MODE_BIKE => "mdi-bike",
        self::TRAVEL_MODE_WALK => "mdi-walk",
        self::TRAVEL_MODE_SUBWAY => "mdi-subway-variant",
        self::TRAVEL_MODE_WAITING => "mdi-account-clock",
        self::TRAVEL_MODE_ON_DEMAND => "mdi-bus-clock",
        self::TRAVEL_UNKNOWN => "mdi-help-circle",
        self::TRAVEL_MODE_METRO => "mdi-subway-variant",
        self::TRAVEL_MODE_TROLLEY_BUS => "mdi-bus",
		self::TRAVEL_MODE_UNKNOWN => "mdi-help-circle-outline"
    ];

    /**
     * @var int The id of this travel mode.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("read")
     */
    private $id;

    /**
     * @var string Name of the travel mode.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write","pt"})
     */
    private $name;

    /**
     * @var string The Material design icon code of this travel mode
     * @Groups({"read","pt"})
     */
    private $mdiIcon;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    public function __construct($mode)
    {
        $this->setId(self::TRAVEL_MODES[$mode]);
        $this->setName($mode);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMdiIcon(): ?string
    {
        return (isset(self::TRAVEL_MODES_MDI_ICONS[$this->getName()])) ? self::TRAVEL_MODES_MDI_ICONS[$this->getName()] : self::TRAVEL_MODES_MDI_ICONS['UNKNOWN'];
    }

    public function setMdiIcon(string $mdiIcon): self
    {
        $this->mdiIcon = $mdiIcon;

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
