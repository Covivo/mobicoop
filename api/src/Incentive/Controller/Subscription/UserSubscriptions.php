<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Service\Manager\JourneyManager;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\User\Entity\User;

class UserSubscriptions
{
    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(JourneyManager $journeyManager, SubscriptionManager $subscriptionManager)
    {
        $this->_journeyManager = $journeyManager;
        $this->_subscriptionManager = $subscriptionManager;
    }

    public function __invoke(User $user): User
    {
        $user = $this->_journeyManager->getAdditionalJourneys($user);

        return $this->_subscriptionManager->getUserMobConnectSubscription($user);
    }
}
