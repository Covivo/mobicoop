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
use App\DataProvider\Ressource\Hook;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Entity\Wallet;
use App\Payment\Ressource\ValidationDocument;
use DateTime;
use Symfony\Component\Security\Core\Security;

/**
 * Payment provider.
 *
 * This service contains methods related to payment.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class PaymentDataProvider
{
    private $paymentActive;
    private $paymentActiveDate;
    private $paymentProvider;
    private $providerInstance;
    private $paymentProfileRepository;
    private $defaultCurrency;
    private $validationDocsPath;
    private $platformName;
    private $baseUri;
    private $baseMobileUri;

    private $security;
    private $clientId;
    private $apikey;
    private $sandBoxMode;

    private const SUPPORTED_PROVIDERS = [
        "MangoPay" => MangoPayProvider::class
    ];

    public function __construct(
        PaymentProfileRepository $paymentProfileRepository,
        Security $security,
        string $paymentActive,
        string $paymentProvider,
        string $clientId,
        string $apikey,
        bool $sandBoxMode,
        string $platformName,
        string $defaultCurrency,
        string $validationDocsPath,
        string $baseUri,
        string $baseMobileUri
    ) {
        $this->paymentProvider = $paymentProvider;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->defaultCurrency = $defaultCurrency;
        $this->validationDocsPath = $validationDocsPath;
        $this->baseUri = $baseUri;
        $this->baseMobileUri = $baseMobileUri;
        $this->platformName = $platformName;
        $this->paymentActive = false;
        if ($this->paymentActiveDate = DateTime::createFromFormat("Y-m-d", $paymentActive)) {
            $this->paymentActiveDate->setTime(0, 0);
            $this->paymentActive = true;
        }

        $this->security = $security;
        $this->clientId = $clientId;
        $this->apikey = $apikey;
        $this->sandBoxMode = $sandBoxMode;
    }

    /**
     * Check if the payment is correcty configured
     */
    public function checkPaymentConfiguration()
    {
        if ($this->paymentProvider!=="") {
            if (isset(self::SUPPORTED_PROVIDERS[$this->paymentProvider])) {
                $providerClass = self::SUPPORTED_PROVIDERS[$this->paymentProvider];
                $this->providerInstance = new $providerClass(
                    $this->security->getUser(),
                    $this->clientId,
                    $this->apikey,
                    $this->sandBoxMode,
                    $this->defaultCurrency,
                    $this->validationDocsPath,
                    $this->baseUri,
                    $this->baseMobileUri,
                    $this->paymentProfileRepository
                );
            }
        } else {
            return;
        }

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
    public function getPaymentProfileBankAccounts(PaymentProfile $paymentProfile): ?BankAccount
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
    public function addBankAccount(BankAccount $bankAccount): ?BankAccount
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
    public function disableBankAccount(BankAccount $bankAccount): ?BankAccount
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->disableBankAccount($bankAccount);
    }


    /**
     * Get the PaymentProfiles of a User
     *
     * @param User $user                    The User
     * @param bool $callExternalProvider    true : make the call to the external provider to get bank accounts and wallets
     * @return PaymentProfile[]|null
     */
    public function getPaymentProfiles(User $user, $callExternalProvider=true): ?array
    {
        $this->checkPaymentConfiguration();

        // Get more information for each profiles
        $paymentProfiles = $this->paymentProfileRepository->findBy(["user"=>$user]);
        if (!is_null($paymentProfiles)) {
            foreach ($paymentProfiles as $paymentProfile) {
                /**
                 * @var PaymentProfile $paymentProfile
                 */

                if ($callExternalProvider) {
                    $bankAccounts = $this->providerInstance->getBankAccounts($paymentProfile);
                    foreach ($bankAccounts as $bankAccount) {
                        $bankAccount->setValidationStatus($paymentProfile->getValidationStatus());
                    }
                    $paymentProfile->setBankAccounts($bankAccounts);
                    $paymentProfile->setWallets($this->providerInstance->getWallets($paymentProfile));
                }
                $user->setPaymentProfileId($paymentProfile->getId());
            }
        }
        return $paymentProfiles;
    }


    /**
     * Register a User on the payment provider platform
     *
     * @param User $user
     * @param Address|null $address The address to use to the registration
     * @return string The identifier
     */
    public function registerUser(User $user, Address $address=null): string
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->registerUser($user, $address);
    }


    /**
     * Update a User on the payment provider platform
     *
     * @param User $user
     * @return string The identifier
     */
    public function updateUser(User $user): string
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->updateUser($user);
    }

    /**
     * Create a wallet for a user
     *
     * @param $identifier Identifier of the User (the one used on the provider's platform)
     * @return Wallet The created wallet
     */
    public function createWallet(string $identifier): Wallet
    {
        $this->checkPaymentConfiguration();
        $wallet = new Wallet();
        $wallet->setDescription("Wallet from ".$this->platformName); // This field is required
        $wallet->setComment("");
        $wallet->setCurrency($this->defaultCurrency);
        $wallet->setOwnerIdentifier($identifier);
        return $this->providerInstance->addWallet($wallet);
    }

    /**
     * Handle a payment web hook
     * @var object $hook The web hook from the payment provider
     * @return Hook with status and transaction id
     */
    public function handleHook(Hook $hook): Hook
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->handleHook($hook);
    }

    /**
     * Get the secured form's url for electronic payment
     *
     * @param CarpoolPayment $carpoolPayment
     * @return CarpoolPayment With redirectUrl filled
     */
    public function generateElectronicPaymentUrl(CarpoolPayment $carpoolPayment): CarpoolPayment
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->generateElectronicPaymentUrl($carpoolPayment);
    }

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
    public function processElectronicPayment(User $debtor, array $creditors): void
    {
        $this->checkPaymentConfiguration();
        $this->providerInstance->processElectronicPayment($debtor, $creditors);
    }

    /**
     * Upload an identity validation document to the payment provider
     * The document is not stored on the platform. It has to be deleted.
     *
     * @param ValidationDocument $validationDocument
     * @return ValidationDocument
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument): ValidationDocument
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->uploadValidationDocument($validationDocument);
    }


    public function getDocument(int $validationDocumentId)
    {
        $this->checkPaymentConfiguration();
        return $this->providerInstance->getDocument($validationDocumentId);
    }
}
