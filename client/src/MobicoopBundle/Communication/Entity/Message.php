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


namespace Mobicoop\Bundle\MobicoopBundle\Communication\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\AskHistory;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 *  A message.
 */
class Message implements ResourceInterface
{
    /**
     * @var int The id of this message.
     *
     * @Groups({"put","post","completeThread"})
     */
    private $id;

    /**
     * @var string|null The iri of this message.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $iri;

    /**
     * @var string The title of the message.
     *
     * @Groups({"put","post","completeThread"})
     */
    private $title;

    /**
     * @var string The text of the message.
     *
     * @Groups({"put","post","completeThread"})
     */
    private $text;

    /**
     * @var User The creator of the message.
     *
     * @Groups({"put","post","get"})
     */
    private $user;

    /**
     * @var AskHistory|null The ask history item if the message is related to an ask.
     *
     * @Groups({"put","post","get","completeThread"})
     */
    private $askHistory;

    /**
     * @var int|null Id of an Ask if this message is related to an Ask
     *
     * @Groups({"post"})
     */
    private $idAsk;

    /**
     * @var int|null Id of an ad if this message is a first contact in a carpool context (id of the ad we want to respond)
     *
     * @Groups({"post"})
     */
    private $idAdToRespond;

    /**
     * @var int|null Id of a proposal if this message is a first contact in a carpool context (id of the search)
     *
     * @Groups({"post"})
     */
    private $idProposal;
    
    /**
     * @var int|null Id of a matching if this message is a first contact in a carpool context
     *
     * @Groups({"post"})
     */
    private $idMatching;
    
    /**
     * @var Message|null The original message if the message is a reply to another message.
     *
     * @Groups({"put","post","get"})
     */
    private $message;

    /**
     * @var ArrayCollection The recipients linked with the message.
     *
     * @Groups({"put","post","get"})
     */
    private $recipients;

    /**
     * @var \DateTimeInterface Creation date of the message.
     * @Groups({"put","post","get"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the message.
     * @Groups({"put","post","get"})
     */
    private $updatedDate;
    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/messages/".$id);
        }
        $this->recipients = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getText()
    {
        return $this->text;
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getAskHistory()
    {
        return $this->askHistory;
    }

    public function setAskHistory($askHistory)
    {
        $this->askHistory = $askHistory;
    }

    public function getIdAsk()
    {
        return $this->idAsk;
    }

    public function setIdAsk($idAsk)
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

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        // Is it useful in the bundle ????
        // set (or unset) the owning side of the relation if necessary
        // $newMessage = $message === null ? null : $this;
        // if ($newMessage !== $message->getMessage()) {
        //     $message->setMessage($newMessage);
        // }
    }

    public function getRecipients()
    {
        return $this->recipients->getValues();
    }
    
    public function addRecipient($recipient)
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            //$recipient->setMessage($this);
        }
    }
    
    public function removeRecipient($recipient)
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            
            // Is it useful in the bundle ????
            // // set the owning side to null (unless already changed)
            // if ($recipient->getMessage() === $this) {
            //     $recipient->setMessage(null);
            // }
        }
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
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
}
