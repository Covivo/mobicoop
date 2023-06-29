<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/subscriptions")
 */
class SubscriptionController extends AbstractController
{
    /**
     * @var @Request
     */
    private $_request;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(RequestStack $requestStack, SubscriptionManager $subscriptionManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_subscriptionManager = $subscriptionManager;
    }

    /**
     * Verify one or many subscriptions.
     *
     * @Route("/verify")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function verifySubscription()
    {
        return $this->_subscriptionManager->verifySubscriptionFromControllerCommand(
            $this->_request->get('subscription_type'),
            $this->_request->get('subscription_id')
        );
    }
}
