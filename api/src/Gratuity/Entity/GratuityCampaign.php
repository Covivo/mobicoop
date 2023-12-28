<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Gratuity\Entity;

use App\Action\Entity\Log;
use App\Geography\Entity\Territory;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class GratuityCampaign
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The Campaign's id
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var User The User who created the Campaign
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="gratuityCampaigns")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var null|ArrayCollection the territories of this campaign (can be null, it means that this campaign apply everywhere)
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     */
    private $territories;

    /**
     * @var string Campaign's name. Mostly used for intern managment
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string Campaign's template. Related to a twig file
     *
     * @ORM\Column(type="text")
     */
    private $template;

    /**
     * @var string Campaign's status
     *
     * @ORM\Column(type="integer", length=255)
     */
    private $status;

    /**
     * @var \DateTimeInterface Campaign's start date
     *
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTimeInterface Campaign's end date
     *
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @var null|ArrayCollection The notifications sent for this campaign
     *
     * @ORM\OneToMany(targetEntity="\App\Gratuity\Entity\GratuityNotification", mappedBy="gratuityCampaign", cascade={"persist"})
     *
     * @MaxDepth(1)
     */
    private $notifications;

    /**
     * @var ArrayCollection the logs linked with the Campaign
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="gratuityCampaign")
     */
    private $logs;

    /**
     * @var \DateTimeInterface creation date of the user
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface validation date of the user
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->territories = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTerritories()
    {
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories[] = $territory;
        }

        return $this;
    }

    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }

        return $this;
    }

    public function getGratuityNotifications()
    {
        return $this->notifications->getValues();
    }

    public function addGratuityNotification(GratuityNotification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
        }

        return $this;
    }

    public function removeGratuityNotification(GratuityNotification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLogs()
    {
        return $this->logs->getValues();
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setGratuityCampaign($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getGratuityCampaign() === $this) {
                $log->setGratuityCampaign(null);
            }
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeInterface $updatedDate): self
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
