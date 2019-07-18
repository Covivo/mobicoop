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
use App\User\Entity\User;
use App\Carpool\Entity\AskHistory;
use App\Communication\Controller\MessageCompleteThreadAction;
use App\Communication\Controller\PostMessageAction;

/**
 * A message sent from a user to other users.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "fetchEager": false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="false"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post" ={
 *              "path"="/messages",
 *              "controller"=PostMessageAction::class
 *          }
 *      },
 *      itemOperations={"get","put","delete",
 *          "completeThread"={
 *              "method"="GET",
 *              "path"="/messages/{id}/completeThread",
 *              "normalization_context"={"groups"={"completeThread"}},
 *              "controller"=MessageCompleteThreadAction::class
 *           }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"title":"partial"})
 */
class Message
{

    /**
     * @var int The id of this message.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","completeThread"})
     */
    private $id;

    /**
     * @var string The title of the message.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write","threads","completeThread"})
     */
    private $title;

    /**
     * @var string The text of the message.
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write","threads","completeThread"})
     */
    private $text;

    /**
     * @var User The creator of the message.
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write","threads","completeThread"})
     */
    private $user;

    /**
     * @var AskHistory|null The ask history item if the message is related to an ask.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\AskHistory", mappedBy="message")
     * @Groups({"read","write","threads","completeThread"})
     * @MaxDepth(1)
     */
    private $askHistory;

    /**
     * @var Message|null The original message if the message is a reply to another message.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Message")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $message;

    /**
     * @var ArrayCollection The recipients linked with the message.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Recipient", mappedBy="message", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({"read","write","threads","completeThread"})
     * @MaxDepth(1)
     */
    private $recipients;

    /**
     * @var \DateTimeInterface Creation date of the message.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","threads","completeThread"})
     */
    private $createdDate;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
    
    public function setText(?string $text): self
    {
        $this->text = $text;
        
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

    public function getAskHistory(): ?AskHistory
    {
        return $this->askHistory;
    }

    public function setAskHistory(?AskHistory $askHistory): self
    {
        $this->askHistory = $askHistory;

        return $this;
    }

    public function getMessage(): ?self
    {
        return $this->message;
    }

    public function setMessage(?self $message): self
    {
        $this->message = $message;

        // set (or unset) the owning side of the relation if necessary
        $newMessage = $message === null ? null : $this;
        if ($newMessage !== $message->getMessage()) {
            $message->setMessage($newMessage);
        }

        return $this;
    }

    public function getRecipients()
    {
        return $this->recipients->getValues();
    }
    
    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setMessage($this);
        }
        
        return $this;
    }
    
    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            // set the owning side to null (unless already changed)
            if ($recipient->getMessage() === $this) {
                $recipient->setMessage(null);
            }
        }
        
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
}
