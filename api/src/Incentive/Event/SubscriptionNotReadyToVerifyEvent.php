<?php

namespace App\Incentive\Event;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use Symfony\Contracts\EventDispatcher\Event;

class SubscriptionNotReadyToVerifyEvent extends Event
{
    public const NAME = 'subscription_not_ready_to_verify';

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_subscription;

    public function __construct($subscription)
    {
        $this->_subscription = $subscription;
    }

    /**
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public function getSubscription()
    {
        return $this->_subscription;
    }
}
