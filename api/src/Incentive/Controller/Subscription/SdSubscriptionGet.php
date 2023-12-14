<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\SubscriptionManager;

class SdSubscriptionGet extends SubscriptionGet
{
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        parent::__construct($subscriptionManager);
    }

    public function __invoke(ShortDistanceSubscription $subscription)
    {
        $this->_currentSubscription = $subscription;

        return $this->getMobConnectSubscription();
    }
}
