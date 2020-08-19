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

use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\Address;
use App\Payment\Ressource\BankAccount;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Exception\PaymentException;
use App\Payment\Entity\Wallet;
use App\Payment\Entity\WalletBalance;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\Payment\Repository\PaymentProfileRepository;
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

    private $user;
    private $serverUrl;
    private $authChain;
    private $paymentProfileRepository;
    
    public function __construct(
        ?User $user,
        string $clientId,
        string $apikey,
        bool $sandBoxMode,
        PaymentProfileRepository $paymentProfileRepository
    ) {
        ($sandBoxMode) ? $this->serverUrl = self::SERVER_URL_SANDBOX : $this->serverUrl = self::SERVER_URL;
        $this->user = $user;
        $this->authChain = "Basic ".base64_encode($clientId.":".$apikey);
        $this->serverUrl .= self::VERSION."/".$clientId."/";
        $this->paymentProfileRepository = $paymentProfileRepository;
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

        // Addresse of the owner
        $homeAddress = null;
        foreach ($this->user->getAddresses() as $address) {
            if ($address->isHome()) {
                $homeAddress = $address;
                break;
            }
        }
        
        if (!is_null($homeAddress)) {
            $body['OwnerAddress'] = [
                "AddressLine1" => $homeAddress->getStreetAddress(),
                "City" => $homeAddress->getAddressLocality(),
                "Region" => $homeAddress->getRegion(),
                "PostalCode" => $homeAddress->getPostalCode(),
                "Country" => substr($homeAddress->getCountryCode(), 0, 2)
            ];
        }

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
    public function addWallet(Wallet $wallet)
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
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
        }

        return $data['Id'];
    }


    /**
     * Register a User to the provider and create a PaymentProfile
     *
     * @param User $user
     * @return string The identifier
     */
    public function registerUser(User $user)
    {

        // Build the body
        $body['FirstName'] = $user->getGivenName();
        $body['LastName'] = $user->getFamilyName();
        $body['Email'] = $user->getEmail();

        if (is_null($user->getBirthDate())) {
            throw new PaymentException(PaymentException::NO_BIRTHDATE);
        }
        $body['Birthday'] = (int)$user->getBirthDate()->format('U');

        

        // Addresse of the user
        $homeAddress = null;
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $homeAddress = $address;
                break;
            }
        }
        
        if (!is_null($homeAddress)) {
            $body['Address'] = [
                "AddressLine1" => $homeAddress->getStreetAddress(),
                "City" => $homeAddress->getAddressLocality(),
                "Region" => $homeAddress->getRegion(),
                "PostalCode" => $homeAddress->getPostalCode(),
                "Country" => substr($homeAddress->getCountryCode(), 0, 2)
            ];

            if (
                $homeAddress->getStreetAddress()=="" ||
                $homeAddress->getAddressLocality()=="" ||
                $homeAddress->getRegion()=="" ||
                $homeAddress->getPostalCode()=="" ||
                $homeAddress->getCountryCode()==""
            ) {
                throw new PaymentException(PaymentException::ADDRESS_INVALID);
            }

                
            $body['Nationality'] = substr($homeAddress->getCountryCode(), 0, 2);
            $body['CountryOfResidence'] = substr($homeAddress->getCountryCode(), 0, 2);
        } else {
            if (is_null($homeAddress)) {
                throw new PaymentException(PaymentException::NO_ADDRESS);
            }
        }

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
