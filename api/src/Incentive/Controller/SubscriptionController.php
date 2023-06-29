<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route(
     *      "/verify/{subscriptionType}/{subscriptionId}",
     *      requirements={
     *          "subscriptionId":"\d+"
     *      }
     * )
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

    /**
     * @Route(
     *      "/timestamps/{subscriptionType}/{subscriptionId}",
     *      requirements={
     *          "subscriptionId":"\d+"
     *      }
     * )
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getSubscriptionMissingTimestamps(string $subscriptionType, string $subscriptionId)
    {
        return new JsonResponse(
            [
                'code' => Response::HTTP_OK,
                'message' => $this->_subscriptionManager->setUserSubscriptionTimestamps($subscriptionType, $subscriptionId),
            ]
        );
    }
}
