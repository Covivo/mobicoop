<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of short distance subscription improved by Mobicoop.
 */
class SdImproved extends SubscriptionDefinition
{
    protected const VERSION = 0;

    protected const MAXIMUM_JOURNEY_NUMBER = 1;

    public static function isReady(): bool
    {
        return false;
    }
}
