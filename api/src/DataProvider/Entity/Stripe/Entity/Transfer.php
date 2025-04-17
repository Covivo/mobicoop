<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity\Stripe\Entity;

class Transfer
{
    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $transfert_group;

    public function __construct(string $currency, int $amount, string $destination, string $transfert_group = '')
    {
        $this->currency = $currency;
        $this->amount = $amount;
        $this->destination = $destination;
        $this->transfert_group = $transfert_group;
    }

    public function buildBody(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'destination' => $this->destination,
            'transfer_group' => $this->transfert_group,
        ];
    }
}
