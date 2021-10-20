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

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Communication\Entity\Notification;

/**
 * User notification preferences.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserNotification
{
    /**
     * @var int $id The id of this user notification preference.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var Notification The notification involved.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Notification")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notification;
        
    /**
     * @var User The user related with the notification.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="userNotifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var bool The status of the notification (active/inactive).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $active;

    /**
     * @var \DateTimeInterface Creation date of the user notification.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the user notification.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $updatedDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }
    
    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;
        
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

    public function isActive(): ?bool
    {
        return $this->active;
    }
    
    public function setActive(?bool $isActive): self
    {
        $this->active = $isActive;
        
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
