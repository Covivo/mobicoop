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

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A week item.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WeekItem
{
    public const STATUS_PENDING = 0;
    public const STATUS_ONLINE = 1;
    public const STATUS_DIRECT = 2;
    public const STATUS_UNPAID = 3;

    /**
     * @var \DateTimeInterface The start of the week item
     *
     * @Groups({"readPayment"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The end of the week item
     *
     * @Groups({"readPayment"})
     */
    private $toDate;

    /**
     * @var int The week number of the item
     *
     * @Groups({"readPayment"})
     */
    private $numWeek;

    /**
     * @var null|int The year of the item
     *
     * @Groups({"readPayment"})
     */
    private $year;

    /** @var int payement status of the week
     * 0 : waiting for payment
     * 1 : payment received electronically
     * 2 : payment received manually
     * 3 : notified as unpaid
     */
    private $status;

    /**
     * @var null|int The paymentItem id of this Week
     * @Groups({"readPayment"})
     */
    private $paymentItemId;

    /**
     * @var null|int The paymentItem id of this Week
     * @Groups({"readPayment"})
     */
    private $unpaidDate;

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getNumWeek(): ?int
    {
        return $this->numWeek;
    }

    public function setNumWeek(int $numWeek): self
    {
        $this->numWeek = $numWeek;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

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

    public function getPaymentItemId(): ?int
    {
        return $this->paymentItemId;
    }

    public function setPaymentItemId(?int $paymentItemId): self
    {
        $this->paymentItemId = $paymentItemId;

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
}
