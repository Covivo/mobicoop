<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\SubscriptionManager;

abstract class SubscriptionGet
{
    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    protected $_currentSubscription;

    /**
     * @var SubscriptionManager
     */
    protected $_subscriptionManager;

    protected function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;
    }

    protected function getMobConnectSubscriptionVersion()
    {
        return $this->_subscriptionManager->getMobConnectSubscription($this->_currentSubscription);
    }
}
