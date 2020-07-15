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

namespace App\Payment\Service;

use App\Payment\Entity\BankAccount;
use App\Payment\Exception\PaymentException;
use App\User\Entity\User;
use App\DataProvider\Entity\MangoPayProvider;
use App\Payment\Entity\PaymentProfile;

/**
 * Payment provider.
 *
 * This service contains methods related to payment.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentProvider
{
    private $paymentActive;
    private $paymentProvider;
    private $providerInstance;

    private const SUPPORTED_PROVIDERS = [
        "MangoPay" => MangoPayProvider::class
    ];
    
    public function __construct(bool $paymentActive, string $paymentProvider, string $clientId, string $apikey, bool $sandBoxMode)
    {
        if (!$paymentActive) {
            throw new PaymentException(PaymentException::PAYMENT_INACTIVE);
        }
        $this->paymentActive = $paymentActive;

        if (empty($paymentProvider)) {
            throw new PaymentException(PaymentException::PAYMENT_NO_PROVIDER);
        }

        if (!isset(self::SUPPORTED_PROVIDERS[$paymentProvider])) {
            throw new PaymentException(PaymentException::UNSUPPORTED_PROVIDER);
        }
        $this->paymentProvider = $paymentProvider;
        $providerClass = self::SUPPORTED_PROVIDERS[$paymentProvider];
        $this->providerInstance = new $providerClass($clientId, $apikey, $sandBoxMode);
    }
    
    /**
     * Add a BankAccount
     *
     * @param BankAccount $user     The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(BankAccount $bankAccount)
    {
        return $this->providerInstance->addBankAccount($bankAccount);
    }

    public function getPaymentProfile(User $user)
    {
        //return $this->providerInstance->getBankAccounts($user);
        
        // Get more information for each profiles
        $paymentProfiles = $user->getPaymentProfiles();
        foreach ($paymentProfiles as $paymentProfile) {
            /**
             * @var PaymentProfile $paymentProfile
             */
            
            $paymentProfile->setBankAccounts($this->providerInstance->getBankAccounts($paymentProfile));
            $paymentProfile->setWallets($this->providerInstance->getWallets($paymentProfile));
        }
        return $user;
    }
}
