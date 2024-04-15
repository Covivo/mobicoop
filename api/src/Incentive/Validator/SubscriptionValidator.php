<?php

namespace App\Incentive\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription\SpecificFields;

abstract class SubscriptionValidator
{
    public const ALLOWED_PROPERTIES_TO_PATCH = [
        SpecificFields::DRIVING_LICENCE_NUMBER,
        SpecificFields::PHONE_NUMBER,
    ];

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

    public static function canPropertyBePatched(string $property): bool
    {
        return in_array($property, static::ALLOWED_PROPERTIES_TO_PATCH);
    }
}
