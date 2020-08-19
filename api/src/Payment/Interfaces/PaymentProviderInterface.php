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
use App\Payment\Ressource\BankAccount;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\Wallet;

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
     * Register a User on the platform
     *
     * @param User $user     The User to register
     * @return string The identifier
     */
    public function registerUser(User $user);

    /**
     * Returns a collection of Bank accounts.
     *
     * @param PaymentProfile $paymentProfile     The User's payment profile related to the Bank accounts
     * @return BankAccount[]
     */
    public function getBankAccounts(PaymentProfile $paymentProfile, bool $onlyActive = true);
    
    /**
     * Add a BankAccount
     *
     * @param BankAccount $bankAccount                  The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(BankAccount $bankAccount);

    /**
     * Disable a BankAccount
     *
     * @param BankAccount $bankAccount                  The BankAccount to create
     * @return BankAccount|null
     */
    public function disableBankAccount(BankAccount $bankAccount);

    /**
     * Returns a collection of Wallet.
     *
     * @param PaymentProfile $paymentProfile     The User's payment profile related to the wallets
     * @return BankAccount[]
     */
    public function getWallets(PaymentProfile $paymentProfile);

    /**
     * Add a Wallet
     *
     * @param Wallet $user  The Wallet to create
     * @return Wallet|null
     */
    public function addWallet(Wallet $wallet);
}
