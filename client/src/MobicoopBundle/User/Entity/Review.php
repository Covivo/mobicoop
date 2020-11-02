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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Review
 */
class Review implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int $id The id of this Review.
     */
    private $id;
   
    /**
     * @var User The User who left this Review
     * @Groups({"post"})
     */
    private $reviewer;

    /**
     * @var User The User targeted by this Review
     * @Groups({"post"})
     */
    private $reviewed;

    /**
     * @var string The content text of the review
     * @Groups({"post"})
     */
    private $content;

    /**
     * @var bool True if the review has already been left, Left if it's waiting
     * @Groups({"post"})
     */
    private $left;

    /**
     * @var \DateTimeInterface Review date
     */
    private $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getReviewer(): ?User
    {
        return $this->reviewer;
    }

    public function setReviewer(?User $reviewer): self
    {
        $this->reviewer = $reviewer;
        
        return $this;
    }

    public function getReviewed(): ?User
    {
        return $this->reviewed;
    }

    public function setReviewed(?User $reviewed): self
    {
        $this->reviewed = $reviewed;
        
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        
        return $this;
    }

    public function isLeft(): ?bool
    {
        return $this->left;
    }

    public function setLeft(?bool $left): self
    {
        $this->left = $left;
        
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function jsonSerialize()
    {
        $userSerialized = [
            'id'                        => $this->getId(),
            'reviewer'                  => $this->getReviewer(),
            'reviewed'                  => $this->getReviewed(),
            'content'                   => $this->getContent(),
            'left'                      => $this->isLeft(),
            'date'                      => $this->getdate()
        ];

        return $userSerialized;
    }
}
