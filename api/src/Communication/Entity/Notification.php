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
 */

namespace App\Communication\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Action\Entity\Action;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A message sent by the system for an action and a medium.
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Notifications"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Notifications"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Notifications"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Notifications"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Notifications"}
 *              }
 *          }
 *      }
 * )
 * ApiFilter(OrderFilter::class, properties={"id", "title"}, arguments={"orderParameterName"="order"})
 * ApiFilter(SearchFilter::class, properties={"title":"partial"})
 */
class Notification
{
    public const USER_REGISTERED_DELEGATE_PASSWORD_SEND_SMS = 65;
    public const PERMISSIVES = [
        self::USER_REGISTERED_DELEGATE_PASSWORD_SEND_SMS,
    ];
    public const CONFIRMED_CARPOOLER = 144;
    public const SEND_BOOSTER = 150;
    public const SOLIDARY_VOLUNTEER_MATCHING_SUCCESS = 178;

    /**
     * @var int the id of this notification
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups("read")
     */
    private $id;

    /**
     * @var string the template file for the title of the notification
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $templateTitle;

    /**
     * @var string the template file for the body of the notification
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $templateBody;

    /**
     * @var bool the status of the notification (active/inactive)
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $active;

    /**
     * @var bool the alternative template of the notification (active/inactive)
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $alt;

    /**
     * @var bool the default status of the notification (active/inactive) for each user
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $userActiveDefault;

    /**
     * @var bool the notification is editable by the user
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"read","write"})
     */
    private $userEditable;

    /**
     * @var int position number in user preferences
     *
     * @ORM\Column(type="smallint")
     *
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var Action the action
     *
     * @ORM\ManyToOne(targetEntity="\App\Action\Entity\Action")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"read","write"})
     *
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var Medium the medium
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Medium")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"read","write"})
     *
     * @MaxDepth(1)
     */
    private $medium;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"read"})
     */
    private $updatedDate;

    /**
     * @var int the max limit of the notification per day
     *
     * @ORM\Column(type="smallint", options={"default": 25})
     *
     * @Groups({"read","write"})
     */
    private $maxEmmittedPerDay;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplateTitle(): ?string
    {
        return $this->templateTitle;
    }

    public function setTemplateTitle(?string $templateTitle): self
    {
        $this->templateTitle = $templateTitle;

        return $this;
    }

    public function getTemplateBody(): ?string
    {
        return $this->templateBody;
    }

    public function setTemplateBody(?string $templateBody): self
    {
        $this->templateBody = $templateBody;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $isActive): self
    {
        $this->active = $isActive;

        return $this;
    }

    public function hasAlt(): ?bool
    {
        return $this->alt;
    }

    public function setAlt(?bool $isAlt): self
    {
        $this->alt = $isAlt;

        return $this;
    }

    public function isUserActiveDefault(): ?bool
    {
        return $this->userActiveDefault;
    }

    public function setUserActiveDefault(?bool $isUserActiveDefault): self
    {
        $this->userActiveDefault = $isUserActiveDefault;

        return $this;
    }

    public function isUserEditable(): ?bool
    {
        return $this->userEditable;
    }

    public function setUserEditable(?bool $isUserEditable): self
    {
        $this->userEditable = $isUserEditable;

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

    public function getAction(): Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMedium(): Medium
    {
        return $this->medium;
    }

    public function setMedium(?Medium $medium): self
    {
        $this->medium = $medium;

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

    public function isPermissive(): bool
    {
        return in_array($this->getId(), self::PERMISSIVES);
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

    public function getMaxEmmittedPerDay(): ?int
    {
        return $this->maxEmmittedPerDay;
    }

    public function setMaxEmmittedPerDay(int $maxEmmittedPerDay): self
    {
        $this->maxEmmittedPerDay = $maxEmmittedPerDay;

        return $this;
    }
}
