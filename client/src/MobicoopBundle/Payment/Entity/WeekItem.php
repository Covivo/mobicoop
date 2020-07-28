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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * A week item
 *
 * @author Remi Wortemann <remi.Wortemann@mobicoop.org>
 */
class WeekItem implements ResourceInterface, \JsonSerializable
{
    const STATUS_PENDING = 0;
    const STATUS_ONLINE = 1;
    const STATUS_DIRECT = 2;
    const STATUS_UNPAID = 3;

    /**
     * @var \DateTimeInterface The start of the week item
     *
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The end of the week item
     *
     */
    private $toDate;

    /**
     * @var int The week number of the item
     *
     */
    private $numWeek;

    /**
     * @var string|null The year of the item
     *
     */
    private $year;

    /** @var int payement status of the week
    * 0 : waiting for payment
    * 1 : payment receveid electronically
    * 2 : payment receveid manually
    * 3 : notified as unpaid
     */
    private $status;


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

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

    public function jsonSerialize()
    {
        return
            [
                'fromDate'      => $this->getFromDate(),
                'toDate'        => $this->getToDate(),
                'numWeek'       => $this->getNumWeek(),
                'year'          => $this->getYear()
            ];
    }
}
