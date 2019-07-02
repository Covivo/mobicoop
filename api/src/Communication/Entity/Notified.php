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

namespace App\Communication\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Communication\Entity\Notification;
use App\Communication\Entity\Medium;
use App\User\Entity\User;

/**
 * A notification to send for a user.
 *
 * @ORM\Entity()
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"title":"partial"})
 */
class Notified
{
    const STATUS_SENT = 1;
    const STATUS_RECEIVED = 2;
    const STATUS_READ = 3;

    /**
     * @var int The id of this notified.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var bool The status of the notified (sent/received/read).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var Notification|null The notification.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Notification")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notification;

    /**
     * @var Medium|null The medium.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Medium")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $medium;

    /**
     * @var User|null The user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Sent date of the message.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $sentDate;

    /**
     * @var \DateTimeInterface Received date of the message.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $receivedDate;

    /**
     * @var \DateTimeInterface Read date of the message.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $readDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(?int $status)
    {
        $this->status = $status;
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

    public function getNotification(): Notification
    {
        return $this->notification;
    }
    
    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;
        
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
}
