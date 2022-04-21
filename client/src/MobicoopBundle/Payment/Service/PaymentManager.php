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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\BankAccount;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentItem;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPayment;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPeriod;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentWeek;

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
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(PaymentItem::class);
    }

    /**
     * Get the bank coordinates of a User.
     *
     * @param string $iban    IBAN of the bank account
     * @param string $bic     BIC of the bank account
     * @param array  $address Address linked to the back account
     */
    public function addBankCoordinates(string $iban, string $bic, array $address)
    {
        $this->dataProvider->setClass(BankAccount::class);

        $bankAccount = new BankAccount();
        $bankAccount->setIban($iban);
        $bankAccount->setBic($bic);

        $bankAccountAddress = new Address();
        $bankAccountAddress->setStreet(isset($address['street']) ? $address['street'] : null);
        $bankAccountAddress->setHouseNumber(isset($address['houseNumber']) ? $address['houseNumber'] : null);
        $bankAccountAddress->setStreetAddress(isset($address['streetAddress']) ? $address['streetAddress'] : null);
        $bankAccountAddress->setAddressLocality(isset($address['addressLocality']) ? $address['addressLocality'] : null);
        $bankAccountAddress->setRegion(isset($address['macroRegion']) ? $address['macroRegion'] : null);
        $bankAccountAddress->setPostalCode(isset($address['postalCode']) ? $address['postalCode'] : null);
        $bankAccountAddress->setAddressCountry(isset($address['addressCountry']) ? $address['addressCountry'] : null);
        $bankAccountAddress->setCountryCode(isset($address['countryCode']) ? $address['countryCode'] : null);

        $bankAccount->setAddress($bankAccountAddress);

        $response = $this->dataProvider->post($bankAccount);
        if (201 == $response->getCode()) {
            return $response->getValue();
        }

        return ['error' => $response->getValue()];

        return null;
    }

    /**
     * Delete a bank account.
     *
     * @param int $bankAccountid Id (provider's one) of the Bank account to delete
     */
    public function deleteBankCoordinates(int $bankAccountid)
    {
        $this->dataProvider->setClass(BankAccount::class);

        $response = $this->dataProvider->getSpecialCollection('disable', ['idBankAccount' => $bankAccountid]);
        if (200 == $response->getCode()) {
            return $response->getValue()->getMember();
        }

        return ['error' => 1];

        return null;
    }

    /**
     * Get payments.
     *
     * @param int    $frequency The frequency of the carpools to get (1 = punctual, 2 = regular)
     * @param int    $type      The type of carpools to get (1 = to pay as a passenger, 2 = to collect as a driver)
     * @param string $week      The week number and year
     */
    public function getPaymentItems(int $frequency, int $type, string $week = null)
    {
        $params = [
            'frequency' => $frequency,
            'type' => $type,
        ];
        if ($week) {
            $params['week'] = $week;
        }

        $response = $this->dataProvider->getCollection($params);

        return $response->getValue()->getMember();
    }

    /**
     * Post payments.
     *
     * @param int   $type  the payment type (1 = a payment to be made, 2 = a payment validation)
     * @param array $items The items concerned by the payment.
     *                     Each item of the array contains the :
     *                     - the id of the payment item
     *                     - the status (1 = realized, 2 = not realized)
     *                     - the mode for the payment if type = 1 (1 = online, 2 = direct)
     */
    public function postPaymentPayment(int $type, array $items)
    {
        $this->dataProvider->setClass(PaymentPayment::class);

        $paymentPayment = new PaymentPayment();

        $paymentPayment->setType($type);
        $paymentPayment->setItems($items);

        $response = $this->dataProvider->post($paymentPayment);
        if (201 != $response->getCode()) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    /**
     * Get a PaymentPayment by its id.
     *
     * @param int $id Id of the PaymentPayment
     */
    public function getPaymentPayment(int $id): ?PaymentPayment
    {
        $this->dataProvider->setClass(PaymentPayment::class);

        $response = $this->dataProvider->getItem($id);
        if (201 != $response->getCode()) {
            return $response->getValue();
        }
    }

    /**
     * Get weeks with a pending payment.
     *
     * @param int $askId
     */
    public function getWeeks($askId)
    {
        $this->dataProvider->setClass(Ask::class);

        $response = $this->dataProvider->getSpecialItem($askId, 'pendingPayment');
        if (201 != $response->getCode()) {
            return $response->getValue()->getWeekItems();
        }

        return $response->getValue();
    }

    /**
     * Get the calendar of payments for a regular Ad.
     *
     * @param int $type The type of payment (collect/pay)
     *
     * @return array|object The calendar
     */
    public function getCalendar(int $type)
    {
        $this->dataProvider->setClass(PaymentPeriod::class);

        $response = $this->dataProvider->getCollection(['type' => $type]);

        return $response->getValue()->getMember();
    }

    /**
     * Get the first non validated week for a regular Ask.
     *
     * @param int $id The id of the ask
     *
     * @return array|object The week
     */
    public function getFirstWeek(int $id)
    {
        $this->dataProvider->setClass(PaymentWeek::class);

        $response = $this->dataProvider->getItem($id);

        return $response->getValue();
    }
}
