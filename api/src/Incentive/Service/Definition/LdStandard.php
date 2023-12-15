<?php

namespace App\Incentive\Service\Definition;

/**
 * Definition of long distance subscription improved by Mobicoop.
 */
class LdStandard extends SubscriptionDefinition
{
    protected const VERSION = 1;

    protected const MAXIMUM_JOURNEY_NUMBER = 1;

    public static function isReady(): bool
    {
        return self::getDeadline() > new \DateTime();
    }
}
