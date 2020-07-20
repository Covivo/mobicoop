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

use App\Payment\Ressource\BankAccount;
use App\Payment\Exception\PaymentException;
use App\User\Entity\User;
use App\DataProvider\Entity\MangoPayProvider;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\Wallet;
use Symfony\Component\Security\Core\Security;

/**
 * Payment provider.
 *
 * This service contains methods related to payment.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentDataProvider
{
    private $paymentActive;
    private $paymentProvider;
    private $providerInstance;
    private $paymentProfileRepository;
    private $defaultCurrency;
    private $platformName;

    private const SUPPORTED_PROVIDERS = [
        "MangoPay" => MangoPayProvider::class
    ];
    
    public function __construct(
        PaymentProfileRepository $paymentProfileRepository,
        Security $security,
        bool $paymentActive,
        string $paymentProvider,
        string $clientId,
        string $apikey,
        bool $sandBoxMode,
        string $platformName,
        string $defaultCurrency
    ) {
        $this->paymentProvider = $paymentProvider;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->defaultCurrency = $defaultCurrency;
        $this->platformName = $platformName;
        $this->paymentActive = $paymentActive;

        if (isset(self::SUPPORTED_PROVIDERS[$paymentProvider])) {
            $providerClass = self::SUPPORTED_PROVIDERS[$paymentProvider];
            $this->providerInstance = new $providerClass($security->getUser(), $clientId, $apikey, $sandBoxMode, $paymentProfileRepository);
        }
    }
    
    /**
     * Check if the payment is correcty configured
     */
    public function checkPaymentConfiguration()
    {
        if (!$this->paymentActive) {
            throw new PaymentException(PaymentException::PAYMENT_INACTIVE);
        }
        $this->paymentActive = $this->paymentActive;

        if (empty($this->paymentProvider)) {
            throw new PaymentException(PaymentException::PAYMENT_NO_PROVIDER);
        }

        if (!isset(self::SUPPORTED_PROVIDERS[$this->paymentProvider])) {
            throw new PaymentException(PaymentException::UNSUPPORTED_PROVIDER);
        }
    }
    

    /**
     * Get the BankAccounts of a PaymentProfile
     *
     * @param PaymentProfile $paymentProfile
     * @return BankAccount|null
     */
    public function getPaymentProfileBankAccounts(PaymentProfile $paymentProfile)
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->getBankAccounts($paymentProfile);
    }

    /**
     * Add a BankAccount
     *
     * @param BankAccount $user     The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(BankAccount $bankAccount)
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->addBankAccount($bankAccount);
    }

    /**
     * Disable a BankAccount
     *
     * @param BankAccount $user     The BankAccount to create
     * @return BankAccount|null
     */
    public function disableBankAccount(BankAccount $bankAccount)
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->disableBankAccount($bankAccount);
    }


    /**
     * Get the PaymentProfiles of a User
     *
     * @param User $user    The User
     * @return PaymentProfile[]|null
     */
    public function getPaymentProfiles(User $user)
    {
        $this->checkPaymentConfiguration();

        // Get more information for each profiles
        $paymentProfiles = $this->paymentProfileRepository->findBy(["user"=>$user]);
        foreach ($paymentProfiles as $paymentProfile) {
            /**
             * @var PaymentProfile $paymentProfile
             */
            
            $paymentProfile->setBankAccounts($this->providerInstance->getBankAccounts($paymentProfile));
            $paymentProfile->setWallets($this->providerInstance->getWallets($paymentProfile));
        }
        return $paymentProfiles;
    }

    
    /**
     * Register a User on the payment provider platform
     *
     * @param User $user
     * @return string The identifier
     */
    public function registerUser(User $user)
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->registerUser($user);
    }

    /**
     * Create a wallet for a user
     *
     * @param $identifier Identifier of the User (the one used on the provider's platform)
     * @return Wallet The created wallet
     */
    public function createWallet(string $identifier)
    {
        $this->checkPaymentConfiguration();
        $wallet = new Wallet();
        $wallet->setDescription("Wallet from ".$this->platformName); // This field is required
        $wallet->setComment("");
        $wallet->setCurrency($this->defaultCurrency);
        $wallet->setOwnerIdentifier($identifier);
        return $this->providerInstance->addWallet($wallet);
    }
}
