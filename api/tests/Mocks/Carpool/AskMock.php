<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Ask;

class AskMock
{
    public static function getAskEec(): Ask
    {
        $ask = new Ask();
        $ask->setMatching(MatchingMock::_getMatchingEecLd());

        return $ask;
    }
}
