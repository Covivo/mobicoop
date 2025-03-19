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

namespace App\DataProvider\Entity\Stripe;

use App\DataProvider\Entity\Stripe\Entity\Account;
use App\DataProvider\Entity\Stripe\Entity\AccountToken;
use App\DataProvider\Entity\Stripe\Entity\BankAccountToken;
use App\DataProvider\Entity\Stripe\Entity\ExternalBankAccount;
use App\DataProvider\Entity\Stripe\Entity\File;
use App\DataProvider\Entity\Stripe\Entity\PaymentLink;
use App\DataProvider\Entity\Stripe\Entity\Price;
use App\DataProvider\Entity\Stripe\Entity\Token;
use App\DataProvider\Ressource\Hook;
use App\DataProvider\Ressource\MangoPayHook;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\Wallet;
use App\Payment\Exception\PaymentException;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Payment\Ressource\ValidationDocument;
use App\Payment\Service\PaymentDataProvider;
use App\User\Entity\User;
use Stripe\Account as StripeAccount;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\Exception\ApiErrorException;
use Stripe\File as StripeFile;
use Stripe\PaymentLink as StripePaymentLink;
use Stripe\Price as StripePrice;
use Stripe\StripeClient;
use Stripe\Token as StripeToken;

/**
 * Payment Provider for Stripe.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class StripeProvider implements PaymentProviderInterface
{
    public const BUSINESS_URL_SANDBOX = 'https://yourbusiness.com/';
    public const SERVER_URL = 'https://api.stripe.com/';
    public const LANDING_AFTER_PAYMENT = 'paiements/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE = '#/carpools/payment/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE_SITE = '#/carpools/payment/paye';
    public const VERSION = 'v1';

    public const BANKACCCOUNT_STATUS_VALIDATED = ['validated', 'new', 'verified'];

    private const STRIPE_API_VERSION = '2025-02-24.acacia';

    private $user;
    private $clientId;
    private $serverUrl;
    private $serverUrlNoClientId;
    private $authChain;
    private $currency;
    private $paymentProfileRepository;
    private $validationDocsPath;
    private $baseUri;
    private $baseMobileUri;

    private $_stripe;

    public function __construct(
        ?User $user,
        string $clientId,
        string $apikey,
        bool $sandBoxMode,
        string $currency,
        string $validationDocsPath,
        string $baseUri,
        string $baseMobileUri,
        PaymentProfileRepository $paymentProfileRepository
    ) {
        $this->serverUrl = self::SERVER_URL;
        $this->serverUrlNoClientId = $this->serverUrl;
        $this->user = $user;
        $this->clientId = $clientId;
        $this->serverUrl .= self::VERSION.'/'.$clientId.'/';
        $this->serverUrlNoClientId .= self::VERSION.'/';
        $this->currency = $currency;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->validationDocsPath = $validationDocsPath;
        $this->baseUri = (!$sandBoxMode) ? $baseUri : self::BUSINESS_URL_SANDBOX;
        $this->baseMobileUri = $baseMobileUri;

        $expectedPrefix = $sandBoxMode ? 'sk_test' : 'sk_live';

        if (0 !== strpos($apikey, $expectedPrefix)) {
            throw new PaymentException('Invalid API key');
        }

        $this->_stripe = new StripeClient([
            'api_key' => $apikey,
            'stripe_version' => self::STRIPE_API_VERSION,
        ]);
    }

    /**
     * Returns a collection of Bank accounts.
     *
     * @param PaymentProfile $paymentProfile The User's payment profile related to the Bank accounts
     * @param bool           $onlyActive     By default, only the active bank accounts are returned
     *
     * @return BankAccount[]
     */
    public function getBankAccounts(PaymentProfile $paymentProfile, bool $onlyActive = true)
    {
        $user = !is_null($this->user) ? $this->user : $paymentProfile->getUser();

        $stripeBankAccounts = $this->_stripe->accounts->allExternalAccounts(
            $this->_getUserIdentifier($user),
            ['object' => 'bank_account']
        );

        if (!isset($stripeBankAccounts->data)) {
            return [];
        }

        $bankAccounts = [];
        foreach ($stripeBankAccounts->data as $stripeBankAccount) {
            $bankAccount = $this->_deserializeBankAccount($stripeBankAccount);
            $bankAccount->setAddress($user->getHomeAddress());
            // if ($onlyActive && !$bankAccount->isActive()) {
            //     continue;
            // }
            $bankAccounts[] = $bankAccount;
        }

        // var_dump(json_encode($bankAccounts));

        // exit;

        return $bankAccounts;
    }

    /**
     * Add a BankAccount.
     *
     * @param BankAccount $bankAccount The BankAccount to create
     *
     * @return null|BankAccount
     */
    public function addBankAccount(BankAccount $bankAccount, ?string $externalAccountId = null)
    {
        $userName = $this->user->getGivenName().' '.$this->user->getFamilyName();

        $bankAccountToken = new BankAccountToken($bankAccount->getIban(), substr($bankAccount->getAddress()->getCountryCode(), 0, 2), $userName, $this->currency);
        $stripeToken = $this->_createToken($bankAccountToken);

        $externalBankAccount = new ExternalBankAccount($externalAccountId, $stripeToken->id);

        $this->_createExternalBankAccount($externalBankAccount);

        return $bankAccount;
    }

    /**
     * Disable a BankAccount (Only IBAN/BIC and active/inactive).
     *
     * @param BankAccount $bankAccount The BankAccount to disable
     *
     * @return null|BankAccount
     */
    public function disableBankAccount(BankAccount $bankAccount)
    {
        return $bankAccount;
    }

    /**
     * Register a User to the provider and create a PaymentProfile.
     *
     * @param null|Address $address The address to use to the registration
     *
     * @return string The identifier
     */
    public function registerUser(User $user, ?Address $address = null): string
    {
        $accountToken = new AccountToken($user, $address);
        $stripeToken = $this->_createToken($accountToken);

        $account = new Account($stripeToken, $this->baseUri);
        $stripeAccount = $this->_createAccount($account);

        return $stripeAccount->id;
    }

    /**
     * Update a User to the provider and create a PaymentProfile.
     *
     * @return string The identifier
     */
    public function updateUser(User $user)
    {
        return '';
    }

    /**
     * Get the secured form's url for electronic payment.
     *
     * @return CarpoolPayment With redirectUrl filled
     */
    public function generateElectronicPaymentUrl(CarpoolPayment $carpoolPayment): CarpoolPayment
    {
        $user = $carpoolPayment->getUser();
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $user]);

        if (is_null($paymentProfiles) || 0 == count($paymentProfiles)) {
            // No active payment profile. The User need at least a Wallet to pay so we register him and create a Wallet
            $identifier = $this->registerUser($user);
            $carpoolPayment->setCreateCarpoolProfileIdentifier($identifier); // To persist the paymentProfile in PaymentManager
        } else {
            $identifier = $paymentProfiles[0]->getIdentifier();
        }

        $stripePrice = $this->_createStripePrice($carpoolPayment, $user);

        $stripePaymentLink = $this->_createStripePaymentLink($carpoolPayment, $stripePrice);

        $carpoolPayment->setTransactionid($stripePaymentLink->id);
        $carpoolPayment->setRedirectUrl($stripePaymentLink->url);
        $carpoolPayment->setTransactionPostData($carpoolPayment->getTransactionPostData());

        return $carpoolPayment;
    }

    public function _createStripePaymentLink(CarpoolPayment $carpoolPayment, StripePrice $stripePrice): ?StripePaymentLink
    {
        $returnUrl = $this->baseUri.''.self::LANDING_AFTER_PAYMENT;
        if (CarpoolPayment::ORIGIN_MOBILE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        } elseif (CarpoolPayment::ORIGIN_MOBILE_SITE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        }

        $paymentLink = new PaymentLink([$stripePrice->id], $returnUrl);

        return $this->_createPaymentLink($paymentLink);
    }

    /**
     * Process an electronic payment between the $debtor and the $creditors.
     *
     * array of creditors are like this :
     * $creditors = [
     *  "userId" => [
     *      "user" => User object
     *      "amount" => float
     *  ]
     * ]
     */
    public function processElectronicPayment(User $debtor, array $creditors, string $tag): array
    {
        // $return = [];

        // // Get the wallet of the debtor and his identifier
        // $debtorPaymentProfile = $this->paymentProfileRepository->find($debtor->getPaymentProfileId());

        // // Transfer to the creditors wallets and payout
        // foreach ($creditors as $creditor) {
        //     $creditorWallet = $creditor['user']->getWallets()[0];
        //     $return[] = $this->transferWalletToWallet($debtorPaymentProfile->getIdentifier(), $debtorPaymentProfile->getWallets()[0], $creditorWallet, $creditor['amount'], $tag);

        //     // Do the payout to the default bank account
        //     $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());
        //     $creditorBankAccount = $creditor['user']->getBankAccounts()[0];
        //     $return[] = $this->triggerPayout($creditorPaymentProfile->getIdentifier(), $creditorWallet, $creditorBankAccount, $creditor['amount'], $tag);
        // }

        // return $return;
        return [];
    }

    /**
     * Trigger a payout from a Wallet to a Bank Account.
     */
    public function triggerPayout(string $authorIdentifier, Wallet $wallet, BankAccount $bankAccount, float $amount, string $reference = ''): ?string
    {
        return null;
    }

    /**
     * Handle a payment web hook.
     *
     * @var Hook The mangopay hook
     *
     * @return Hook with status and transaction id
     */
    public function handleHook(Hook $hook): Hook
    {
        // switch ($hook->getEventType()) {
        //     case MangoPayHook::PAYIN_SUCCEEDED:
        //     case MangoPayHook::VALIDATION_SUCCEEDED:
        //         $hook->setStatus(Hook::STATUS_SUCCESS);

        //         break;

        //     case MangoPayHook::VALIDATION_OUTDATED:
        //         $hook->setStatus(Hook::STATUS_OUTDATED_RESSOURCE);

        //         break;

        //     default:
        //         $hook->setStatus(Hook::STATUS_FAILED);
        // }

        return $hook;
    }

    /**
     * Upload an identity validation document
     * The document is not stored on the platform. It has to be deleted.
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument): ValidationDocument
    {
        $file = new File(File::PURPOSE_IDENTITY_VALIDATION, $validationDocument->getFile());
        $stripefile = $this->_createFile($file);

        $accountToken = new AccountToken($validationDocument->getUser());
        $accountToken->setValidationDocumentFrontId($stripefile->id);
        $stripeToken = $this->_createToken($accountToken);
        $return = $this->_updateUserFromToken($stripeToken->id);

        if (isset($return['individual']['verification']['document']['front'])) {
            $validationDocument->setIdentifier($return['individual']['verification']['document']['front']);
        }

        return $validationDocument;
    }

    public function getDocument($validationDocumentId) {}

    public function getKycDocument(string $kycDocumentId) {}

    public function getWallets(PaymentProfile $paymentProfile)
    {
        return [];
    }

    public function processAsyncElectronicPayment(User $debtor, array $creditors): array
    {
        var_dump('processAsyncElectronicPayment');
        $debtorPaymentProfile = $this->paymentProfileRepository->find($debtor->getPaymentProfileId());
        var_dump($debtorPaymentProfile->getUser()->getId());

        foreach ($creditors as $creditor) {
            if (CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE == $creditor['debtorStatus']) {
                // $creditorWallet = $creditor['user']->getWallets()[0];
                // $result = $this->transferWalletToWallet($debtorPaymentProfile->getIdentifier(), $debtorPaymentProfile->getWallets()[0], $creditorWallet, $creditor['amount']);
                // $treatedResult = $this->__treatReturn($debtor, $creditor, $result);
                // $return[] = $treatedResult;

                // do the transfert to the creditor
                var_dump('transfert to the creditor');
            }

            if (CarpoolItem::DEBTOR_STATUS_ONLINE == $creditor['debtorStatus']) {
                // Do the payout to the default bank account
                // $creditorWallet = $creditor['user']->getWallets()[0];
                // $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());
                // $creditorBankAccount = $creditor['user']->getBankAccounts()[0];
                // $result = $this->triggerPayout($creditorPaymentProfile->getIdentifier(), $creditorWallet, $creditorBankAccount, $creditor['amount']);
                // $treatedResult = $this->__treatReturn($debtor, $creditor, $result);
                // $return[] = $treatedResult;

                // trigger a payout to the creditor
                var_dump('payout to the creditor');
            }
        }

        exit;

        return [];
    }

    private function _createStripePrice(CarpoolPayment $carpoolPayment, User $user): ?StripePrice
    {
        $price = new Price(
            $this->currency,
            $carpoolPayment->getAmountOnline() * 100,
            $this->baseUri.'|'.$carpoolPayment->getId().'|'.$user->getId()
        );

        return $this->_createPrice($price);
    }

    private function _createPrice(Price $price): ?StripePrice
    {
        try {
            return $this->_stripe->prices->create($price->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createPaymentLink(PaymentLink $paymentLink): ?StripePaymentLink
    {
        try {
            return $this->_stripe->paymentLinks->create($paymentLink->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _deserializeBankAccount(StripeBankAccount $stripeBankAccount): BankAccount
    {
        $bankAccount = new BankAccount();
        $bankAccount->setId($stripeBankAccount->id);
        $bankAccount->setIban($stripeBankAccount->last4);
        $bankAccount->setStatus(in_array($stripeBankAccount->status, self::BANKACCCOUNT_STATUS_VALIDATED) ? BankAccount::STATUS_ACTIVE : BankAccount::STATUS_INACTIVE);
        $bankAccount->setUserLitteral($stripeBankAccount->account_holder_name);

        $bankAccount->setBic('');

        return $bankAccount;
    }

    private function _getUserIdentifier(?User $user): string
    {
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => !is_null($this->user) ? $this->user : $user, 'provider' => PaymentDataProvider::STRIPE]);

        $identifier = $paymentProfiles[0]->getIdentifier();
        if ($identifier) {
            return $identifier;
        }

        throw new PaymentException('No identifier found for the user');
    }

    /**
     * Update a User to the provider from a token.
     */
    private function _updateUserFromToken(string $tokenId): ?StripeAccount
    {
        try {
            return $this->_stripe->accounts->update($this->_getUserIdentifier(), ['account_token' => $tokenId]);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createToken(Token $token): ?StripeToken
    {
        try {
            return $this->_stripe->tokens->create($token->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createAccount(Account $account): ?StripeAccount
    {
        try {
            return $this->_stripe->accounts->create($account->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createExternalBankAccount(ExternalBankAccount $externalBankAccount): ?StripeBankAccount
    {
        try {
            return $this->_stripe->accounts->createExternalAccount($externalBankAccount->getStripeAccountId(), $externalBankAccount->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createFile(File $file): ?StripeFile
    {
        try {
            $fp = fopen($file->getFile(), 'r');
            $stripeFile = $this->_stripe->files->create([
                'purpose' => $file->getPurpose(),
                'file' => $fp,
            ]);
            fclose($fp);

            return $stripeFile;
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }
}
