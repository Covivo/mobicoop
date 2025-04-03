<?php

namespace App\Tests\DataProvider\Entity\Stripe\Mock;

use App\User\Entity\User;

class MockUser
{
    public static function getSimpleUser(): User
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setGivenName('test');
        $user->setFamilyName('test');
        $user->setTelephone('0606060606');
        $user->setBirthDate(new \DateTime('1980-01-01'));

        $user->setHomeAddress(MockAddress::getSimpleAddress());
        $user->setPaymentProfileId(1);

        return $user;
    }
}
