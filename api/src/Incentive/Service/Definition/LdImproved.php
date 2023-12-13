<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of long distance subscription improved by Mobicoop.
 */
class LdImproved extends SubscriptionDefinition
{
    protected const DEADLINE = '2024-01-01 00:00:00';

    protected const MAXIMUM_JOURNEY_NUMBER = 1;

    public static function isReady(): bool
    {
        return self::getDeadline() <= new \DateTime();
    }
}
