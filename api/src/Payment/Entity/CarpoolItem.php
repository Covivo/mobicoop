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

use App\Carpool\Entity\Ask;
use Doctrine\ORM\Mapping as ORM;
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpool item : a carpool journey effectively done, or supposed to be done.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolItem
{
    const STATUS_INITIALIZED = 0;
    const STATUS_REALIZED = 1;
    const STATUS_NOT_REALIZED = 2;

    const DEBTOR_STATUS_PENDING = 0;
    const DEBTOR_STATUS_ONLINE = 1;
    const DEBTOR_STATUS_DIRECT = 2;

    const CREDITOR_STATUS_PENDING = 0;
    const CREDITOR_STATUS_ONLINE = 1;
    const CREDITOR_STATUS_DIRECT = 2;
    //const CREDITOR_STATUS_UNPAID = 3;

    /**
     * @var int The id of this item.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int Item type related with the ask :
     * 1 : one way trip
     * 2 : outward of a round trip
     * 3 : return of a round trip
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @var int The status of the carpool :
     * 0 : the carpool was planned, we don't know yet if it has been realized
     * 1 : the carpool has been realized (planned or dynamic)
     * 2 : the carpool was planned but was not realized
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     */
    private $itemStatus;

    /**
     * @var \DateTimeInterface The date of the carpool (=date of the start of the carpool).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="date")
     */
    private $itemDate;

    /**
    * @var float The amount to be paid.
    *
    * @Assert\NotBlank
    * @ORM\Column(type="decimal", precision=6, scale=2)
    */
    private $amount;

    /**
     * @var int Debtor payment status :
     * 0 : waiting for payment
     * 1 : payment done electronically
     * 2 : payment done manually
     *
     * @ORM\Column(type="smallint")
     */
    private $debtorStatus;

    /**
     * @var int Creditor payment status :
     * 0 : waiting for payment
     * 1 : payment receveid electronically
     * 2 : payment receveid manually
     *
     * @ORM\Column(type="smallint")
     */
    private $creditorStatus;

    /**
     * @var Ask The ask related to the item.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="carpoolItems")
     */
    private $ask;

    /**
     * @var User Debtor user (the user that pays)
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     */
    private $debtorUser;

    /**
     * @var User Creditor user (the user paid)
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     */
    private $creditorUser;

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
     * @var \DateTimeInterface Unpaid notify date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $unpaidDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getItemStatus(): ?int
    {
        return $this->itemStatus;
    }

    public function setItemStatus(int $itemStatus): self
    {
        $this->itemStatus = $itemStatus;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }
    
    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
    }

    public function getDebtorStatus(): ?int
    {
        return $this->debtorStatus;
    }

    public function setDebtorStatus(int $debtorStatus): self
    {
        $this->debtorStatus = $debtorStatus;

        return $this;
    }

    public function getCreditorStatus(): ?int
    {
        return $this->creditorStatus;
    }

    public function setCreditorStatus(int $creditorStatus): self
    {
        $this->creditorStatus = $creditorStatus;

        return $this;
    }

    public function getItemDate(): ?\DateTimeInterface
    {
        return $this->itemDate;
    }

    public function setItemDate(\DateTimeInterface $itemDate): self
    {
        $this->itemDate = $itemDate;

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

    public function getDebtorUser(): ?User
    {
        return $this->debtorUser;
    }

    public function setDebtorUser(?User $debtorUser): self
    {
        $this->debtorUser = $debtorUser;

        return $this;
    }

    public function getCreditorUser(): ?User
    {
        return $this->creditorUser;
    }

    public function setCreditorUser(?User $creditorUser): self
    {
        $this->creditorUser = $creditorUser;

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

    public function getUnpaidDate(): ?\DateTimeInterface
    {
        return $this->unpaidDate;
    }

    public function setUnpaidDate(?\DateTimeInterface $unpaidDate): self
    {
        $this->unpaidDate = $unpaidDate;

        return $this;
    }

    // DOCTRINE EVENTS

    /**
     * Item status.
     *
     * @ORM\PrePersist
     */
    public function setAutoItemStatus()
    {
        $this->setItemStatus(self::STATUS_INITIALIZED);
    }

    /**
     * Debtor status.
     *
     * @ORM\PrePersist
     */
    public function setAutoDebtorStatus()
    {
        $this->setDebtorStatus(self::DEBTOR_STATUS_PENDING);
    }

    /**
     * Creditor status.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreditorStatus()
    {
        $this->setCreditorStatus(self::CREDITOR_STATUS_PENDING);
    }

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
