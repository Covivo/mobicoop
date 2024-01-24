<?php

namespace App\Incentive\Interfaces;

interface SubscriptionInterface
{
    /**
     * Returns the definitions list.
     *
     * @return SubscriptionDefinitionInterface[]
     */
    public static function getAvailableDefinitions();
}
