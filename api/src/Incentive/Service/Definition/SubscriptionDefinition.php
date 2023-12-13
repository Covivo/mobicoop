<?php

namespace App\Incentive\Service\Definition;

use App\Incentive\Interfaces\SubscriptionDefinitionInterface;

abstract class SubscriptionDefinition implements SubscriptionDefinitionInterface
{
    protected const DEADLINE = '2150-01-01';                // Default value as date that will never be gone.

    protected const MAXIMUM_JOURNEY_NUMBER = 3;

    protected const VALIDITY_PERIOD_DURATION = 3;

    public function getMaximumJourneysNumber(): int
    {
        return static::MAXIMUM_JOURNEY_NUMBER;
    }

    public function getValidityPeriodDuration(): int
    {
        return static::VALIDITY_PERIOD_DURATION;
    }

    public static function getDeadline(): \DateTime
    {
        return new \DateTime(static::DEADLINE);
    }
}
