<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/subscriptions")
 */
class SubscriptionController extends AbstractController
{
    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->_subscriptionManager = $subscriptionManager;
    }

    /**
     * @Route(
     *      "/{subscriptionType}/{subscriptionId}",
     *      requirements={
     *          "subscriptionId":"\d+"
     *      }
     * )
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function verifySubscription(string $subscriptionType, string $subscriptionId)
    {
        return $this->_subscriptionManager->verifySubscriptionFromControllerCommand($subscriptionType, $subscriptionId);
    }
}
