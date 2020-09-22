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

use App\DataProvider\Ressource\Hook;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use App\Payment\Ressource\BankAccount;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\Wallet;
use App\Payment\Ressource\ElectronicPayment;
use App\Payment\Ressource\ValidationDocument;

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
     * @param User $user            The User to register
     * @param Address|null $address The address to use to the registration
     * @return string The identifier
     */
    public function registerUser(User $user, Address $address=null);

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
     * Get the secured form's url for electronic payment
     *
     * @param CarpoolPayment $carpoolPayment
     * @return CarpoolPayment With redirectUrl filled
     */
    public function generateElectronicPaymentUrl(CarpoolPayment $carpoolPayment);

    /**
     * Process an electronic payment between the $debtor and the $creditors
     *
     * array of creditors are like this :
     * $creditors = [
     *  "userId" => [
     *      "user" => User object
     *      "amount" => float
     *  ]
     * ]
     *
     * @param User $debtor
     * @param array $creditors
     * @return void
     */
    public function processElectronicPayment(User $debtor, array $creditors);

    /**
     * Upload an identity validation document
     * The document is not stored on the platform. It has to be deleted.
     *
     * @param ValidationDocument $validationDocument
     * @return ValidationDocument
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument);

    /**
     * Handle a payment web hook
     * @var object $hook The web hook from the payment provider
     * @return Hook with status and ressource id
     */
    public function handleHook(Hook $hook);
}
