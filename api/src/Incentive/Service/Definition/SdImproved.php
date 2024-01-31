<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of a subscription to a standard short distance form as validated by the French government.
 */
class SdImproved extends SubscriptionDefinition
{
    protected const DEADLINE = '2025-01-01 00:00:00';

    protected const MAXIMUM_JOURNEY_NUMBER = 10;

    public static function isReady(): bool
    {
        return true;
    }
}
