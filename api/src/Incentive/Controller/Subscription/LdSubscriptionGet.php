<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Manager\SubscriptionManager;

class LdSubscriptionGet extends SubscriptionGet
{
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        parent::__construct($subscriptionManager);
    }

    public function __invoke(LongDistanceSubscription $subscription)
    {
        $this->_currentSubscription = $subscription;

        return $this->getMobConnectSubscriptionVersion();
    }
}
