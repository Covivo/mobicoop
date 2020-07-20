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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\BankAccount;

/**
 * Payment management service.
 */
class PaymentManager
{
    private $dataProvider;


    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(BankAccount::class);
    }

    /**
     * Get the bank coordinates of a User
     */
    public function addBankCoordinates(string $iban, string $bic)
    {
        $bankAccount = new BankAccount();
        $bankAccount->setIban($iban);
        $bankAccount->setBic($bic);
        $response = $this->dataProvider->post($bankAccount);
        if ($response->getCode() == 201) {
            return $response->getValue();
        } else {
            return ['error'=>1];
        }
        return null;
    }
    
    /**
     * Get the bank coordinates of a User
     */
    public function deleteBankCoordinates(int $bankAccountid)
    {
        $response = $this->dataProvider->getSpecialCollection('disable', ['idBankAccount'=>$bankAccountid]);
        if ($response->getCode() == 200) {
            return $response->getValue()->getMember();
        } else {
            return ['error'=>1];
        }
        return null;
    }
}
