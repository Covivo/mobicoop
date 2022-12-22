<?php

namespace App\Incentive\Event;

use App\Incentive\Entity\Flat\ShortDistanceSubscription;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a first long distance journey is validated by the RPC.
 */
class ShortDistanceSubscriptionClosedEvent extends Event
{
    public const NAME = 'short_distance_subscription_closed';

    protected $subscription;

    public function __construct(ShortDistanceSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription(): ?ShortDistanceSubscription
    {
        return $this->subscription;
    }
}
