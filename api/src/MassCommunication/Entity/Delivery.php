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

namespace App\MassCommunication\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;

/**
 * A mass communication delivery related to a campaign.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read_campaign"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write_campaign"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          }
 *      }
 * )
 */
class Delivery
{
    public const STATUS_PENDING = 0;
    public const STATUS_SENT = 1;
    public const STATUS_ERROR = 2;

    /**
     * @var int The id of this delivery.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("read_campaign")
     */
    private $id;

    /**
     * @var bool The status of the delivery.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read_campaign","write_campaign"})
     */
    private $status;

    /**
     * @var Campaign The campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\Campaign")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     * @MaxDepth(1)
     */
    private $campaign;

    /**
     * @var User The user recipient.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="deliveries")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface Sent date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $sentDate;

    /**
     * @var \DateTimeInterface Received date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $receivedDate;

    /**
     * @var \DateTimeInterface Read date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $readDate;

    public function __construct()
    {
        if (is_null($this->status)) {
            $this->status = self::STATUS_PENDING;
        }
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;

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

    public function getSentDate(): ?\DateTimeInterface
    {
        return $this->sentDate;
    }

    public function setSentDate(\DateTimeInterface $sentDate): self
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    public function getReceivedDate(): ?\DateTimeInterface
    {
        return $this->receivedDate;
    }

    public function setRedeivedDate(\DateTimeInterface $receivedDate): self
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    public function getReadDate(): ?\DateTimeInterface
    {
        return $this->readDate;
    }

    public function setReadDate(\DateTimeInterface $readDate): self
    {
        $this->readDate = $readDate;

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
