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
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 *  A recipient.
 */
class Recipient implements ResourceInterface
{
    const STATUS_PENDING = 1;
    const STATUS_READ = 2;

    /**
     * @var int The id of this recipient.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $id;

    /**
     * @var string|null The iri of this recipient.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $iri;

    /**
     * @var int The status of the recipient.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $status;

    /**
     * @var User The recipient user of the message.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $user;

    /**
     * @var Message The message.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $message;

    /**
     * @var \DateTimeInterface Sent date of the message.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $sentDate;

    /**
     * @var \DateTimeInterface Read date of the message.
     *
     * @Groups({"post","put","completeThread"})
     */
    private $readDate;
    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
        }
        $this->notifieds = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIri(): ?string
    {
        return $this->iri;
    }
    
    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
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

    public function getMessage(): ?Message
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
}
