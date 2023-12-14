<?php

namespace App\Incentive\Interfaces;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\User\Entity\User;

interface SubscriptionInterface
{
    public function __construct(
        User $user,
        MobConnectSubscriptionResponse $mobConnectSubscriptionResponse,
        SubscriptionDefinitionInterface $subscriptionDefinition
    );

    /**
     * Returns the definitions list.
     *
     * @return SubscriptionDefinitionInterface[]
     */
    public static function getAvailableDefinitions();
}
