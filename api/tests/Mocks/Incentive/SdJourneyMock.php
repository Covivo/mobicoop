<?php

namespace App\Tests\Mocks\Incentive;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceJourney;

class SdJourneyMock
{
    public static function getCommitedJourned(): ShortDistanceJourney
    {
        return static::_getSdJourney();
    }

    public static function getValidatedJourney(): ShortDistanceJourney
    {
        $journey = static::_getSdJourney();

        $journey->getCarpoolProof()->setStatus(CarpoolProof::STATUS_VALIDATED);
        $journey->getCarpoolProof()->setType(CarpoolProof::TYPE_HIGH);

        return $journey;
    }

    private static function _getSdJourney(): ShortDistanceJourney
    {
        return new ShortDistanceJourney(new CarpoolProof());
    }
}
