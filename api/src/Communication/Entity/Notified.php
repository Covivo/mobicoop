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
use App\User\Entity\User;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Proposal;
use App\Community\Entity\Community;

/**
 * A notification to send for a user.
 *
 * @ORM\Entity()
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
     * @var Notification The notification.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Notification")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $notification;

    /**
     * @var User The user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="notifieds")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date of the notification.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the notification.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface Sent date of the notification.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $sentDate;

    /**
     * @var \DateTimeInterface Received date of the notification.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $receivedDate;

    /**
     * @var \DateTimeInterface Read date of the notification.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $readDate;

    /**
     * @var Proposal The proposal if the notified is linked to a proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $proposal;

    /**
     * @var Community The community if the notified is linked to a community.
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $community;

    /**
     * @var Matching The matching if the notified is linked to a matching.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $matching;

    /**
     * @var AskHistory The askHistory if the notified is linked to an askHistory.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\AskHistory", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $askHistory;

    /**
     * @var Recipient The recipient if the notified is linked to a recipient.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Recipient", inversedBy="notifieds")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $recipient;

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

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }

    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;

        return $this;
    }

    public function getAskHistory(): ?AskHistory
    {
        return $this->askHistory;
    }

    public function setAskHistory(?AskHistory $askHistory): self
    {
        $this->askHistory = $askHistory;

        return $this;
    }

    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(?Recipient $recipient): self
    {
        $this->recipient = $recipient;

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
