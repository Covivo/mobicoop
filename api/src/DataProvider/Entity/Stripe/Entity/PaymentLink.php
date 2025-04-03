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

class PaymentLink
{
    public const QUANTITY = 1;

    /**
     * @var string[]
     */
    private $prices;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $returnUrl;

    public function __construct(array $prices, string $returnUrl)
    {
        $this->prices = $prices;
        $this->quantity = self::QUANTITY;
        $this->returnUrl = $returnUrl;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    public function getQuantity(): int
    {
        return self::QUANTITY;
    }

    public function buildBody(): array
    {
        $return = [
            'line_items' => [],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [
                    'url' => $this->returnUrl,
                ],
            ],
        ];

        foreach ($this->prices as $price) {
            $return['line_items'][] = [
                'price' => $price,
                'quantity' => $this->getQuantity(),
            ];
        }

        return $return;
    }
}
