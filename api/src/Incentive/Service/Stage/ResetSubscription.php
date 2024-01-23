<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionReset extends Stage
{
    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct(EntityManagerInterface $em, $subscription)
    {
        $this->_em = $em;
        $this->_subscription = $subscription;
    }

    public function execute()
    {
        $this->_subscription->reset();

        $this->_em->flush();
    }
}
