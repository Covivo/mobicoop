<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of a subscription to a standard long distance form as validated by the French government.
 */
class LdStandard extends SubscriptionDefinition
{
    protected const DEADLINE = '2024-01-01 00:00:00';

    protected const MAXIMUM_JOURNEY_NUMBER = 3;

    public static function isReady(): bool
    {
        return self::getDeadline() > new \DateTime();
    }
}
