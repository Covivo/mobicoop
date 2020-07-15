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
use App\Payment\Entity\BankAccount;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Interfaces\PaymentProviderInterface;
use App\User\Entity\User;
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

    const COLLECTION_BANK_ACCOUNTS = "bankaccounts/";

    private $serverUrl;
    private $authChain;
    
    public function __construct(string $clientId, string $apikey, bool $sandBoxMode)
    {
        ($sandBoxMode) ? $this->serverUrl = self::SERVER_URL_SANDBOX : $this->serverUrl = self::SERVER_URL;
        $this->authChain = "Basic ".base64_encode($clientId.":".$apikey);
        $this->serverUrl .= self::VERSION."/".$clientId."/";
    }
    
    /**
     * Returns a collection of Bank accounts.
     *
     * @param PaymentProfile $paymentProfile     The User's payment profile related to the Bank accounts
     * @return BankAccount[]
     */
    public function getBankAccounts(PaymentProfile $paymentProfile)
    {
        $dataProvider = new DataProvider($this->serverUrl."users/".$paymentProfile->getIdentifier()."/", self::COLLECTION_BANK_ACCOUNTS);
        $getParams = [
            "per_page" => 100,
            "sort" => "creationdate:desc",
        ];
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
     * Returns a single Bank account
     *
     * @param int $bankAccountId     The id of the Bank Account
     * @return BankAccount|null
     */
    public function getBankAccount(int $bankAccountId)
    {
    }
    
    /**
     * Add a BankAccount
     *
     * @param PaymentProfile $paymentProfile     The PaymentProfile you want to add an BankAccount
     * @param BankAccount $user                  The BankAccount to create
     * @return BankAccount|null
     */
    public function addBankAccount(PaymentProfile $paymentProfile, BankAccount $bankAccount)
    {
    }

    /**
     * Deserialize a BankAccount
     * @param array $account                    The account to deserialize
     * @return BankAccount
     */
    public function deserializeBankAccount(array $account)
    {
        $bankAccount = new BankAccount();
        $bankAccount->setUserLitteral($account['OwnerName']);
        $bankAccount->setIban($account['IBAN']);
        $bankAccount->setBic($account['BIC']);
        $bankAccount->setCreatedDate(\DateTime::createFromFormat('U', $account['CreationDate']));
        $bankAccount->setComment($account['Tag']);
        $bankAccount->setStatus($account['Active']);
        return $bankAccount;
    }
}
