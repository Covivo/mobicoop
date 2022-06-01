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

namespace App\Carpool\Entity;

use App\Geography\Entity\Address;
use App\User\Entity\User;

/**
 * A CarpoolExport item.
 *
 *  @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CarpoolExport
{
    public const DEFAULT_ID = 999999999999;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;

    public const MODE_ONLINE = 1;
    public const MODE_DIRECT = 2;

    public const CERTIFICATION_A = "A";
    public const CERTIFICATION_B = "B";
    public const CERTIFICATION_C = "C";

    /**
     * @var int The id of this carpoolExport item.
     */
    private $id;

    /**
     * @var \DateTimeInterface|null The date of the carpool.
     *
     */
    private $date;

    /**
     * @var int The frequency (1 = driver; 2 = passenger).
     */
    private $role;

    /**
     * @var User The carpooler
     */
    private $carpooler;

    /**
     * @var Address The origin of the carpool
     */
    private $pickUp;

    /**
     * @var Address The destination of the carpool
     */
    private $dropOff;

    /**
     * @var string|null The amount for the carpool.
     */
    private $amount;

    /**
     * @var int|null The mode of payment for the carpool.
     */
    private $mode;

    /**
     * @var string|null The Certification of the payment for the carpool.
     */
    private $certification;



    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(User $carpooler): self
    {
        $this->carpooler = $carpooler;

        return $this;
    }

    public function getPickUp(): ?Address
    {
        return $this->pickUp;
    }

    public function setPickUp(?Address $pickUp): self
    {
        $this->pickUp = $pickUp;

        return $this;
    }

    public function getDropOff(): ?Address
    {
        return $this->dropOff;
    }

    public function setDropOff(?Address $dropOff): self
    {
        $this->dropOff = $dropOff;

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

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode)
    {
        $this->mode = $mode;
    }

    public function getCertification(): ?string
    {
        return $this->certification;
    }

    public function setCertification(?string $certification)
    {
        $this->certification = $certification;
    }
}
