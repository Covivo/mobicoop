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

class ExternalBankAccount
{
    private const OBJECT = 'external_account';

    /**
     * @var string
     */
    private $object;

    /**
     * @var string
     */
    private $stripeAccountId;

    /**
     * @var string
     */
    private $bankAccountTokenId;

    public function __construct(string $stripeAccountId, string $bankAccountTokenId)
    {
        $this->stripeAccountId = $stripeAccountId;
        $this->bankAccountTokenId = $bankAccountTokenId;
        $this->object = self::OBJECT;
    }

    public function getStripeAccountId(): string
    {
        return $this->stripeAccountId;
    }

    public function getBankAccountTokenId(): string
    {
        return $this->bankAccountTokenId;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function buildBody(): array
    {
        return [
            self::OBJECT => $this->bankAccountTokenId,
            'default_for_currency' => true,
        ];
    }
}
