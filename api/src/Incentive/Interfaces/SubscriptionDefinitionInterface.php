<?php

namespace App\Incentive\Interfaces;

/**
 * A definition returns the properties that define how the subscription works. It also defines the conditions relating to its use.
 */
interface SubscriptionDefinitionInterface
{
    /**
     * Returns the maximum number of journeys that the subscription can contain.
     */
    public function getMaximumJourneysNumber(): int;

    /**
     * Returns the duration of the subscription validity period.
     */
    public function getValidityPeriodDuration(): int;

    /**
     * Returns if define, the definition deadline.
     */
    public static function getDeadline(): ?\DateTime;

    /**
     * Returns if the définition is ready to be used.
     */
    public static function isReady(): bool;
}
