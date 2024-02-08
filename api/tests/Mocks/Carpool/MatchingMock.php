<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Matching;

class MatchingMock
{
    public static function _getMatchingEecLd()
    {
        $matching = new Matching();
        $matching->setCommonDistance(100000);

        $matching->addWaypoint(WaypointMock::getWaypointEec());
        $matching->addWaypoint(WaypointMock::getWaypointEec());
        $matching->addWaypoint(WaypointMock::getWaypointEec());
        $matching->addWaypoint(WaypointMock::getWaypointEec());

        return $matching;
    }
}
