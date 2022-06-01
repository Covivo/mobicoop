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

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Wallet
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Wallet
{
    public const DEFAULT_ID = "999999999999";

    /**
     * @var string The id of this wallet
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var string|null The description of this wallet
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $description;

    /**
     * @var WalletBalance The ballance of this wallet
     *
     * @Groups({"readPayment"})
     */
    private $balance;

    /**
     * @var string General Currency of this wallet
     *
     * @Groups({"readPayment"})
     */
    private $currency;

    /**
     * @var string|null A comment for this wallet
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $comment;

    /**
     * @var string|null Identifier (on the payment provider's platform) of the owner of this wallet
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $ownerIdentifier;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readPayment"})
     */
    private $createdDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?String
    {
        return $this->id;
    }

    public function setId(String $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): ?String
    {
        return $this->description;
    }

    public function setDescription(?String $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBalance(): ?WalletBalance
    {
        return $this->balance;
    }

    public function setBalance(?WalletBalance $walletBalance): self
    {
        $this->balance = $walletBalance;

        return $this;
    }

    public function getCurrency(): ?String
    {
        return $this->currency;
    }

    public function setCurrency(?String $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getComment(): ?String
    {
        return $this->comment;
    }

    public function setComment(?String $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getOwnerIdentifier(): ?String
    {
        return $this->ownerIdentifier;
    }

    public function setOwnerIdentifier(?String $ownerIdentifier): self
    {
        $this->ownerIdentifier = $ownerIdentifier;

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
}
