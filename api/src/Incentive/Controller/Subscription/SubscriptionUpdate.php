<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Service\Manager\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class SubscriptionUpdate
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

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;

        $this->_subscriptionManager = $subscriptionManager;
    }
}
