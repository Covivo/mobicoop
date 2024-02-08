<?php

namespace App\Tests\Mocks\Geography;

use App\Geography\Entity\Address;

abstract class AddressMock
{
    public static function getAddress(): Address
    {
        return new Address();
    }
}
