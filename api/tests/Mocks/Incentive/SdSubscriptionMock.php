<?php

namespace App\Tests\Mocks\Incentive;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Definition\SdImproved;
use App\Tests\Mocks\Carpool\CarpoolProofMock;
use App\Tests\Mocks\User\UserMock;

class SdSubscriptionMock
{
    public static function getNewSubscription(): ShortDistanceSubscription
    {
        return static::_getSdSubscription();
    }

    public static function getCommitedSubscription(): ShortDistanceSubscription
    {
        $subscription = static::_getSdSubscription();
        $subscription->setCommitmentProofJourney(SdJourneyMock::getCommitedJourned());
        $subscription->setCommitmentProofDate(new \DateTime());

        return $subscription;
    }

    public static function getValidatedSubscription(CarpoolProof $carpoolProof = null): ShortDistanceSubscription
    {
        $subscription = static::getCommitedSubscription();

        if (is_null($carpoolProof)) {
            $carpoolProof = CarpoolProofMock::getCarpoolProofEec(new Ask());
        }

        $commitmentJourney = $subscription->getCommitmentProofJourney();
        $commitmentJourney->setCarpoolProof($carpoolProof);

        return $subscription;
    }

    public static function getCompleteSubscription(): ShortDistanceSubscription
    {
        $subscription = static::getCommitedSubscription();

        for ($i = 0; $i < 9; ++$i) {
            $subscription->addShortDistanceJourney(SdJourneyMock::getValidatedJourney());
        }

        return $subscription;
    }

    public static function getCompleteValidatedSubscription(): ShortDistanceSubscription
    {
        $subscription = static::getValidatedSubscription();

        for ($i = 0; $i < 9; ++$i) {
            $subscription->addShortDistanceJourney(SdJourneyMock::getValidatedJourney());
        }

        return $subscription;
    }

    private static function _getSdSubscription(): ShortDistanceSubscription
    {
        return new ShortDistanceSubscription(
            UserMock::getUserEec(),
            md5(rand()),
            new SdImproved()
        );
    }
}
