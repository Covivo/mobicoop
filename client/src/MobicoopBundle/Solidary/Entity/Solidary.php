<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Solidary\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Solidary implements ResourceInterface
{
    const ASKED = 0;
    const REFUSED = 1;
    const PENDING = 2;
    const LOOKINGFORSOLUTION = 3;
    const FOLLOWUP = 4;
    const CLOSED = 5;
    
    /**
     * @var int The id of this solidary record.
     */
    private $id;

    /**
     * @var string|null The iri of this solidary record.
     *
     * @Groups({"get","post"})
     */
    private $iri;
    
    /**
     * @var int Ask status (0 = asked; 1 = refused; 2 = pending, 3 = looking for solution; 4 = follow up; 5 = closed).
     *
     * @Assert\NotBlank

     * @Groups({"get","post"})
     */
    private $status;

    /**
     * @var bool Social assist.
     *
     * @Groups({"get","post"})
     */
    private $assisted;

    /**
     * @var string Structure of the solidary record.
     *
     * @Groups({"get","post"})
     */
    private $structure;

    /**
     * @var string Subject of the solidary record.
     *
     * @Groups({"get","post"})
     */
    private $subject;

    /**
     * @var Proposal The proposal.
     *
     * @Groups({"get","post"})
     */
    private $proposal;

    /**
     * @var User The user related with the solidary record.
     *
     * @Assert\NotBlank
     * @Groups({"get", "post"})
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date of the solidary record.
     *
     * @Groups("get")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary record.
     *
     * @Groups("get")
     */
    private $updatedDate;

    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function setIri(?string $iri): Solidary
    {
        $this->iri = $iri;
        return $this;
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

    public function isAssisted(): ?bool
    {
        return $this->assisted;
    }

    public function setAssisted(bool $isAssisted): self
    {
        $this->assisted = $isAssisted;

        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(string $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getProposal(): Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

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
}
