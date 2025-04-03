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

class BankAccountToken extends Token
{
    private const OBJECT = 'bank_account';
    private const ACCOUNT_HOLDER_TYPE = 'individual';

    /**
     * @var string
     */
    private $object;

    /**
     * @var string
     */
    private $account_number;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $account_holder_name;

    /**
     * @var string
     */
    private $account_holder_type;

    /**
     * @var string
     */
    private $currency;

    public function __construct(string $account_number, string $country, string $account_holder_name, string $currency)
    {
        $this->setAccountNumber($account_number);
        $this->setCountry($country);
        $this->setAccountHolderName($account_holder_name);
        $this->setCurrency($currency);
        $this->object = self::OBJECT;
        $this->account_holder_type = self::ACCOUNT_HOLDER_TYPE;
    }

    public function getAccountNumber(): string
    {
        return $this->account_number;
    }

    public function setAccountNumber(string $account_number): self
    {
        $this->account_number = $account_number;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAccountHolderName(): string
    {
        return $this->account_holder_name;
    }

    public function setAccountHolderName(string $account_holder_name): self
    {
        $this->account_holder_name = $account_holder_name;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getAccountHolderType(): string
    {
        return $this->account_holder_type;
    }

    public function buildBody(): array
    {
        return [
            self::OBJECT => [
                'account_number' => $this->getAccountNumber(),
                'country' => $this->getCountry(),
                'account_holder_name' => $this->getAccountHolderName(),
                'account_holder_type' => $this->getAccountHolderType(),
                'currency' => $this->getCurrency(),
            ],
        ];
    }
}
