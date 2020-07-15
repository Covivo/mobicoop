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

namespace App\Payment\Interfaces;

use App\User\Entity\User;
use App\Payment\Entity\BankAccount;

/**
 * Payment Provider interface.
 *
 * A payment provider entity class must implement all these methods in order to perform all possible payment related actions
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
interface PaymentProviderInterface
{
    /**
     * Returns a collection of Bank accounts.
     *
     * @param User $user     The User owning the Bank accounts
     * @return BankAccount[]
     */
    public function getBankAccounts(User $user);
    
    /**
     * Returns a single Bank account
     *
     * @param User $user     The User owning the Bank account
     * @return BankAccount|null
     */
    public function getBankAccount(User $user);
    
    /**
     * Add a BankAccount
     *
     * @param User $user            The User owning the Bank account
     * @param BankAccount $user     The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(User $user, BankAccount $bankAccount);
}
