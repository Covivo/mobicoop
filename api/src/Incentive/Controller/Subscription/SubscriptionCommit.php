<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Service\Manager\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;

abstract class SubscriptionCommit
{
    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var SubscriptionManager
     */
    protected $_subscriptionManager;
}
