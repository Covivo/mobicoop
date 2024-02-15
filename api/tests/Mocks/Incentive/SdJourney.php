<?php

namespace App\Tests\Mocks\Incentive;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceJourney;

class SdJourney
{
    public static function getCommitedJourned(): ShortDistanceJourney
    {
        return static::_getSdJourney();
    }

    public static function getValidatedJourney(): ShortDistanceJourney
    {
        return static::_getSdJourney();
    }

    private static function _getSdJourney(): ShortDistanceJourney
    {
        return new ShortDistanceJourney(new CarpoolProof());
    }
}
