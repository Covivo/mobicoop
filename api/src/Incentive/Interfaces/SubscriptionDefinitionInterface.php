<?php

namespace App\Incentive\Interfaces;

/**
 * A definition returns the properties that define how the subscription works. It also defines the conditions relating to its use.
 */
interface SubscriptionDefinitionInterface
{
    /**
     * Returns the subscription version used when the subscription has been created.
     */
    public function getVersion(): int;

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
     * Returns if the deadline is over.
     */
    public static function isDeadlineOver(): bool;

    /**
     * Returns, in month, the transitional period duration.
     */
    public static function getTransitionalPeriodDuration(): int;

    /**
     * Returns the transitional period end date.
     */
    public static function getTransitionalPeriodEndDate(): \DateTime;

    /**
     * Returns if the transitional period is over.
     */
    public static function isTransitionalPeriodOver(): bool;

    /**
     * Returns if the définition is ready to be used.
     */
    public static function isReady(): bool;

    /**
     * Processes during or after transition.
     */
    public static function manageTransition(): void;
}
