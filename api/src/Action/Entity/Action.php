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

namespace App\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Action\Filter\TypeFilter;

/**
 * An action that can be logged and / or trigger notifications.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read","readUser"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('action_list',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('action_read',object)"
 *          },
 *      }
 * )
 * @ApiFilter(TypeFilter::class, properties={"type"})
 */
class Action
{
    const TYPE_ACTION_AUTO = 0;
    const TYPE_ACTION_TAKING_ACCOUNT_ASK = 1;
    const TYPE_ACTION_SOLUTION_FINDING = 2;
    const TYPE_ACTION_FOLLOW_UP_CARPOOL = 3;
    const TYPE_ACTION_CLOSING_ASK = 4;

    const ACTION_TYPE_FILTER = [
        'solidary' => [1,2,3,4]
    ];

    const ACTION_TYPE_NAME = [
        self::TYPE_ACTION_AUTO => "Automatique",
        self::TYPE_ACTION_TAKING_ACCOUNT_ASK => "Prise en compte de la demande",
        self::TYPE_ACTION_SOLUTION_FINDING => "Recherche de solution",
        self::TYPE_ACTION_FOLLOW_UP_CARPOOL => "Suivi du covoiturage",
        self::TYPE_ACTION_CLOSING_ASK => "Clôture de la demande"
    ];

    /**
     * @var int The id of this action.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("read")
     */
    private $id;

    /**
     * @var string Name of the action.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write","readUser"})
     */
    private $name;

    /**
     * @var int The type of this action.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("read")
     */
    private $type;

    /**
     * @var string The name of the type of this action.
     * @Groups("read")
     */
    private $typeName;

    /**
     * @var bool The action has to be logged in the log system.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $inLog;

    /**
     * @var bool The action has to be logged in the user action diary.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $inDiary;

    /**
     * @var int|null The progression if the action can be related to a process (like for solidary records). It's a numeric value, so it can be a percent, a step...
     *
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     * @Groups({"read","write"})
     */
    private $progression;

    /**
     * @var int Position number in user preferences.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var \DateTimeInterface Creation date.
     * Nullable for now as actions are manually inserted.
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

    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
        return $this;
    }

    public function getTypeName(): ?string
    {
        if ($this->getType() !== null) {
            return self::ACTION_TYPE_NAME[$this->getType()];
        }
        return null;
    }
    
    public function setTypeName(string $typeName): self
    {
        $this->typeName = $typeName;
        
        return $this;
    }

    public function isInLog(): ?bool
    {
        return $this->inLog;
    }
    
    public function setInLog(bool $isInLog): self
    {
        $this->inLog = $isInLog;
        
        return $this;
    }

    public function isInDiary(): ?bool
    {
        return $this->inDiary;
    }
    
    public function setInDiary(bool $isInDiary): self
    {
        $this->inDiary = $isInDiary;
        
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

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
