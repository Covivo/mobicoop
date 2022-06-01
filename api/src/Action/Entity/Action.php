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

namespace App\Action\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Action\Filter\TypeFilter;
use App\Gamification\Entity\GamificationAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

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
 *              "security"="is_granted('action_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Misc"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('action_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Misc"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(TypeFilter::class, properties={"type"})
 */
class Action
{
    public const TYPE_AUTO = 0;
    public const TYPE_TAKING_ACCOUNT_ASK = 1;
    public const TYPE_SOLUTION_FINDING = 2;
    public const TYPE_FOLLOW_UP_CARPOOL = 3;
    public const TYPE_CLOSING_ASK = 4;
    public const TYPE_FREE = 5;

    public const SOLIDARY_CREATE = 37;

    public const DOMAIN_TYPE_SOLIDARY = 'solidary';

    public const TYPE_FILTER = [
        self::DOMAIN_TYPE_SOLIDARY => [1, 2, 3, 4, 5],
    ];

    public const TYPE_NAME = [
        self::TYPE_AUTO => 'Automatique',
        self::TYPE_TAKING_ACCOUNT_ASK => 'Prise en compte de la demande',
        self::TYPE_SOLUTION_FINDING => 'Recherche de solution',
        self::TYPE_FOLLOW_UP_CARPOOL => 'Suivi du covoiturage',
        self::TYPE_CLOSING_ASK => 'Clôture de la demande',
        self::TYPE_FREE => 'Action libre',
    ];

    public const ACTION_SOLIDARY_UPDATE_PROGRESS_MANUALLY = 39;

    /**
     * @var int the id of this action
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"read","aReadCol"})
     */
    private $id;

    /**
     * @var string name of the action
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","aReadCol","write","readUser"})
     */
    private $name;

    /**
     * @var int the type of this action
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","aReadCol"})
     */
    private $type;

    /**
     * @var string the name of the type of this action
     * @Groups("read")
     */
    private $typeName;

    /**
     * @var bool the action has to be logged in the log system
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $inLog;

    /**
     * @var bool the action has to be logged in the user action diary
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $inDiary;

    /**
     * @var null|int The progression if the action can be related to a process (like for solidary records). It's a numeric value, so it can be a percent, a step...
     *
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     * @Groups({"read","write","aReadCol"})
     */
    private $progression;

    /**
     * @var int position number in user preferences
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var null|ArrayCollection An Action can have multiple GamificationActions related
     *
     * @ORM\OneToMany(targetEntity="\App\Gamification\Entity\GamificationAction", mappedBy="action", cascade={"persist"})
     * @Groups({"readUser", "write"})
     * @MaxDepth(1)
     */
    private $gamificationActions;

    /**
     * @var \DateTimeInterface Creation date.
     *                         Nullable for now as actions are manually inserted.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
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
        if (null !== $this->getType()) {
            return self::TYPE_NAME[$this->getType()];
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

    public function getGamificationActions()
    {
        return $this->gamificationActions->getValues();
    }

    public function addGamificationAction(GamificationAction $gamificationAction): self
    {
        if (!$this->gamificationActions->contains($gamificationAction)) {
            $this->gamificationActions[] = $gamificationAction;
            $gamificationAction->getAction($this);
        }

        return $this;
    }

    public function removeGamificationAction(GamificationAction $gamificationAction): self
    {
        if ($this->gamificationActions->contains($gamificationAction)) {
            $this->gamificationActions->removeElement($gamificationAction);
        }

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
