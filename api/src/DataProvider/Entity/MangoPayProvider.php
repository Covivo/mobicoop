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

namespace App\DataProvider\Entity;

use App\Payment\Entity\BankAccount;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\User\Entity\User;

/**
 * Payment Provider for MangoPay
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MangoPayProvider implements PaymentProviderInterface
{
    const SERVER_URL_SANDBOX = "https://api.sandbox.mangopay.com/";
    const SERVER_URL = "https://api.mangopay.com/";

    private $clientId;
    private $sandBoxMode;
    private $serverUrl;
    
    public function __construct(string $clientId, bool $sandBoxMode)
    {
        $this->clientId = $clientId;
        $this->sandBoxMode = $sandBoxMode;
        ($this->sandBoxMode) ? $this->serverUrl = self::SERVER_URL_SANDBOX : $this->serverUrl = self::SERVER_URL;
    }
    
    /**
     * Returns a collection of Bank accounts.
     *
     * @param User $user     The User owning the Bank accounts
     * @return BankAccount[]
     */
    public function getBankAccounts(User $user)
    {
    }
    
    /**
     * Returns a single Bank account
     *
     * @param User $user     The User owning the Bank account
     * @return BankAccount|null
     */
    public function getBankAccount(User $user)
    {
    }
    
    /**
     * Add a BankAccount
     *
     * @param User $user            The User owning the Bank account
     * @param BankAccount $user     The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(User $user, BankAccount $bankAccount)
    {
    }
}
