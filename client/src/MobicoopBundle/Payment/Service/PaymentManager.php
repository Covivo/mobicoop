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

use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentItem;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\BankAccount;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPayment;

/**
 * Payment management service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
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
        $this->dataProvider->setClass(PaymentItem::class);
    }

    /**
     * Get the bank coordinates of a User
     * @param string $iban  IBAN of the bank account
     * @param string $bic   BIC of the bank account
     */
    public function addBankCoordinates(string $iban, string $bic)
    {
        $this->dataProvider->setClass(BankAccount::class);

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
     * Delete a bank account
     * @param int $bankAccountid  Id (provider's one) of the Bank account to delete
     */
    public function deleteBankCoordinates(int $bankAccountid)
    {
        $this->dataProvider->setClass(BankAccount::class);

        $response = $this->dataProvider->getSpecialCollection('disable', ['idBankAccount'=>$bankAccountid]);
        if ($response->getCode() == 200) {
            return $response->getValue()->getMember();
        } else {
            return ['error'=>1];
        }
        return null;
    }

    /**
     * Get payments
     *
     * @param integer $frequency    The frequency of the carpools to get (1 = punctual, 2 = regular)
     * @param integer $type         The type of carpools to get (1 = to pay as a passenger, 2 = to collect as a driver)
     * @param integer $week         The week number and year
     */
    public function getPaymentItems(int $frequency, int $type, int $week=null)
    {
        $params = [
            "frequency" => $frequency,
            "type" => $type
        ];
        if ($week) {
            $params['week'] = $week;
        }

        $response = $this->dataProvider->getCollection($params);
        return $response->getValue()->getMember();
    }

    /**
     * Post payments
     *
     * @param integer $type The payment type (1 = a payment to be made, 2 = a payment validation).
     * @param array $items The items concerned by the payment.
     * Each item of the array contains the :
     * - the id of the payment item
     * - the status (1 = realized, 2 = not realized)
     * - the mode for the payment if type = 1 (1 = online, 2 = direct)
     */
    public function postPaymentPayment(int $type, array $items)
    {
        $this->dataProvider->setClass(PaymentPayment::class);

        $paymentPayment = new PaymentPayment;

        $paymentPayment->setType($type);
        $paymentPayment->setItems($items);
        
        $response = $this->dataProvider->post($paymentPayment);
        if ($response->getCode() != 201) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    /**
     * Get weeks with a pending payment
     *
     * @param int $askId
     * @return void
     */
    public function getWeeks($askId)
    {
        $this->dataProvider->setClass(Ask::class);

        $response = $this->dataProvider->getSpecialItem($askId, 'pendingPayment');
        if ($response->getCode() != 201) {
            return $response->getValue()->getWeekItems();
        }
        return $response->getValue();
    }
}
