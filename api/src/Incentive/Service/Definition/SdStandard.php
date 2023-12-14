<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of a subscription to a standard short distance form as validated by the French government.
 */
class SdStandard extends SubscriptionDefinition
{
    protected const MAXIMUM_JOURNEY_NUMBER = 10;

    public static function isReady(): bool
    {
        return true;
    }
}
