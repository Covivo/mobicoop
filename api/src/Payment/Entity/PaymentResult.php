<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
 * A PaymentResult. It's generated after a payout or a transfert.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentResult
{
    public const DEFAULT_ID = '999999999999';

    public const RESULT_ONLINE_PAYMENT_STATUS_SUCCESS = 'OK';
    public const RESULT_ONLINE_PAYMENT_STATUS_FAILED = 'KO';
    public const RESULT_ONLINE_PAYMENT_TYPE_TRANSFER = 'TRANSFER';
    public const RESULT_ONLINE_PAYMENT_TYPE_PAYOUT = 'PAYOUT';

    /**
     * @var string The id of this result = the CarpoolPayment's id
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var null|string Result's type
     *
     * @Groups({"readPayment"})
     */
    private $type;

    /**
     * @var int Debtor's id involved in this result
     *
     * @Groups({"readPayment"})
     */
    private $debtorId;

    /**
     * @var int Creditor's id involved in this result
     *
     * @Groups({"readPayment"})
     */
    private $creditorId;

    /**
     * @var int CarpoolItem's id involved in this result
     *
     * @Groups({"readPayment"})
     */
    private $carpoolItemId;

    /**
     * @var null|string Result's status
     *
     * @Groups({"readPayment"})
     */
    private $status;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDebtorId(): ?int
    {
        return $this->debtorId;
    }

    public function setDebtorId(?int $debtorId): self
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function getCreditorId(): ?int
    {
        return $this->creditorId;
    }

    public function setCreditorId(?int $creditorId): self
    {
        $this->creditorId = $creditorId;

        return $this;
    }

    public function getCarpoolItemId(): ?int
    {
        return $this->carpoolItemId;
    }

    public function setCarpoolItemId(?int $carpoolItemId): self
    {
        $this->carpoolItemId = $carpoolItemId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
