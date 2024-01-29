<?php

namespace App\Tests\Mocks\Carpool;

use App\Carpool\Entity\Ask;

class AskMock
{
    public static function getAskEec(): Ask
    {
        return new Ask();
    }
}
