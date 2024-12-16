<?php

namespace App\Incentive\Service\Definition;

use App\Incentive\Interfaces\SubscriptionDefinitionInterface;

abstract class SubscriptionDefinition implements SubscriptionDefinitionInterface
{
    protected const VERSION = 0;

    protected const DEADLINE = '2150-01-01';                // Default value as date that will never be gone.

    protected const TRANSITIONAL_PERIOD_DURATION = 0;

    protected const MAXIMUM_JOURNEY_NUMBER = 3;

    protected const VALIDITY_PERIOD_DURATION = 3;

    public function getVersion(): int
    {
        return static::VERSION;
    }

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

    public static function isDeadlineOver(): bool
    {
        return self::getDeadline() <= new \DateTime();
    }

    public static function getTransitionalPeriodDuration(): int
    {
        return static::TRANSITIONAL_PERIOD_DURATION;
    }

    public static function getTransitionalPeriodEndDate(): \DateTime
    {
        $endDate = clone self::getDeadline();

        return $endDate->add(new \DateInterval('P'.static::TRANSITIONAL_PERIOD_DURATION.'M'));
    }

    public static function isTransitionalPeriodOver(): bool
    {
        return self::getTransitionalPeriodEndDate() < new \DateTime();
    }

    public static function manageTransition(): void {}

    public static function isReady(): bool
    {
        return self::getDeadline() > new \DateTime();
    }
}
