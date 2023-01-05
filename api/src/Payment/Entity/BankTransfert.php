<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\CarpoolProof;
use App\Geography\Entity\Territory;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Bank Transfert.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfert
{
    public const DEFAULT_ID = '999999999999';

    public const STATUS_INVALID = 0;
    public const STATUS_INITIATED = 1;
    public const STATUS_EMITTED = 1;
    public const STATUS_EXECUTED = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_ABANDONNED_NO_PAYMENT_PROVIDER = 4;
    public const STATUS_ABANDONNED_FUNDS_UNAVAILABLE = 5;

    /**
     * @var string The id of this bank transfert
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var User The recipient of this bank transfert
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"readPayment"})
     * @MaxDepth(1)
     */
    private $recipient;

    /**
     * @var float Amount of this bank transfert
     *
     * @ORM\Column(type="decimal", precision=10, scale=6)
     * @Assert\NotBlank
     * @Groups({"readPayment"})
     */
    private $amount;

    /**
     * @var null|int Territory of the paying territory
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"readPayment"})
     */
    private $territory;

    /**
     * @var null|int CarpoolProofId linked to this bank transfert
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\CarpoolProof")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"readPayment"})
     */
    private $carpoolProof;

    /**
     * @var null|string Various textual details about this bank transfert (separated by |)
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"readPayment"})
     */
    private $details;

    /**
     * @var int Bank Transfert status
     *
     * @ORM\Column(type="integer")
     * @Groups({"readPayment"})
     */
    private $status;

    /**
     * @var string Bank Transfert batch id (timestamp)
     *
     * @ORM\Column(type="integer")
     * @Groups({"readPayment"})
     */
    private $batchId;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readPayment"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface update date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $updatedDate;

    public function __construct(?int $id = null)
    {
        if (null !== $id) {
            $this->id = $id;
        } else {
            $this->id = self::DEFAULT_ID;
        }
        $this->status = self::STATUS_INITIATED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTerritory(): ?Territory
    {
        return $this->territory;
    }

    public function setTerritory(?Territory $territory): self
    {
        $this->territory = $territory;

        return $this;
    }

    public function getCarpoolProof(): ?CarpoolProof
    {
        return $this->carpoolProof;
    }

    public function setCarpoolProof(?CarpoolProof $carpoolProof): self
    {
        $this->carpoolProof = $carpoolProof;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

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

    public function getBatchId(): ?int
    {
        return $this->batchId;
    }

    public function setBatchId(int $batchId): self
    {
        $this->batchId = $batchId;

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
