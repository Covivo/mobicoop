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

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Wallet.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Wallet
{
    public const DEFAULT_ID = '999999999999';

    /**
     * @var string The id of this wallet
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var null|string The description of this wallet
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
     * @var null|string A comment for this wallet
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $comment;

    /**
     * @var null|string Identifier (on the payment provider's platform) of the owner of this wallet
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $ownerIdentifier;

    /**
     * @var \DateTimeInterface creation date
     *
     * @Groups({"readPayment"})
     */
    private $createdDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getOwnerIdentifier(): ?string
    {
        return $this->ownerIdentifier;
    }

    public function setOwnerIdentifier(?string $ownerIdentifier): self
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
