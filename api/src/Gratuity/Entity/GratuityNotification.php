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

use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class GratuityNotification
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The Notification's id
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
     * @var User The User notified
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="gratuityCampaigns")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var GratuityCampaign The GratuityCampaign notified to the user
     *
     * @ORM\ManyToOne(targetEntity="\App\Gratuity\Entity\GratuityCampaign", inversedBy="notifications")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $gratuityCampaign;

    /**
     * @var \DateTimeInterface creation date of the Notification
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
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

    public function getGratuityCampaign(): ?GratuityCampaign
    {
        return $this->gratuityCampaign;
    }

    public function setGratuityCampaign(?GratuityCampaign $gratuityCampaign): self
    {
        $this->gratuityCampaign = $gratuityCampaign;

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
}
