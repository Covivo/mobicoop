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

use Stripe\Token as StripeToken;

class Account
{
    private const TYPE_PROFILE = 'business_profile';
    private const MCC = 4789;
    private const CAPABILITIES = [
        'bank_transfer_payments' => ['requested' => true],
        'card_payments' => ['requested' => true],
        'transfers' => ['requested' => true],
    ];

    /**
     * @var StripeToken
     */
    private $account_token;

    /**
     * @var string
     */
    private $mcc;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $capabilities;

    public function __construct(StripeToken $account_token, string $url)
    {
        $this->account_token = $account_token;
        $this->url = $url;
        $this->mcc = self::MCC;
        $this->capabilities = self::CAPABILITIES;
    }

    public function getAccountToken(): StripeToken
    {
        return $this->account_token;
    }

    public function setAccountToken(StripeToken $account_token): self
    {
        $this->account_token = $account_token;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getMcc(): string
    {
        return self::MCC;
    }

    public function getCapabilities(): array
    {
        return self::CAPABILITIES;
    }

    public function buildBody(): array
    {
        return [
            'account_token' => $this->getAccountToken()->id,
            self::TYPE_PROFILE => [
                'mcc' => $this->getMcc(),
                'url' => $this->getUrl(),
            ],
            'capabilities' => $this->getCapabilities(),
            'controller' => [
                'fees' => ['payer' => 'application'],
                'losses' => ['payments' => 'application'],
                'stripe_dashboard' => ['type' => 'none'],
                'requirement_collection' => 'application',
            ],
        ];
    }
}
