<?php

namespace App\Tests\Mocks\Incentive;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;

class LdJourneyMock
{
    public static function getCommitedJourned(): LongDistanceJourney
    {
        return static::_getLdJourney();
    }

    public static function getValidatedJourney(): LongDistanceJourney
    {
        return static::_getLdJourney();
    }

    private static function _getLdJourney(): LongDistanceJourney
    {
        return new LongDistanceJourney(new Proposal());
    }
}
