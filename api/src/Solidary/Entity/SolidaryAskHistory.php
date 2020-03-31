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

namespace App\Solidary\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Communication\Entity\Message;
use App\Communication\Interfaces\MessagerInterface;
use App\Solidary\Entity\SolidaryAsk;
use App\Communication\Entity\Notified;

/**
 * Carpooling : a history item for an solidaryAsk
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class SolidaryAskHistory implements MessagerInterface
{
    const STATUS_INITIATED = 1;
    const STATUS_PENDING_AS_DRIVER = 2;
    const STATUS_PENDING_AS_PASSENGER = 3;
    const STATUS_ACCEPTED_AS_DRIVER = 4;
    const STATUS_ACCEPTED_AS_PASSENGER = 5;
    const STATUS_DECLINED_AS_DRIVER = 6;
    const STATUS_DECLINED_AS_PASSENGER = 7; // asked by remi
    
    /**
     * @var int The id of this ask history item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var int Ask status at the date of creation of the item (1 = initiated; 2 = pending, 3 = accepted; 4 = declined).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Creation date of the history item.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the history item.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var SolidaryAsk|null The linked solidary ask.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\SolidaryAsk", inversedBy="solidaryAskHistories")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $solidaryAsk;

    /**
     * @var Message|null The message linked the solidary ask history item.
     *
     * @ORM\OneToOne(targetEntity="\App\Communication\Entity\Message", inversedBy="solidaryAskHistory", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $message;

    public function __construct()
    {
        $this->notifieds = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSolidaryAsk(): ?SolidaryAsk
    {
        return $this->solidaryAsk;
    }

    public function setSolidaryAsk(?SolidaryAsk $solidaryAsk): self
    {
        $this->solidaryAsk = $solidaryAsk;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(Message $message): self
    {
        $this->message = $message;

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

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }
    
    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setAskHistory($this);
        }
        
        return $this;
    }
    
    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getAskHistory() === $this) {
                $notified->setAskHistory(null);
            }
        }
        
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
