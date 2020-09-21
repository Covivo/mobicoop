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

use App\DataProvider\Ressource\Hook;
use App\DataProvider\Ressource\MangoPayHook;
use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\Address;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Ressource\BankAccount;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Exception\PaymentException;
use App\Payment\Entity\Wallet;
use App\Payment\Entity\WalletBalance;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\ValidationDocument;
use App\User\Entity\User;
use LogicException;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;

/**
 * Payment Provider for MangoPay
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MangoPayProvider implements PaymentProviderInterface
{
    const SERVER_URL_SANDBOX = "https://api.sandbox.mangopay.com/";
    const SERVER_URL = "https://api.mangopay.com/";
    const VERSION = "V2.01";

    const COLLECTION_BANK_ACCOUNTS = "bankaccounts";
    const COLLECTION_WALLETS = "wallets";

    const ITEM_USER_NATURAL = "natural";
    const ITEM_WALLET = "wallets";
    const ITEM_PAYIN = "payins/card/web";
    const ITEM_TRANSFERS = "transfers";
    const ITEM_PAYOUT = "payouts/bankwire";
    
    const ITEM_KYC_CREATE_DOC = "users/{userId}/KYC/documents/";
    const ITEM_KYC_CREATE_PAGE = "users/{userId}/KYC/documents/{KYCDocId}/pages";
    const ITEM_KYC_PUT_DOC = "users/{userId}/KYC/documents/{KYCDocId}";

    const CARD_TYPE = "CB_VISA_MASTERCARD";
    const LANGUAGE = "FR";
    const VALIDATION_DOC_TYPE = "IDENTITY_PROOF";
    const VALIDATION_ASKED = "VALIDATION_ASKED";

    private $user;
    private $serverUrl;
    private $authChain;
    private $currency;
    private $paymentProfileRepository;
    private $validationDocsPath;

    public function __construct(
        ?User $user,
        string $clientId,
        string $apikey,
        bool $sandBoxMode,
        string $currency,
        string $validationDocsPath,
        PaymentProfileRepository $paymentProfileRepository
    ) {
        ($sandBoxMode) ? $this->serverUrl = self::SERVER_URL_SANDBOX : $this->serverUrl = self::SERVER_URL;
        $this->user = $user;
        $this->authChain = "Basic ".base64_encode($clientId.":".$apikey);
        $this->serverUrl .= self::VERSION."/".$clientId."/";
        $this->currency = $currency;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->validationDocsPath = $validationDocsPath;
    }
    
    /**
     * Returns a collection of Bank accounts.
     *
     * @param PaymentProfile $paymentProfile     The User's payment profile related to the Bank accounts
     * @param bool $onlyActive     By default, only the active bank accounts are returned
     * @return BankAccount[]
     */
    public function getBankAccounts(PaymentProfile $paymentProfile, bool $onlyActive = true)
    {
        $dataProvider = new DataProvider($this->serverUrl."users/".$paymentProfile->getIdentifier()."/", self::COLLECTION_BANK_ACCOUNTS);
        $getParams = [
            "per_page" => 100,
            "sort" => "creationdate:desc"
        ];

        if ($onlyActive) {
            $getParams['Active'] = "true";
        }

        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->getCollection($getParams, $headers);
        
        $bankAccounts = [];
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            foreach ($data as $account) {
                $bankAccounts[] = $this->deserializeBankAccount($account);
            }
        }
        return $bankAccounts;
    }
       
    /**
     * Add a BankAccount
     *
     * @param BankAccount $bankAccount    The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(BankAccount $bankAccount)
    {
        // Build the body
        $body['OwnerName'] = $this->user->getGivenName()." ".$this->user->getFamilyName();
        $body['IBAN'] = $bankAccount->getIban();
        $body['BIC'] = $bankAccount->getBic();

        $body['OwnerAddress'] = [
            "AddressLine1" => $bankAccount->getAddress()->getStreetAddress(),
            "City" => $bankAccount->getAddress()->getAddressLocality(),
            "Region" => $bankAccount->getAddress()->getRegion(),
            "PostalCode" => $bankAccount->getAddress()->getPostalCode(),
            "Country" => substr($bankAccount->getAddress()->getCountryCode(), 0, 2)
        ];

        // Get the identifier
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$this->user]);
        $identifier = $paymentProfiles[0]->getIdentifier();
        
        if (is_null($identifier)) {
            throw new PaymentException(PaymentException::NO_IDENTIFIER);
        }

        $dataProvider = new DataProvider($this->serverUrl."users/".$identifier."/", self::COLLECTION_BANK_ACCOUNTS."/iban");
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            $bankAccount = $this->deserializeBankAccount($data);
        }
        return $bankAccount;
    }

    /**
     * Disable a BankAccount (Only IBAN/BIC and active/inactive)
     *
     * @param BankAccount $bankAccount  The BankAccount to disable
     * @return BankAccount|null
     */
    public function disableBankAccount(BankAccount $bankAccount)
    {
        // Build the body
        $body['Active'] = "false";

        // Get the identifier
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$this->user]);
        $identifier = $paymentProfiles[0]->getIdentifier();
        
        if (is_null($identifier)) {
            throw new PaymentException(PaymentException::NO_IDENTIFIER);
        }

        $dataProvider = new DataProvider($this->serverUrl."users/".$identifier."/", self::COLLECTION_BANK_ACCOUNTS."/".$bankAccount->getId());
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->putItem($body, $headers);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            $bankAccount = $this->deserializeBankAccount($data);
        }
        return $bankAccount;
    }

    /**
     * Returns a collection of Wallet.
     *
     * @param PaymentProfile $paymentProfile     The User's payment profile related to the wallets
     * @return Wallet[]
     */
    public function getWallets(PaymentProfile $paymentProfile)
    {
        $wallets = [new Wallet()];

        $dataProvider = new DataProvider($this->serverUrl."users/".$paymentProfile->getIdentifier()."/", self::COLLECTION_WALLETS);
        $getParams = null;
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->getCollection($getParams, $headers);
        
        $wallets = [];
        if ($response->getCode() == 200) {
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
     * Add a Wallet
     *
     * @param Wallet $user  The Wallet to create
     * @return Wallet|null
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
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        $wallet = new Wallet();
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            $wallet->setId($data["Id"]);
            $wallet->setDescription($data["Description"]);
            $wallet->setOwnerIdentifier($data['Owners'][0]);
        } else {
            throw new PaymentException(PaymentException::ADD_WALLET_USER_FAILED);
        }

        return $wallet;
    }


    /**
     * Register a User to the provider and create a PaymentProfile
     *
     * @param User $user
     * @param Address|null $address The address to use to the registration
     * @return string The identifier
     */
    public function registerUser(User $user, Address $address=null)
    {

        // Build the body
        $body['FirstName'] = $user->getGivenName();
        $body['LastName'] = $user->getFamilyName();
        $body['Email'] = $user->getEmail();

        if (is_null($user->getBirthDate())) {
            throw new PaymentException(PaymentException::NO_BIRTHDATE);
        }
        $body['Birthday'] = (int)$user->getBirthDate()->format('U');

        

        
        if (is_null($address)) {
            // Addresse of the user
            foreach ($user->getAddresses() as $homeAddress) {
                if ($homeAddress->isHome()) {
                    $address = $homeAddress;
                    break;
                }
            }
        }
        
        if (!is_null($address)) {
            $body['Address'] = [
                "AddressLine1" => $address->getStreetAddress(),
                "City" => $address->getAddressLocality(),
                "Region" => $address->getRegion(),
                "PostalCode" => $address->getPostalCode(),
                "Country" => substr($address->getCountryCode(), 0, 2)
            ];

            if (
                $address->getStreetAddress()=="" ||
                $address->getAddressLocality()=="" ||
                $address->getRegion()=="" ||
                $address->getPostalCode()=="" ||
                $address->getCountryCode()==""
            ) {
                throw new PaymentException(PaymentException::ADDRESS_INVALID);
            }

                
            $body['Nationality'] = substr($address->getCountryCode(), 0, 2);
            $body['CountryOfResidence'] = substr($address->getCountryCode(), 0, 2);
        } else {
            throw new PaymentException(PaymentException::NO_ADDRESS);
        }

        $body['KYCLevel'] = "LIGHT";

        $dataProvider = new DataProvider($this->serverUrl."users/", self::ITEM_USER_NATURAL);
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
        }

        return $data['Id'];
    }


    /**
     * Get the secured form's url for electronic payment
     *
     * @param CarpoolPayment $carpoolPayment
     * @return CarpoolPayment With redirectUrl filled
     */
    public function generateElectronicPaymentUrl(CarpoolPayment $carpoolPayment): CarpoolPayment
    {
        $user = $carpoolPayment->getUser();
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);
        
        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            // No active payment profile. The User need at least a Wallet to pay so we register him and create a Wallet
            $identifier = $this->registerUser($user);
            $wallet = new Wallet();
            $wallet->setComment("");
            $wallet->setCurrency($this->currency);
            $wallet->setDescription("From Mobicoop");
            $wallet->setOwnerIdentifier($identifier);
            $wallet = $this->addWallet($wallet);
            $carpoolPayment->setCreateCarpoolProfileIdentifier($identifier); // To persist the paymentProfile in PaymentManager
        } else {
            $identifier = $paymentProfiles[0]->getIdentifier();
            $wallet = $this->getWallets($paymentProfiles[0])[0];
        }
        
        $body = [
            "AuthorId" => $identifier,
            "DebitedFunds" => [
                "Currency" => $this->currency,
                "Amount" => (int)($carpoolPayment->getAmount()*100)
            ],
            "Fees" => [
                "Currency" => $this->currency,
                "Amount" => 0
            ],
            "CreditedWalletId" => $wallet->getId(),
            "ReturnURL" => "http://www.test.com?carpoolPaymentId=".$carpoolPayment->getId(),
            "CardType" => self::CARD_TYPE,
            "Culture" => self::LANGUAGE
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_PAYIN);
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::GET_URL_PAYIN_FAILED);
        }

        $carpoolPayment->setTransactionid($data['Id']);
        $carpoolPayment->setRedirectUrl($data['RedirectURL']);
        
        return $carpoolPayment;
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
    public function processElectronicPayment(User $debtor, array $creditors)
    {
        // Get the wallet of the debtor and his identifier
        $debtorPaymentProfile = $this->paymentProfileRepository->find($debtor->getPaymentProfileId());
        
        // Transfer to the creditors wallets and payout
        foreach ($creditors as $creditor) {
            $creditorWallet = $creditor['user']->getWallets()[0];
            $this->transferWalletToWallet($debtorPaymentProfile->getIdentifier(), $debtorPaymentProfile->getWallets()[0], $creditorWallet, $creditor['amount']);

            // Do the payout to the default bank account
            $creditorPaymentProfile = $this->paymentProfileRepository->find($creditor['user']->getPaymentProfileId());
            $creditorBankAccount = $creditor['user']->getBankAccounts()[0];
            $this->triggerPayout($creditorPaymentProfile->getIdentifier(), $creditorWallet, $creditorBankAccount, $creditor['amount']);
        }
    }

    /**
     * Transfer founds bewteen two wallets
     *
     * @param integer $debtorIdentifier MangoPay's identifier of the debtor
     * @param Wallet $walletFrom    Wallet of the debtor
     * @param Wallet $walletTo      Wallet of the creditor
     * @param float $amount         Amount of the transaction
     * @param string $tag
     * @return boolean
     */
    public function transferWalletToWallet(int $debtorIdentifier, Wallet $walletFrom, Wallet $walletTo, float $amount, string $tag=""): bool
    {
        $body = [
            "AuthorId" => $debtorIdentifier,
            "DebitedFunds" => [
                "Currency" => $this->currency,
                "Amount" => (int)($amount*100)
            ],
            "Fees" => [
                "Currency" => $this->currency,
                "Amount" => 0
            ],
            "DebitedWalletId" => $walletFrom->getId(),
            "CreditedWalletId" => $walletTo->getId(),
            "Tag" => $tag
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_TRANSFERS);
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        if ($response->getCode() == 200) {
            //$data = json_decode($response->getValue(), true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Trigger a payout from a Wallet to a Bank Account
     *
     * @param Wallet $wallet
     * @param BankAccount $bankAccount
     * @return boolean
     */
    public function triggerPayout(int $authorIdentifier, Wallet $wallet, BankAccount $bankAccount, float $amount, string $reference=""): bool
    {
        $body = [
            "AuthorId" => $authorIdentifier,
            "DebitedFunds" => [
                "Currency" => $this->currency,
                "Amount" => (int)($amount*100)
            ],
            "Fees" => [
                "Currency" => $this->currency,
                "Amount" => 0
            ],
            "DebitedWalletId" => $wallet->getId(),
            "BankAccountId" => $bankAccount->getId(),
            "BankWireRef" => $reference
        ];

        $dataProvider = new DataProvider($this->serverUrl, self::ITEM_PAYOUT);
        $headers = [
            "Authorization" => $this->authChain
        ];
        $response = $dataProvider->postCollection($body, $headers);
        
        if ($response->getCode() == 200) {
            //$data = json_decode($response->getValue(), true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Handle a payment web hook
     * @var MangoPayHook $hook The mangopay hook
     * @return int|null : return the transactionId if it's a success. Null otherwise.
     */
    public function handleHook(Hook $hook): ?array
    {
        switch ($hook->getEventType()) {
            case MangoPayHook::PAYIN_SUCCEEDED:
            case MangoPayHook::VALIDATION_ASKED:
                echo "yo!!!!";die;
                return [
                    "transactionId" => $hook->getRessourceId(),
                    "success" => true
                ];
            break;
            default:
                return [
                    "transactionId" => $hook->getRessourceId(),
                    "success" => false
                ];
        }

        return [];
    }

    /**
     * Upload an identity validation document
     * The document is not stored on the platform. It has to be deleted.
     *
     * @param ValidationDocument $validationDocument
     * @return ValidationDocument
     */
    public function uploadValidationDocument(ValidationDocument $validationDocument): ValidationDocument
    {
        $user = $validationDocument->getUser();
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);
        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            throw new PaymentException(PaymentException::CARPOOL_PAYMENT_NOT_FOUND);
        }
        $identifier = $paymentProfiles[0]->getIdentifier();

        //$fileContent = base64_encode(file_get_contents(self::VALIDATION_DOCUMENTS_PATH."".$validationDocument->getFileName()));
        

        // General header for all 3 requests
        $headers = [
            "Authorization" => $this->authChain
        ];


        // Creation of the doc
        $urlPost = str_replace("{userId}", $identifier, self::ITEM_KYC_CREATE_DOC);
        $body = [
            "Type" => self::VALIDATION_DOC_TYPE,
            "Tag" => "Automatic"
        ];
        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->postCollection($body, $headers);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            $docId = $data['Id'];
        } else {
            throw new PaymentException(PaymentException::ERROR_CREATING_DOC_TO_PROVIDER);
        }
        
        // Creation of pages
        $urlPost = str_replace("{KYCDocId}", $docId, str_replace("{userId}", $identifier, self::ITEM_KYC_CREATE_PAGE));

        $body = [
            "File" => base64_encode(file_get_contents($this->validationDocsPath."".$validationDocument->getFileName()))
        ];
        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->postCollection($body, $headers);
        if ($response->getCode() !== 204) {
            throw new PaymentException(PaymentException::ERROR_CREATING_DOC_PAGE_TO_PROVIDER);
        }

        // Asking validation
        $urlPost = str_replace("{KYCDocId}", $docId, str_replace("{userId}", $identifier, self::ITEM_KYC_PUT_DOC));
        
        $body = [
            "Status" => self::VALIDATION_ASKED
        ];
        

        $dataProvider = new DataProvider($this->serverUrl, $urlPost);
        $response = $dataProvider->putItem($body, $headers);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            if ($data['Status']!== self::VALIDATION_ASKED) {
                throw new PaymentException(PaymentException::ERROR_VALIDATION_ASK_DOC_BAD_STATUS);
            }
        } else {
            throw new PaymentException(PaymentException::ERROR_VALIDATION_ASK_DOC);
        }

        return $validationDocument;
    }

    /**
     * Deserialize a BankAccount
     * @param array $account                    The account to deserialize
     * @return BankAccount
     */
    public function deserializeBankAccount(array $account)
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
            if (trim($account['OwnerAddress']['AddressLine2'])!=="") {
                $streetAddress .= " ".$account['OwnerAddress']['AddressLine2'];
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
     * Deserialize a Wallet
     * @param array $data  The wallet to deserialize
     * @return Wallet
     */
    public function deserializeWallet(array $data)
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
            $paymentProfile = $this->paymentProfileRepository->findOneBy(['identifier'=>$owner]);
            if (!is_null($paymentProfile)) {
                $paymentProfiles[] = $paymentProfile;
            }
        }

        return $wallet;
    }
}
