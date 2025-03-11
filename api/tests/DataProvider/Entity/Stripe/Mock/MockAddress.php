<?php

namespace App\Tests\DataProvider\Entity\Stripe\Mock;

use App\Geography\Entity\Address;

class MockAddress
{
    public static function getSimpleAddress(): Address
    {
        $homeAddress = new Address();
        $homeAddress->setStreetAddress('1 rue de la paix');
        $homeAddress->setPostalCode('75000');
        $homeAddress->setAddressLocality('Paris');
        $homeAddress->setAddressCountry('France');
        $homeAddress->setCountryCode('FRA');
        $homeAddress->setHome(true);

        return $homeAddress;
    }
}
