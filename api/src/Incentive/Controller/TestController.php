<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/incentives/test")
 */
class TestController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var MobConnectSubscriptionManager
     */
    private $_mobConnectSubscriptionManager;

    public function __construct(EntityManagerInterface $em, MobConnectSubscriptionManager $mobConnectSubscriptionManager)
    {
        $this->_em = $em;
        $this->_mobConnectSubscriptionManager = $mobConnectSubscriptionManager;
    }

    /**
     * @Route("/")
     */
    public function test()
    {
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find(24);

        $this->_mobConnectSubscriptionManager->updateSubscription($carpoolProof);

        return new Response('Ok');
    }

    /**
     * TODO:
     * Writes the eligibility test logs of carpool proofs passed in parameters.
     *
     * @Route("/carpools/check")
     */
    public function checkCarpools(Request $request)
    {
        $carpoolProofIds = explode(',', $request->get('carpoolProofs'));

        $carpoolProofs = [];

        foreach ($carpoolProofIds as $key => $id) {
            $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find(intval($id));

            if (!is_null($carpoolProof)) {
                array_push($carpoolProofs, $carpoolProof);
            }
        }

        return new JsonResponse($carpoolProofs);
    }

    /**
     * Returns users EEC subscriptions.
     *
     * @Route("/users/check")
     */
    public function checkSubscriptions(Request $request)
    {
        $ids = explode(',', $request->get('users'));

        $returnedSubscriptions = [];

        foreach ($ids as $key => $id) {
            $user = $this->_em->getRepository(User::class)->find(intval($id));

            $userSubscriptions = $this->_mobConnectSubscriptionManager->getUserSubscriptions($user);

            $subscription = new \stdClass();
            $subscription->userId = $user->getId();
            $subscription->shortDistanceSubscription = $userSubscriptions[0]->getShortDistanceSubscriptions();
            $subscription->longDistanceSubscription = $userSubscriptions[0]->getLongDistanceSubscriptions();
            $subscription->nbPendingProofs = $userSubscriptions[0]->getNbPendingProofs();
            $subscription->nbValidatedProofs = $userSubscriptions[0]->getNbValidatedProofs();
            $subscription->nbRejectedProofs = $userSubscriptions[0]->getNbRejectedProofs();

            array_push($returnedSubscriptions, $subscription);
        }

        return new JsonResponse($returnedSubscriptions);
    }
}
