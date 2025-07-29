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
use App\DataProvider\Entity\Stripe\Entity\Transfer;
use App\DataProvider\Ressource\Hook;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\PaymentResult;
use App\Payment\Entity\Wallet;
use App\Payment\Exception\PaymentException;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Payment\Ressource\ValidationDocument;
use App\Payment\Service\PaymentDataProvider;
use App\Payment\Service\PaymentManager;
use App\User\Entity\User;
use Stripe\Account as StripeAccount;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\Exception\ApiErrorException;
use Stripe\File as StripeFile;
use Stripe\PaymentLink as StripePaymentLink;
use Stripe\Payout as StripePayout;
use Stripe\Price as StripePrice;
use Stripe\StripeClient;
use Stripe\Token as StripeToken;
use Stripe\Transfer as StripeTransfer;

/**
 * Payment Provider for Stripe.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class StripeProvider implements PaymentProviderInterface
{
    public const SERVER_URL = 'https://api.stripe.com/';
    public const LANDING_AFTER_PAYMENT = 'paiements/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE = '#/carpools/payment/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE_SITE = '#/carpools/payment/paye';
    public const VERSION = 'v1';

    public const BANKACCCOUNT_STATUS_VALIDATED = ['validated', 'new', 'verified'];
    public const BANKACCCOUNT_STATUS_INACTIVE = 'inactive';

    private const STRIPE_API_VERSION = '2025-02-24.acacia';

    private const IDENTITY_VERIFED = 'verified';
    private const IDENTITY_UNVERIFIED = 'unverified';

    private const OUT_OF_DATE = 'document_expired';
    private const DOCUMENT_FALSIFIED = 'document_fraudulent';
    private const DOCUMENT_MISSING_FRONT = 'document_missing_front';
    private const DOCUMENT_MISSING_BACK = 'document_missing_back';
    private const DOCUMENT_HAS_EXPIRED = 'document_expired';
    private const DOCUMENT_NOT_ACCEPTED = 'document_type_not_supported';
    private const DOCUMENT_DO_NOT_MATCH_USER_DATA = 'document_invalid';
    private const DOCUMENT_UNREADABLE = 'document_not_readable';
    private const DOCUMENT_INCOMPLETE = 'document_incomplete';
    private const DOCUMENT_FAILED_OTHER_CASE = 'document_failed_other';
    private const DOCUMENT_FAILED_COPY = 'document_failed_copy';
    private const DOCUMENT_DOB_MISMATCH = 'document_dob_mismatch';

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
    private $_stripePublicKey;

    public function __construct(
        ?User $user,
        string $clientId,
        string $apikey,
        string $publicApikey,
        bool $sandBoxMode,
        string $currency,
        string $validationDocsPath,
        string $baseUri,
        string $baseMobileUri,
        string $sandBoxReturnUrl,
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
        $this->baseUri = (!$sandBoxMode) ? $baseUri : $sandBoxReturnUrl;
        $this->baseMobileUri = $baseMobileUri;

        $expectedPrefix = $sandBoxMode ? 'sk_test' : 'sk_live';

        if (0 !== strpos($apikey, $expectedPrefix)) {
            throw new PaymentException('Invalid API key');
        }

        $this->_stripe = new StripeClient([
            'api_key' => $apikey,
            'stripe_version' => self::STRIPE_API_VERSION,
        ]);
        $this->_stripePublicKey = new StripeClient([
            'api_key' => $publicApikey,
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

            if ($onlyActive && BankAccount::STATUS_ACTIVE !== $bankAccount->getStatus()) {
                continue;
            }
            $bankAccounts[] = $bankAccount;
        }

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
        $this->_disableAccount($bankAccount->getUserIdentifier(), $bankAccount->getId());

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
        $accountToken = new AccountToken($user);
        $stripeToken = $this->_createToken($accountToken);
        $return = $this->_updateUserFromToken($stripeToken->id);

        return $return->id;
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
        $carpoolPayment->setTransactionPostData($carpoolPayment->getTransactionPostData().((!is_null($carpoolPayment->getTransactionPostData())) ? '|' : '').json_encode($stripePrice));

        return $carpoolPayment;
    }

    /**
     * Get a User to the provider.
     */
    public function getUser(string $identifier)
    {
        try {
            return $this->_stripe->accounts->retrieve($identifier, []);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }
    }

    public function getIdentityValidationStatus($userPaymentProfile): array
    {
        if (isset($userPaymentProfile['individual']['verification']['status'])) {
            switch ($userPaymentProfile['individual']['verification']['status']) {
                case self::IDENTITY_VERIFED:
                    $kycDocument['Status'] = PaymentManager::KYC_DOCUMENT_VALIDATED;

                    break;

                case self::IDENTITY_UNVERIFIED:
                    $kycDocument['Status'] = PaymentManager::KYC_DOCUMENT_REFUSED;

                    $detailsCode = $userPaymentProfile['individual']['verification']['document']['details_code'] ?? $userPaymentProfile['individual']['verification']['details_code'];

                    switch ($detailsCode) {
                        case self::OUT_OF_DATE:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::OUT_OF_DATE;

                            break;

                        case self::DOCUMENT_FALSIFIED:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_FALSIFIED;

                            break;

                        case self::DOCUMENT_MISSING_FRONT:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_MISSING_FRONT;

                            break;

                        case self::DOCUMENT_MISSING_BACK:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_MISSING_BACK;

                            break;

                        case self::DOCUMENT_HAS_EXPIRED:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_HAS_EXPIRED;

                            break;

                        case self::DOCUMENT_NOT_ACCEPTED:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_NOT_ACCEPTED;

                            break;

                        case self::DOCUMENT_DO_NOT_MATCH_USER_DATA:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_DO_NOT_MATCH_USER_DATA;

                            break;

                        case self::DOCUMENT_UNREADABLE:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_UNREADABLE;

                            break;

                        case self::DOCUMENT_INCOMPLETE:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_INCOMPLETE;

                            break;

                        case self::DOCUMENT_FAILED_OTHER_CASE:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_FAILED_OTHER_CASE;

                            break;

                        case self::DOCUMENT_DOB_MISMATCH:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_DOB_MISMATCH;

                            break;

                        case self::DOCUMENT_FAILED_COPY:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_FAILED_COPY;

                            break;

                        default:
                            $kycDocument['RefusedReasonType'] = PaymentProfile::DOCUMENT_FAILED_OTHER_CASE;
                    }

                    break;

                default:
                    $kycDocument['Status'] = '';

                    break;
            }
            $kycDocument['Id'] = isset($userPaymentProfile['individual']['verification']['document']['front']) ? $userPaymentProfile['individual']['verification']['document']['front'] : '';
        }

        return $kycDocument;
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
     *
     * @param null|mixed $carpoolPaymentId
     */
    public function processElectronicPayment(?User $debtor = null, array $creditors, string $tag, $carpoolPaymentId = null): array
    {
        foreach ($creditors as $creditor) {
            $stripeTransfer = $this->_stripeTransferToCreditor($creditor, $carpoolPaymentId);
            $treatedResult = $this->_treatReturn($debtor, $creditor, $stripeTransfer);
            $return[] = $treatedResult;

            $stripeTransfer = $this->_stripePayoutToCreditor($creditor);
            $treatedResult = $this->_treatReturn($debtor, $creditor, $stripeTransfer);
            $return[] = $treatedResult;
        }

        return $return;
    }

    public function getBalance(?PaymentProfile $paymentProfile = null): int
    {
        try {
            $balance = $this->_stripe->balance->retrieve([]);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        if (isset($balance->available[0])) {
            return $balance->available[0]->amount;
        }

        return 0;
    }

    /**
     * Trigger a payout from a Wallet to a Bank Account.
     */
    public function triggerPayout(string $authorIdentifier, Wallet $wallet, BankAccount $bankAccount, float $amount, string $reference = ''): ?string
    {
        return null;
    }

    /**
     * Handle a payment web hook. (useless in Stripe context).
     *
     * @var Hook The mangopay hook
     *
     * @return Hook with status and transaction id
     */
    public function handleHook(Hook $hook): Hook
    {
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

        $stripefile2 = null;
        if (!is_null($validationDocument->getFile2())) {
            $stripefile2 = $this->_createFile($file);
        }

        $accountToken = new AccountToken($validationDocument->getUser());
        $accountToken->setValidationDocumentFrontId($stripefile->id);

        if (!is_null($stripefile2) && isset($stripefile2->id)) {
            $accountToken->setValidationDocumentBackId($stripefile2->id);
        }

        $stripeToken = $this->_createToken($accountToken);
        $return = $this->_updateUserFromToken($stripeToken->id);

        if (isset($return['individual']['verification']['document']['front'])) {
            $validationDocument->setIdentifier($return['individual']['verification']['document']['front']);
        }

        return $validationDocument;
    }

    public function getDocument($validationDocumentId, $status = '')
    {
        $validationDocument = new ValidationDocument();

        $validationDocument->setId($validationDocumentId);

        switch ($status) {
            case self::OUT_OF_DATE:
                $validationDocument->setStatus(PaymentProfile::OUT_OF_DATE);

                break;

            case self::DOCUMENT_FALSIFIED:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_FALSIFIED);

                break;

            case self::DOCUMENT_MISSING_FRONT:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_MISSING_FRONT);

                break;

            case self::DOCUMENT_MISSING_BACK:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_MISSING_BACK);

                break;

            case self::DOCUMENT_HAS_EXPIRED:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_HAS_EXPIRED);

                break;

            case self::DOCUMENT_NOT_ACCEPTED:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_NOT_ACCEPTED);

                break;

            case self::DOCUMENT_DO_NOT_MATCH_USER_DATA:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_DO_NOT_MATCH_USER_DATA);

                break;

            case self::DOCUMENT_UNREADABLE:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_UNREADABLE);

                break;

            case self::DOCUMENT_INCOMPLETE:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_INCOMPLETE);

                break;

            case self::DOCUMENT_FAILED_OTHER_CASE:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_FAILED_OTHER_CASE);

                break;

            case self::DOCUMENT_DOB_MISMATCH:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_DOB_MISMATCH);

                break;

            case self::DOCUMENT_FAILED_COPY:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_FAILED_COPY);

                break;

            default:
                $validationDocument->setStatus(PaymentProfile::DOCUMENT_FAILED_OTHER_CASE);
        }

        return $validationDocument;
    }

    public function getKycDocument(string $kycDocumentId)
    {
        return [];
    }

    public function getWallets(PaymentProfile $paymentProfile)
    {
        return [];
    }

    public function processAsyncElectronicPayment(User $debtor, array $creditors, ?int $carpoolPaymentId): array
    {
        $return = [];

        foreach ($creditors as $creditor) {
            if (CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE == $creditor['debtorStatus']) {
                // do the transfert to the creditor
                $stripeTransfer = $this->_stripeTransferToCreditor($creditor, $carpoolPaymentId);
                $treatedResult = $this->_treatReturn($debtor, $creditor, $stripeTransfer);
                $return[] = $treatedResult;
            }

            if (CarpoolItem::DEBTOR_STATUS_ONLINE == $creditor['debtorStatus']) {
                // trigger a payout to the creditor
                $stripeTransfer = $this->_stripePayoutToCreditor($creditor);
                $treatedResult = $this->_treatReturn($debtor, $creditor, $stripeTransfer);
                $return[] = $treatedResult;
            }
        }

        return $return;
    }

    private function _disableAccount(string $accountId, string $bankAccountId)
    {
        try {
            return $this->_stripe->accounts->updateExternalAccount(
                $accountId,
                $bankAccountId,
                ['metadata' => ['status' => 'inactive']]
            );
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createStripePaymentLink(CarpoolPayment $carpoolPayment, StripePrice $stripePrice): ?StripePaymentLink
    {
        $returnUrl = $this->baseUri.''.self::LANDING_AFTER_PAYMENT;
        if (CarpoolPayment::ORIGIN_MOBILE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        } elseif (CarpoolPayment::ORIGIN_MOBILE_SITE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        }

        $paymentLink = new PaymentLink([$stripePrice->id], $returnUrl.'?paymentPaymentId='.$carpoolPayment->getId());

        return $this->_createPaymentLink($paymentLink);
    }

    private function _treatReturn(?User $debtor, array $creditor, object $stripeReturn): PaymentResult
    {
        $return = new PaymentResult();
        if (!is_null($debtor)) {
            $return->setDebtorId($debtor->getId());
        }
        $return->setCreditorId($creditor['user']->getId());
        if (isset($creditor['carpoolItemId'])) {
            $return->setCarpoolItemId($creditor['carpoolItemId']);
        }

        $return->setStatus(PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_FAILED);

        if ($stripeReturn instanceof StripeTransfer) {
            $return->setType(PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_TRANSFER);
            $return->setStatus(PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_SUCCESS);
        }
        if ($stripeReturn instanceof StripePayout) {
            $return->setType(PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_PAYOUT);
            $return->setStatus(PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_SUCCESS);
        }

        return $return;
    }

    private function _stripePayoutToCreditor(array $creditor): ?StripePayout
    {
        $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());

        try {
            return $this->_stripe->payouts->create([
                'amount' => $creditor['amount'] * 100, // Montant en centimes
                'currency' => $this->currency,
            ], [
                'stripe_account' => $creditorPaymentProfile->getIdentifier(),
            ]);
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _stripeTransferToCreditor(array $creditor, $carpoolPaymentId = null): ?StripeTransfer
    {
        $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());

        $transfert = new Transfer($this->currency, $creditor['amount'] * 100, $creditorPaymentProfile->getIdentifier(), $carpoolPaymentId);

        try {
            return $this->_stripe->transfers->create($transfert->buildBody());
        } catch (ApiErrorException $e) {
            throw new PaymentException($e->getMessage());
        }

        return null;
    }

    private function _createStripePrice(CarpoolPayment $carpoolPayment, User $user): ?StripePrice
    {
        $price = new Price(
            $this->currency,
            $carpoolPayment->getAmountOnline() * 100,
            $this->baseUri
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
        if (self::BANKACCCOUNT_STATUS_INACTIVE == $stripeBankAccount->metadata->status) {
            $bankAccount->setStatus(BankAccount::STATUS_INACTIVE);
        } else {
            $bankAccount->setStatus(in_array($stripeBankAccount->status, self::BANKACCCOUNT_STATUS_VALIDATED) ? BankAccount::STATUS_ACTIVE : BankAccount::STATUS_INACTIVE);
        }
        $bankAccount->setUserLitteral($stripeBankAccount->account_holder_name);

        $bankAccount->setBic('');

        return $bankAccount;
    }

    private function _getUserIdentifier(?User $user = null): string
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
            return $this->_stripePublicKey->tokens->create($token->buildBody());
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
