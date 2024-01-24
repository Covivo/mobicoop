<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/recovery")
 */
class JourneysRecoveryController extends AbstractController
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
     * @Route("/journeys")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function proofsRecovery(Request $request)
    {
        $type = $request->get('type');

        if (is_null($type)) {
            throw new BadRequestHttpException('The mandatory type parameter is missing.');
        }

        return new JsonResponse($this->_subscriptionManager->proofsRecover($type, $request->get('user')));
    }
}
