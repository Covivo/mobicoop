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

namespace App\Payment\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpool payment : a carpool payment for carpool items.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolPayment
{
    const STATUS_INITIATED = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;

    /**
     * @var int The id of this item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
    * @var float The amount to be paid.
    *
    * @Assert\NotBlank
    * @ORM\Column(type="decimal", precision=6, scale=2)
    */
    private $amount;

    /**
     * @var int The status of the payment :
     * 0 : initiated
     * 1 : success
     * 2 : failure
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var User The user that pays
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null Payment tries for carpool items : many tries can be necessary for a successful payment. A payment may concern many items.
     *
     * @ORM\ManyToMany(targetEntity="\App\Payment\Entity\CarpoolItem")
     */
    private $carpoolItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }
    
    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
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

    public function getCarpoolItems()
    {
        return $this->carpoolItems->getValues();
    }

    public function addCarpoolItem(CarpoolItem $carpoolItem): self
    {
        if (!$this->carpoolItems->contains($carpoolItem)) {
            $this->carpoolItems[] = $carpoolItem;
        }
        
        return $this;
    }
    
    public function removeCarpoolItem(CarpoolItem $carpoolItem): self
    {
        if ($this->carpoolItems->contains($carpoolItem)) {
            $this->carpoolItems->removeElement($carpoolItem);
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
