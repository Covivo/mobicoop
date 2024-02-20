<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Waypoint;
use App\Tests\Mocks\Geography\AddressMock;

class WaypointMock
{
    public static function getWaypointEec()
    {
        $waypoint = new Waypoint();
        $waypoint->setAddress(AddressMock::getAddressEec());

        return $waypoint;
    }
}
