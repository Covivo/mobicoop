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
 */

namespace App\DataProvider\Entity;

use App\DataProvider\Ressource\Hook;
use App\DataProvider\Ressource\MangoPayHook;
use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Entity\Wallet;
use App\Payment\Entity\WalletBalance;
use App\Payment\Exception\PaymentException;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Payment\Ressource\ValidationDocument;
use App\User\Entity\User;

/**
 * Payment Provider for MangoPay.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class MangoPayProvider implements PaymentProviderInterface
{
    public const SERVER_URL_SANDBOX = 'https://api.sandbox.mangopay.com/';
    public const SERVER_URL = 'https://api.mangopay.com/';
    public const LANDING_AFTER_PAYMENT = 'paiements/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE = '#/carpools/payment/paye';
    public const LANDING_AFTER_PAYMENT_MOBILE_SITE = '#/carpools/payment/paye';
    public const VERSION = 'V2.01';

    public const COLLECTION_BANK_ACCOUNTS = 'bankaccounts';
    public const COLLECTION_WALLETS = 'wallets';

    public const ITEM_USER_NATURAL = 'natural';
    public const ITEM_WALLET = 'wallets';
    public const ITEM_PAYIN = 'payins/card/web';
    public const ITEM_TRANSFERS = 'transfers';
    public const ITEM_PAYOUT = 'payouts/bankwire';

    public const ITEM_KYC_CREATE_DOC = 'users/{userId}/KYC/documents/';
    public const ITEM_KYC_CREATE_PAGE = 'users/{userId}/KYC/documents/{KYCDocId}/pages';
    public const ITEM_KYC_PUT_DOC = 'users/{userId}/KYC/documents/{KYCDocId}';

    public const CARD_TYPE = 'CB_VISA_MASTERCARD';
    public const LANGUAGE = 'FR';
    public const VALIDATION_DOC_TYPE = 'IDENTITY_PROOF';
    public const VALIDATION_ASKED = 'VALIDATION_ASKED';

    public const OUT_OF_DATE = 'OUT_OF_DATE';
    public const UNDERAGE_PERSON = 'UNDERAGE_PERSON';
    public const DOCUMENT_FALSIFIED = 'DOCUMENT_FALSIFIED';
    public const DOCUMENT_MISSING = 'DOCUMENT_MISSING';
    public const DOCUMENT_HAS_EXPIRED = 'DOCUMENT_HAS_EXPIRED';
    public const DOCUMENT_NOT_ACCEPTED = 'DOCUMENT_NOT_ACCEPTED';
    public const DOCUMENT_DO_NOT_MATCH_USER_DATA = 'DOCUMENT_DO_NOT_MATCH_USER_DATA';
    public const DOCUMENT_UNREADABLE = 'DOCUMENT_UNREADABLE';
    public const DOCUMENT_INCOMPLETE = 'DOCUMENT_INCOMPLETE';
    public const SPECIFIC_CASE = 'SPECIFIC_CASE';

    private $user;
    private $serverUrl;
    private $authChain;
    private $currency;
    private $paymentProfileRepository;
    private $validationDocsPath;
    private $baseUri;
    private $baseMobileUri;

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
        ($sandBoxMode) ? $this->serverUrl = self::SERVER_URL_SANDBOX : $this->serverUrl = self::SERVER_URL;
        $this->user = $user;
        $this->authChain = 'Basic '.base64_encode($clientId.':'.$apikey);
        $this->serverUrl .= self::VERSION.'/'.$clientId.'/';
        $this->currency = $currency;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->validationDocsPath = $validationDocsPath;
        $this->baseUri = $baseUri;
        $this->baseMobileUri = $baseMobileUri;
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
        $dataProvider = new DataProvider($this->serverUrl.'users/'.$paymentProfile->getIdentifier().'/', self::COLLECTION_BANK_ACCOUNTS);
        $getParams = [
            'per_page' => 100,
            'sort' => 'creationdate:desc',
        ];

        if ($onlyActive) {
            $getParams['Active'] = 'true';
        }

        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->getCollection($getParams, $headers);

        $bankAccounts = [];
        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            foreach ($data as $account) {
                $bankAccount = $this->deserializeBankAccount($account);
                $bankAccount->setValidationAskedDate($paymentProfile->getValidationAskedDate());
                $bankAccount->setValidatedDate($paymentProfile->getValidatedDate());
                $bankAccount->setValidationOutdatedDate($paymentProfile->getValidationOutdatedDate());
                $bankAccounts[] = $bankAccount;
            }
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
    public function addBankAccount(BankAccount $bankAccount)
    {
        // Build the body
        $body['OwnerName'] = $this->user->getGivenName().' '.$this->user->getFamilyName();
        $body['IBAN'] = $bankAccount->getIban();
        if (!null == $bankAccount->getBic()) {
            $body['BIC'] = $bankAccount->getBic();
        }

        $street = '';
        if ('' != $bankAccount->getAddress()->getStreetAddress()) {
            $street = $bankAccount->getAddress()->getStreetAddress();
        } else {
            $street = trim($bankAccount->getAddress()->getHouseNumber().' '.$bankAccount->getAddress()->getStreet());
        }

        $body['OwnerAddress'] = [
            'AddressLine1' => $street,
            'City' => $bankAccount->getAddress()->getAddressLocality(),
            'Region' => $bankAccount->getAddress()->getRegion(),
            'PostalCode' => $bankAccount->getAddress()->getPostalCode(),
            'Country' => substr($bankAccount->getAddress()->getCountryCode(), 0, 2),
        ];

        // Get the identifier
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $this->user]);
        $identifier = $paymentProfiles[0]->getIdentifier();

        if (is_null($identifier)) {
            throw new PaymentException(PaymentException::NO_IDENTIFIER);
        }

        $dataProvider = new DataProvider($this->serverUrl.'users/'.$identifier.'/', self::COLLECTION_BANK_ACCOUNTS.'/iban');
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $bankAccount = $this->deserializeBankAccount($data);
        } else {
            throw new PaymentException(PaymentException::ERROR_CREATING);
        }

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
        // Build the body
        $body['Active'] = 'false';

        // Get the identifier
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $this->user]);
        $identifier = $paymentProfiles[0]->getIdentifier();

        if (is_null($identifier)) {
            throw new PaymentException(PaymentException::NO_IDENTIFIER);
        }

        $dataProvider = new DataProvider($this->serverUrl.'users/'.$identifier.'/', self::COLLECTION_BANK_ACCOUNTS.'/'.$bankAccount->getId());
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->putItem($body, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $bankAccount = $this->deserializeBankAccount($data);
        }

        return $bankAccount;
    }

    /**
     * Returns a collection of Wallet.
     *
     * @param PaymentProfile $paymentProfile The User's payment profile related to the wallets
     *
     * @return Wallet[]
     */
    public function getWallets(PaymentProfile $paymentProfile)
    {
        $wallets = [new Wallet()];

        $dataProvider = new DataProvider($this->serverUrl.'users/'.$paymentProfile->getIdentifier().'/', self::COLLECTION_WALLETS);
        $getParams = null;
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->getCollection($getParams, $headers);

        $wallets = [];
        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            foreach ($data as $wallet) {
                $wallet = $this->deserializeWallet($wallet);
                $wallet->setOwnerIdentifier($paymentProfile->getIdentifier());
                $wallets[] = $wallet;
            }
        }

        return $wallets;
    }

    /**
     * Add a Wallet.
     *
     * @param Wallet $user The Wallet to create
     *
     * @return null|Wallet
     */
    public function addWallet(Wallet $wallet): Wallet
    {
        // Build the body
        $body['Description'] = $wallet->getDescription();
        $body['Currency'] = $wallet->getCurrency();
        $body['Tag'] = $wallet->getComment();
        $body['Owners'] = [$wallet->getOwnerIdentifier()];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_WALLET);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        $wallet = new Wallet();
        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $wallet->setId($data['Id']);
            $wallet->setDescription($data['Description']);
            $wallet->setOwnerIdentifier($data['Owners'][0]);
        } else {
            throw new PaymentException(PaymentException::ADD_WALLET_USER_FAILED);
        }

        return $wallet;
    }

    /**
     * Register a User to the provider and create a PaymentProfile.
     *
     * @param null|Address $address The address to use to the registration
     *
     * @return string The identifier
     */
    public function registerUser(User $user, Address $address = null)
    {
        // Build the body
        $body['FirstName'] = $user->getGivenName();
        $body['LastName'] = $user->getFamilyName();
        $body['Email'] = $user->getEmail();

        if (is_null($user->getBirthDate())) {
            throw new PaymentException(PaymentException::NO_BIRTHDATE);
        }
        $body['Birthday'] = (int) $user->getBirthDate()->format('U');

        if (is_null($address)) {
            // Address of the user
            foreach ($user->getAddresses() as $homeAddress) {
                if ($homeAddress->isHome()) {
                    $address = $homeAddress;

                    break;
                }
            }
        }

        if (!is_null($address)) {
            $street = '';
            if ('' != $address->getStreetAddress()) {
                $street = $address->getStreetAddress();
            } else {
                $street = trim($address->getHouseNumber().' '.$address->getStreet());
            }

            $body['Address'] = [
                'AddressLine1' => $street,
                'City' => $address->getAddressLocality(),
                'Region' => $address->getRegion(),
                'PostalCode' => $address->getPostalCode(),
                'Country' => substr($address->getCountryCode(), 0, 2),
            ];

            if (
                ('' == $address->getStreetAddress() && '' == $address->getStreet())
                || '' == $address->getAddressLocality()
                || '' == $address->getRegion()
                || '' == $address->getPostalCode()
                || '' == $address->getCountryCode()
            ) {
                throw new PaymentException(PaymentException::ADDRESS_INVALID);
            }

            $body['Nationality'] = substr($address->getCountryCode(), 0, 2);
            $body['CountryOfResidence'] = substr($address->getCountryCode(), 0, 2);
        } else {
            throw new PaymentException(PaymentException::NO_ADDRESS);
        }

        $body['KYCLevel'] = 'LIGHT';
        $body['TermsAndConditionsAccepted'] = true;
        $body['UserCategory'] = 'OWNER';

        $dataProvider = new DataProvider($this->serverUrl.'users/', self::ITEM_USER_NATURAL);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
        }

        return $data['Id'];
    }

    /**
     * Update a User to the provider and create a PaymentProfile.
     *
     * @return string The identifier
     */
    public function updateUser(User $user)
    {
        // We check first if the user have an identifier
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $this->user]);
        $identifier = $paymentProfiles[0]->getIdentifier();

        if (is_null($identifier)) {
            throw new PaymentException(PaymentException::NO_IDENTIFIER);
        }

        // Build the body
        $body['FirstName'] = $user->getGivenName();
        $body['LastName'] = $user->getFamilyName();
        $body['Email'] = $user->getEmail();

        if (is_null($user->getBirthDate())) {
            throw new PaymentException(PaymentException::NO_BIRTHDATE);
        }
        $body['Birthday'] = (int) $user->getBirthDate()->format('U');

        $body['KYCLevel'] = 'LIGHT';

        $dataProvider = new DataProvider($this->serverUrl.'users/', self::ITEM_USER_NATURAL.'/'.$identifier);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->putItem($body, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::UPDATE_USER_FAILED);
        }

        return $data['Id'];
    }

    /**
     * Get a User to the provider.
     */
    public function getUser(int $identifier)
    {
        $dataProvider = new DataProvider($this->serverUrl.'users/'.$identifier);
        $headers = [
            'Authorization' => $this->authChain,
        ];

        $response = $dataProvider->getCollection(null, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
        }

        return $data;
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
            $wallet = new Wallet();
            $wallet->setComment('');
            $wallet->setCurrency($this->currency);
            $wallet->setDescription('From Mobicoop');
            $wallet->setOwnerIdentifier($identifier);
            $wallet = $this->addWallet($wallet);
            $carpoolPayment->setCreateCarpoolProfileIdentifier($identifier); // To persist the paymentProfile in PaymentManager
        } else {
            $identifier = $paymentProfiles[0]->getIdentifier();
            $wallet = $this->getWallets($paymentProfiles[0])[0];
        }

        $returnUrl = $this->baseUri.''.self::LANDING_AFTER_PAYMENT;
        if (CarpoolPayment::ORIGIN_MOBILE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        } elseif (CarpoolPayment::ORIGIN_MOBILE_SITE == $carpoolPayment->getOrigin()) {
            $returnUrl = $this->baseMobileUri.self::LANDING_AFTER_PAYMENT_MOBILE;
        }

        $body = [
            'AuthorId' => $identifier,
            'DebitedFunds' => [
                'Currency' => $this->currency,
                'Amount' => (int) ($carpoolPayment->getAmountOnline() * 100),
            ],
            'Fees' => [
                'Currency' => $this->currency,
                'Amount' => 0,
            ],
            'CreditedWalletId' => $wallet->getId(),
            'ReturnURL' => $returnUrl.'?paymentPaymentId='.$carpoolPayment->getId(),
            'CardType' => self::CARD_TYPE,
            'Culture' => self::LANGUAGE,
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_PAYIN);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::GET_URL_PAYIN_FAILED);
        }

        $carpoolPayment->setTransactionid($data['Id']);
        $carpoolPayment->setRedirectUrl($data['RedirectURL']);
        $carpoolPayment->setTransactionPostData($carpoolPayment->getTransactionPostData().((!is_null($carpoolPayment->getTransactionPostData())) ? '|' : '').json_encode($body));

        return $carpoolPayment;
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
    public function processElectronicPayment(User $debtor, array $creditors): array
    {
        $return = [];

        // Get the wallet of the debtor and his identifier
        $debtorPaymentProfile = $this->paymentProfileRepository->find($debtor->getPaymentProfileId());

        // Transfer to the creditors wallets and payout
        foreach ($creditors as $creditor) {
            $creditorWallet = $creditor['user']->getWallets()[0];
            $return[] = $this->transferWalletToWallet($debtorPaymentProfile->getIdentifier(), $debtorPaymentProfile->getWallets()[0], $creditorWallet, $creditor['amount']);

            // Do the payout to the default bank account
            $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());
            $creditorBankAccount = $creditor['user']->getBankAccounts()[0];
            $return[] = $this->triggerPayout($creditorPaymentProfile->getIdentifier(), $creditorWallet, $creditorBankAccount, $creditor['amount']);
        }

        return $return;
    }

    /**
     * Transfer founds bewteen two wallets.
     *
     * @param int    $debtorIdentifier MangoPay's identifier of the debtor
     * @param Wallet $walletFrom       Wallet of the debtor
     * @param Wallet $walletTo         Wallet of the creditor
     * @param float  $amount           Amount of the transaction
     */
    public function transferWalletToWallet(int $debtorIdentifier, Wallet $walletFrom, Wallet $walletTo, float $amount, string $tag = ''): ?string
    {
        $body = [
            'AuthorId' => $debtorIdentifier,
            'DebitedFunds' => [
                'Currency' => $this->currency,
                'Amount' => (int) ($amount * 100),
            ],
            'Fees' => [
                'Currency' => $this->currency,
                'Amount' => 0,
            ],
            'DebitedWalletId' => $walletFrom->getId(),
            'CreditedWalletId' => $walletTo->getId(),
            'Tag' => $tag,
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_TRANSFERS);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        if (200 == $response->getCode()) {
            return $response->getValue();
        }

        return null;
    }

    /**
     * Trigger a payout from a Wallet to a Bank Account.
     */
    public function triggerPayout(int $authorIdentifier, Wallet $wallet, BankAccount $bankAccount, float $amount, string $reference = ''): ?string
    {
        $body = [
            'AuthorId' => $authorIdentifier,
            'DebitedFunds' => [
                'Currency' => $this->currency,
                'Amount' => (int) ($amount * 100),
            ],
            'Fees' => [
                'Currency' => $this->currency,
                'Amount' => 0,
            ],
            'DebitedWalletId' => $wallet->getId(),
            'BankAccountId' => $bankAccount->getId(),
            'BankWireRef' => $reference,
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_PAYOUT);
        $headers = [
            'Authorization' => $this->authChain,
        ];
        $response = $dataProvider->postCollection($body, $headers);

        if (200 == $response->getCode()) {
            return $response->getValue();
        }

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
        switch ($hook->getEventType()) {
            case MangoPayHook::PAYIN_SUCCEEDED:
            case MangoPayHook::VALIDATION_SUCCEEDED:
                $hook->setStatus(Hook::STATUS_SUCCESS);

                break;

            case MangoPayHook::VALIDATION_OUTDATED:
                $hook->setStatus(Hook::STATUS_OUTDATED_RESSOURCE);

                break;

            default:
                $hook->setStatus(Hook::STATUS_FAILED);
        }

        return $hook;
    }

    /**
     * Upload an identity validation document
     * The document is not stored on the platform. It has to be deleted.
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument): ValidationDocument
    {
        $user = $validationDocument->getUser();
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user' => $user]);
        if (is_null($paymentProfiles) || 0 == count($paymentProfiles)) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }
        $identifier = $paymentProfiles[0]->getIdentifier();

        // $fileContent = base64_encode(file_get_contents(self::VALIDATION_DOCUMENTS_PATH."".$validationDocument->getFileName()));

        // General header for all 3 requests
        $headers = [
            'Authorization' => $this->authChain,
        ];

        // Creation of the doc
        $urlPost = str_replace('{userId}', $identifier, self::ITEM_KYC_CREATE_DOC);
        $body = [
            'Type' => self::VALIDATION_DOC_TYPE,
            'Tag' => 'Automatic',
        ];
        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->postCollection($body, $headers);
        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $docId = $data['Id'];
        } else {
            throw new PaymentException(PaymentException::ERROR_CREATING_DOC_TO_PROVIDER);
        }

        // Creation of pages
        $urlPost = str_replace('{KYCDocId}', $docId, str_replace('{userId}', $identifier, self::ITEM_KYC_CREATE_PAGE));

        $body = [
            'File' => base64_encode(file_get_contents($this->validationDocsPath.''.$validationDocument->getFileName())),
        ];
        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->postCollection($body, $headers);
        if (204 !== $response->getCode()) {
            throw new PaymentException(PaymentException::ERROR_CREATING_DOC_PAGE_TO_PROVIDER);
        }

        // Asking validation
        $urlPost = str_replace('{KYCDocId}', $docId, str_replace('{userId}', $identifier, self::ITEM_KYC_PUT_DOC));

        $body = [
            'Status' => self::VALIDATION_ASKED,
        ];

        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->putItem($body, $headers);
        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            if (self::VALIDATION_ASKED !== $data['Status']) {
                throw new PaymentException(PaymentException::ERROR_VALIDATION_ASK_DOC_BAD_STATUS);
            }
            $validationDocument->setIdentifier($docId);
        } else {
            throw new PaymentException(PaymentException::ERROR_VALIDATION_ASK_DOC);
        }

        return $validationDocument;
    }

    /**
     * Deserialize a BankAccount.
     *
     * @param array $account The account to deserialize
     */
    public function deserializeBankAccount(array $account): BankAccount
    {
        $bankAccount = new BankAccount();
        $bankAccount->setId($account['Id']);
        $bankAccount->setUserLitteral($account['OwnerName']);
        $bankAccount->setIban($account['IBAN']);
        $bankAccount->setBic($account['BIC']);
        $bankAccount->setCreatedDate(\DateTime::createFromFormat('U', $account['CreationDate']));
        $bankAccount->setComment($account['Tag']);
        $bankAccount->setStatus($account['Active']);

        if (!empty($account['OwnerAddress'])) {
            $address = new Address();
            $streetAddress = $account['OwnerAddress']['AddressLine1'];
            if ('' !== trim($account['OwnerAddress']['AddressLine2'])) {
                $streetAddress .= ' '.$account['OwnerAddress']['AddressLine2'];
            }

            $address->setStreetAddress($streetAddress);
            $address->setAddressLocality($account['OwnerAddress']['City']);
            $address->setRegion($account['OwnerAddress']['Region']);
            $address->setPostalCode($account['OwnerAddress']['PostalCode']);
            $address->setCountryCode($account['OwnerAddress']['Country']);

            $bankAccount->setAddress($address);
        }

        return $bankAccount;
    }

    /**
     * Deserialize a Wallet.
     *
     * @param array $data The wallet to deserialize
     */
    public function deserializeWallet(array $data): Wallet
    {
        $wallet = new Wallet();
        $wallet->setId($data['Id']);
        $wallet->setDescription($data['Description']);
        $wallet->setComment($data['Tag']);
        $wallet->setCreatedDate(\DateTime::createFromFormat('U', $data['CreationDate']));
        $wallet->setCurrency($data['Currency']);

        $balance = new WalletBalance();
        $balance->setCurrency($data['Balance']['Currency']);
        $balance->setAmount($data['Balance']['Amount']);
        $wallet->setBalance($balance);

        // Get the Users matching the Owners of this wallet
        $paymentProfiles = [];
        foreach ($data['Owners'] as $owner) {
            $paymentProfile = $this->paymentProfileRepository->findOneBy(['identifier' => $owner]);
            if (!is_null($paymentProfile)) {
                $paymentProfiles[] = $paymentProfile;
            }
        }

        return $wallet;
    }

    public function getDocument($validationDocumentId)
    {
        $dataProvider = new DataProvider($this->serverUrl.'kyc/documents/'.$validationDocumentId.'/');
        $headers = [
            'Authorization' => $this->authChain,
        ];

        $validationDocument = new ValidationDocument();
        $response = $dataProvider->getCollection(null, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);

            switch ($data['RefusedReasonType']) {
                case self::OUT_OF_DATE:
                    $validationDocument->setStatus(ValidationDocument::OUT_OF_DATE);

                    break;

                case self::UNDERAGE_PERSON:
                    $validationDocument->setStatus(ValidationDocument::UNDERAGE_PERSON);

                    break;

                case self::DOCUMENT_FALSIFIED:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_FALSIFIED);

                    break;

                case self::DOCUMENT_MISSING:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_MISSING);

                    break;

                case self::DOCUMENT_HAS_EXPIRED:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_HAS_EXPIRED);

                    break;

                case self::DOCUMENT_NOT_ACCEPTED:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_NOT_ACCEPTED);

                    break;

                case self::DOCUMENT_DO_NOT_MATCH_USER_DATA:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_DO_NOT_MATCH_USER_DATA);

                    break;

                case self::DOCUMENT_UNREADABLE:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_UNREADABLE);

                    break;

                case self::DOCUMENT_INCOMPLETE:
                    $validationDocument->setStatus(ValidationDocument::DOCUMENT_INCOMPLETE);

                    break;

                case self::SPECIFIC_CASE:
                    $validationDocument->setStatus(ValidationDocument::SPECIFIC_CASE);

                    break;
            }
        } else {
            throw new PaymentException(PaymentException::ERROR_DOC);
        }

        return $validationDocument;
    }

    public function getKycDocument(int $kycDocumentId)
    {
        $dataProvider = new DataProvider($this->serverUrl.'kyc/documents/'.$kycDocumentId.'/');
        $headers = [
            'Authorization' => $this->authChain,
        ];

        $response = $dataProvider->getCollection(null, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);

            if (isset($data['Status']) && !is_null($data['Status'])) {
                return $data;
            }
        } else {
            throw new PaymentException(PaymentException::ERROR_DOC);
        }
    }
}
