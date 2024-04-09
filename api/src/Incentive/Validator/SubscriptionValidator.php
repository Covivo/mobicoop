<?php

namespace App\Incentive\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;

abstract class SubscriptionValidator
{
    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function canSubscriptionBeRecommited($subscription): bool
    {
        return
            is_null($subscription->getStatus())
            && !static::isCommitmentJourneyEecCompliant($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isCommitmentJourneyEecCompliant($subscription): bool
    {
        return
            !is_null($subscription->getCommitmentProofJourney())
            && $subscription->getCommitmentProofJourney()->isEECCompliant();
    }
}
