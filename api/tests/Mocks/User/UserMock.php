<?php

namespace App\Tests\Mocks\User;

use App\Tests\Mocks\Geography\AddressMock;
use App\User\Entity\User;

class UserMock
{
    public static function getUserEec(): User
    {
        $user = new User();
        $user->setGivenName(md5(rand()));
        $user->setFamilyName(md5(rand()));
        $user->setDrivingLicenceNumber(md5(rand()));
        $user->setTelephone(md5(rand()));
        $user->setEmail(md5(rand()));
        $user->setHomeAddress(AddressMock::getHomeAddress());

        return $user;
    }
}
