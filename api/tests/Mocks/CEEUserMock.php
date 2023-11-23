<?php

namespace App\Tests\Mocks;

use App\Geography\Entity\Address;
use App\User\Entity\User;

class CEEUserMock
{
    public static function getUser(): User
    {
        $user = new User();
        $user->setGivenName('givenName');
        $user->setFamilyName('familyName');
        $user->setDrivingLicenceNumber('123456789123');
        $user->setTelephone('+33582160010');
        $user->setEmail('contact@mobicoop.org');

        $address = new Address();
        $address->setHouseNumber('9');
        $address->setStreetAddress('boulevard Louis Sicre');
        $address->setAddressLocality('Castelsarrasin');
        $address->setPostalCode('82100');
        $user->setHomeAddress($address);

        return $user;
    }
}
