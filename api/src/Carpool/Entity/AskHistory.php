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

namespace App\Carpool\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Communication\Entity\Message;
use App\Communication\Entity\Notified;
use App\Communication\Interfaces\MessagerInterface;

/**
 * Carpooling : a history item for an ask (all the items represent a thread for the ask).
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 */
class AskHistory implements MessagerInterface
{
    /**
     * @var int The id of this ask history item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","thread"})
     */
    private $id;

    /**
     * @var int Ask status at the date of creation of the item (1 = initiated; 2 = pending as driver, 3 = pending as passenger, 4 = accepted as driver; 5 = accepted as passenger, 6 = declined as driver, 7 = declined as passenger).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $status;

    /**
     * @var int The ask type at the date of creation of the item (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $type;

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
     * @var Ask|null The linked ask.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="askHistories")
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $ask;

    /**
     * @var Message|null The message linked the ask history item.
     *
     * @ORM\OneToOne(targetEntity="\App\Communication\Entity\Message", inversedBy="askHistory", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $message;

    /**
     * @var ArrayCollection|null The notifications sent for the ask history.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="askHistory", cascade={"persist","remove"}, orphanRemoval=true)
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getAsk(): ?Ask
    {
        return $this->ask;
    }

    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;

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
