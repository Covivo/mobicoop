<?php

namespace App\Tests\Mocks\Incentive;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Definition\LdImproved;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Tests\Mocks\User\UserMock;

class LdSubscriptionMock
{
    public static function getNewSubscription(): LongDistanceSubscription
    {
        return static::_getLdSubscription();
    }

    public static function getCommitedSubscription(): LongDistanceSubscription
    {
        $subscription = static::_getLdSubscription();
        $subscription->setCommitmentProofJourney(LdJourney::getCommitedJourned());
        $subscription->setCommitmentProofDate(new \DateTime());

        return $subscription;
    }

    public static function getValidatedSubscription(CarpoolItem $carpoolItem, CarpoolPayment $carpoolPayment): LongDistanceSubscription
    {
        $subscription = static::getCommitedSubscription();

        $commitmentJourney = $subscription->getCommitmentProofJourney();
        $commitmentJourney->setCarpoolItem($carpoolItem);
        $commitmentJourney->setCarpoolPayment($carpoolPayment);

        return $subscription;
    }

    private static function _getLdSubscription(): LongDistanceSubscription
    {
        $user = UserMock::getUserEec();

        return new LongDistanceSubscription(
            $user,
            md5(rand()),
            new LdImproved()
        );
    }
}
