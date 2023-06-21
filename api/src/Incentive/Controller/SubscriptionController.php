<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     *      "/verify/{subscriptionType}/{subscriptionId}",
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
