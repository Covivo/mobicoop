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

/**
 * A payment transaction
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentTransaction
{
    const STATUS_FAILED = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_DELAYED = 2;
    const STATUS_REFUSED = 3;

    /**
     * @var int The id of this payment profile
     */
    private $id;

    /**
     * @var int The status of the transaction
     */
    private $status;


    public function __construct($id=null, $status=null)
    {
        $this->id=$id;
        $this->$status=$status;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
