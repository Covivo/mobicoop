<?php

namespace App\Tests\Mocks\Incentive;

use App\Carpool\Entity\Ask;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Definition\LdImproved;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Tests\Mocks\Carpool\CarpoolProofMock;
use App\Tests\Mocks\Payment\CarpoolItemMock;
use App\Tests\Mocks\Payment\CarpoolPaymentMock;
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
        $subscription->setCommitmentProofJourney(LdJourneyMock::getCommitedJourned());
        $subscription->setCommitmentProofDate(new \DateTime());

        return $subscription;
    }

    public static function getValidatedSubscription(CarpoolItem $carpoolItem = null, CarpoolPayment $carpoolPayment = null): LongDistanceSubscription
    {
        $subscription = static::getCommitedSubscription();

        if (is_null($carpoolPayment)) {
            $carpoolPayment = CarpoolPaymentMock::getCarpoolPaymentEec();
        }

        if (is_null($carpoolItem)) {
            $ask = new Ask();
            $carpoolProof = CarpoolProofMock::getCarpoolProofEec($ask);

            $carpoolItem = CarpoolItemMock::getCarpoolItemEec($carpoolPayment, $ask);
        }

        $commitmentJourney = $subscription->getCommitmentProofJourney();
        $commitmentJourney->setCarpoolItem($carpoolItem);
        $commitmentJourney->setCarpoolPayment($carpoolPayment);

        return $subscription;
    }

    public static function getCompleteSubscription(): LongDistanceSubscription
    {
        $subscription = static::getCommitedSubscription();

        for ($i = 0; $i < 9; ++$i) {
            $subscription->addLongDistanceJourney(LdJourneyMock::getValidatedJourney());
        }

        return $subscription;
    }

    public static function getCompleteValidatedSubscription(): LongDistanceSubscription
    {
        $subscription = static::getValidatedSubscription();

        for ($i = 0; $i < 9; ++$i) {
            $subscription->addLongDistanceJourney(LdJourneyMock::getValidatedJourney());
        }

        return $subscription;
    }

    private static function _getLdSubscription(): LongDistanceSubscription
    {
        return new LongDistanceSubscription(
            UserMock::getUserEec(),
            md5(rand()),
            new LdImproved()
        );
    }
}
