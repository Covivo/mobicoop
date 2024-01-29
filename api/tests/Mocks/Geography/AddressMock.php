<?php

namespace App\Tests\Mocks\Geography;

use App\Geography\Entity\Address;

class AddressMock
{
    public static function getHomeAddress(): Address
    {
        $homeAddress = new Address();
        $homeAddress->setHouseNumber('5');
        $homeAddress->setStreetAddress('rue de la monnaie');
        $homeAddress->setAddressLocality('Nancy');
        $homeAddress->setPostalCode('54000');
        $homeAddress->setCounty('France');

        return $homeAddress;
    }
}
