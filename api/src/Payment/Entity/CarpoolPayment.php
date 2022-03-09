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
 */

namespace App\Payment\Entity;

use App\Action\Entity\Log;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpool payment : a carpool payment for carpool items.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolPayment
{
    public const STATUS_INITIATED = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_FAILURE = 2;

    public const ORIGIN_DESKTOP = 0;
    public const ORIGIN_MOBILE = 1;
    public const ORIGIN_MOBILE_SITE = 2;

    /**
     * @var int the id of this payment
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float the amount to be paid
     *
     * @Assert\NotBlank
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $amount;

    /**
     * @var int The status of the payment :
     *          0 : initiated
     *          1 : success
     *          2 : failure
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var User The user that pays
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var null|ArrayCollection Carpool items for this payment : many tries can be necessary for a successful payment. A payment may concern many items.
     *
     * @ORM\ManyToMany(targetEntity="\App\Payment\Entity\CarpoolItem", inversedBy="carpoolPayments")
     */
    private $carpoolItems;

    /**
     * @var int the transaction id of this payment if there is an online part
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $transactionId;

    /**
     * @var \DateTimeInterface the transaction date of this payment if there is an online part
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $transactionDate;

    /**
     * @var string the transaction post data of this payment if there is an online part
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transactionPostData;

    /**
     * @var string Secured form's url to process the electronic payement
     */
    private $redirectUrl;

    /**
     * @var null|string Filled if we need to create the payment profile
     */
    private $createCarpoolProfileIdentifier;

    /**
     * @var int Origin of this payment
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $origin;

    /**
     * @var ArrayCollection The logs linked with the carpool payment
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="carpoolPayment")
     */
    private $logs;

    /**
     * @var float The amountOnline to be paid. Not persisted.
     */
    private $amountOnline;

    public function __construct()
    {
        $this->carpoolItems = new ArrayCollection();
        $this->origin = self::ORIGIN_DESKTOP;
    }

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

    public function getAmountOnline(): ?string
    {
        return $this->amountOnline;
    }

    public function setAmountOnline(?string $amountOnline)
    {
        $this->amountOnline = $amountOnline;
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

    public function getTransactionId(): ?int
    {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getTransactionDate(): ?\DateTimeInterface
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTimeInterface $transactionDate): self
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    public function getTransactionPostData(): ?string
    {
        return $this->transactionPostData;
    }

    public function setTransactionPostData(?string $transactionPostData): self
    {
        $this->transactionPostData = $transactionPostData;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getCreateCarpoolProfileIdentifier(): ?int
    {
        return $this->createCarpoolProfileIdentifier;
    }

    public function setCreateCarpoolProfileIdentifier(string $createCarpoolProfileIdentifier): self
    {
        $this->createCarpoolProfileIdentifier = $createCarpoolProfileIdentifier;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(int $origin): self
    {
        $this->origin = $origin;

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
            $log->setCarpoolPayment($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getCarpoolPayment() === $this) {
                $log->setCarpoolPayment(null);
            }
        }

        return $this;
    }

    // DOCTRINE EVENTS

    /**
     * Status.
     *
     * @ORM\PrePersist
     */
    public function setAutoStatus()
    {
        $this->setStatus(self::STATUS_INITIATED);
    }

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
