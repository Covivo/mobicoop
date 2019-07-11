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
use App\User\Entity\User;

/**
 * A recipient of a message.
 *
 * @ORM\Entity()
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "status"}, arguments={"orderParameterName"="order"})
 */
class Recipient
{
    const STATUS_PENDING = 1;
    const STATUS_READ = 2;

    /**
     * @var int The id of this recipient.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int The status of the recipient.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads"})
     */
    private $status;

    /**
     * @var User The recipient user of the message.
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="recipients")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write","threads"})
     */
    private $user;

    /**
     * @var Message The message.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Message", inversedBy="recipients")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write","threads"})
     * @MaxDepth(1)
     */
    private $message;

    /**
     * @var \DateTimeInterface Sent date of the message.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write","threads"})
     */
    private $sentDate;

    /**
     * @var \DateTimeInterface Read date of the message.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write","threads"})
     */
    private $readDate;

    /**
     * @var ArrayCollection|null The notifications sent for the recipient.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="recipient", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notifieds;

    public function __construct()
    {
        $this->notifieds = new ArrayCollection();
    }

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

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

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

    public function getSentDate(): ?\DateTimeInterface
    {
        return $this->sentDate;
    }

    public function setSentDate(\DateTimeInterface $sentDate): self
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }
    
    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setRecipient($this);
        }
        
        return $this;
    }
    
    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getRecipient() === $this) {
                $notified->setRecipient(null);
            }
        }
        
        return $this;
    }
}
