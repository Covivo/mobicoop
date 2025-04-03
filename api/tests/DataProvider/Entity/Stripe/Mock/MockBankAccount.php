<?php

namespace App\Tests\DataProvider\Entity\Stripe\Mock;

use App\Geography\Entity\Address;
use App\Payment\Ressource\BankAccount;

class MockBankAccount
{
    public static function getBankAccount(): BankAccount
    {
        $bankAccount = new BankAccount();
        $bankAccount->setIBAN('FR1420041010050500013M02606');

        $address = new Address();
        $address->setAddressLocality('1 rue de la paix');
        $address->setAddressCountry('France');
        $address->setCountryCode('FRA');
        $address->setPostalCode('75000');

        $bankAccount->setAddress($address);

        return $bankAccount;
    }
}
