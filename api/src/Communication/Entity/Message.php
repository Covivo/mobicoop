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
 */

namespace App\Communication\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Action\Entity\Log;
use App\Carpool\Entity\AskHistory;
use App\Solidary\Entity\SolidaryAskHistory;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A message sent from a user to other users.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security_post_denormalize"="is_granted('user_message_create',object)",
 *              "normalization_context"={"groups"={"sendMessage"}},
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          },
 *          "completeThread"={
 *              "method"="GET",
 *              "path"="/messages/completeThread",
 *              "normalization_context"={"groups"={"thread"}},
 *              "security_post_denormalize"="is_granted('user_message_read', object)",
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *           }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('user_message_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communication"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "createdDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"title":"partial"})
 */
class Message
{
    public const TYPE_DIRECT = 'Direct';
    public const TYPE_CARPOOL = 'Carpool';
    public const TYPE_SOLIDARY = 'Solidary';

    /**
     * @var int the id of this message
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread","sendMessage"})
     */
    private $id;

    /**
     * @var string the title of the message
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write","threads","thread"})
     */
    private $title;

    /**
     * @var string the text of the message
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write","threads","thread"})
     */
    private $text;

    /**
     * @var User the creator of the message
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write","threads","thread"})
     */
    private $user;

    /**
     * @var null|User the user who send the message in the name of the creator
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"read","write","threads","thread"})
     */
    private $userDelegate;

    /**
     * @var null|AskHistory the ask history item if the message is related to an ask
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\AskHistory", mappedBy="message")
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $askHistory;

    /**
     * @var null|SolidaryAskHistory the solidary ask history item if the message is related to an ask
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryAskHistory", mappedBy="message")
     * @Groups({"read","threads","thread"})
     * @MaxDepth(1)
     */
    private $solidaryAskHistory;

    /**
     * @var null|int Id of an Ask if this message is related to an Ask
     *
     * @Groups({"read","write","sendMessage"})
     */
    private $idAsk;

    /**
     * @var null|int Id of an ad if this message is a first contact in a carpool context (id of the ad we want to respond)
     *
     * @Groups({"read","write"})
     */
    private $idAdToRespond;

    /**
     * @var null|int Id of a proposal if this message is a first contact in a carpool context (id of the search)
     *
     * @Groups({"read","write"})
     */
    private $idProposal;

    /**
     * @var null|int Id of a matching if this message is a first contact in a carpool context
     *
     * @Groups({"read","write"})
     */
    private $idMatching;

    /**
     * @var null|int Id of a Solidary Ask if this message is related to a Solidary Ask
     *
     * @Groups({"read","write"})
     */
    private $idSolidaryAsk;

    /**
     * @var null|Message the original message if the message is a reply to another message
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Message", inversedBy="messages")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","write","sendMessage"})
     * @MaxDepth(1)
     */
    private $message;

    /**
     * @var ArrayCollection the recipients linked with the message
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Recipient", mappedBy="message", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $recipients;

    /**
     * @var ArrayCollection the messages linked with the message
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Message", mappedBy="message", cascade={"persist"})
     * @ORM\OrderBy({"createdDate" = "ASC"})
     * @Groups({"thread"})
     * @MaxDepth(1)
     */
    private $messages;

    /**
     * @var ArrayCollection the logs linked with the message
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="message")
     */
    private $logs;

    /**
     * @var null|Message The last message of a thread
     *
     * @Groups({"read","threads","sendMessage"})
     * @MaxDepth(1)
     */
    private $lastMessage;

    /**
     * @var \DateTimeInterface creation date of the message
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","threads","thread"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the message
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","threads","thread"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->messages = new ArrayCollection();
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

    public function getUserDelegate(): ?User
    {
        return $this->userDelegate;
    }

    public function setUserDelegate(?User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;

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

    public function getSolidaryAskHistory(): ?SolidaryAskHistory
    {
        return $this->solidaryAskHistory;
    }

    public function setSolidaryAskHistory(?SolidaryAskHistory $solidaryAskHistory): self
    {
        $this->solidaryAskHistory = $solidaryAskHistory;

        return $this;
    }

    public function getIdAsk(): ?int
    {
        if (!is_null($this->getAskHistory())) {
            return $this->getAskHistory()->getAsk()->getId();
        }

        return $this->idAsk;
    }

    public function setIdAsk(?int $idAsk): self
    {
        $this->idAsk = $idAsk;

        return $this;
    }

    public function getIdAdToRespond(): ?int
    {
        return $this->idAdToRespond;
    }

    public function setIdAdToRespond(?int $idAdToRespond): self
    {
        $this->idAdToRespond = $idAdToRespond;

        return $this;
    }

    public function getIdProposal(): ?int
    {
        return $this->idProposal;
    }

    public function setIdProposal(?int $idProposal): self
    {
        $this->idProposal = $idProposal;

        return $this;
    }

    public function getIdMatching(): ?int
    {
        return $this->idMatching;
    }

    public function setIdMatching(?int $idMatching): self
    {
        $this->idMatching = $idMatching;

        return $this;
    }

    public function getIdSolidaryAsk(): ?int
    {
        if (!is_null($this->getSolidaryAskHistory())) {
            return $this->getSolidaryAskHistory()->getSolidaryAsk()->getId();
        }

        return $this->idSolidaryAsk;
    }

    public function setIdSolidaryAsk(?int $idSolidaryAsk): self
    {
        $this->idSolidaryAsk = $idSolidaryAsk;

        return $this;
    }

    public function getMessage(): ?self
    {
        return $this->message;
    }

    public function setMessage(?self $message): self
    {
        $this->message = $message;

        // // set (or unset) the owning side of the relation if necessary
        // $newMessage = $message === null ? null : $this;
        // if ($newMessage !== $message->getMessage()) {
        //     $message->setMessage($newMessage);
        // }

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

    public function getMessages()
    {
        return $this->messages->getValues();
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setMessage($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getMessage() === $this) {
                $message->setMessage(null);
            }
        }

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
            $log->setMessage($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getMessage() === $this) {
                $log->setMessage(null);
            }
        }

        return $this;
    }

    public function getLastMessage(): ?self
    {
        return (count($this->messages) > 0) ? $this->messages[count($this->messages) - 1] : null;
    }

    public function setLastMessage(?self $lastMessage): self
    {
        $this->lastMessage = $lastMessage;

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
