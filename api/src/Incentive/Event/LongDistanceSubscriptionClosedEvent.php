<?php

namespace App\Incentive\Event;

use App\Incentive\Entity\Flat\LongDistanceSubscription;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a first long distance journey is validated by the RPC.
 */
class LongDistanceSubscriptionClosedEvent extends Event
{
    public const NAME = 'long_distance_subscription_closed';

    protected $subscription;

    public function __construct(LongDistanceSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription(): ?LongDistanceSubscription
    {
        return $this->subscription;
    }
}
