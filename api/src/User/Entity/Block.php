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

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Block between two Users
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Block
{
    /**
     * @var int The id of this Block.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readBlock"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var User The User who made the Block
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="blocks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
    */
    private $user;

    /**
     * @var User The User blocked by $user
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="blockBys")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $blockedUser;

    /**
     * @var \DateTimeInterface Creation date of this Block.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readBlock"})
     */
    private $createdDate;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBlockedUser(): ?User
    {
        return $this->blockedUser;
    }

    public function setBlockedUser(?User $blockedUser): self
    {
        $this->blockedUser = $blockedUser;

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
