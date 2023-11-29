<?php

namespace App\Incentive\Controller\Subscription;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SdSubscriptionCommit extends SubscriptionCommit
{
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        parent::__construct($requestStack, $em, $journeyManager);
    }

    public function __invoke(ShortDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_request->get('carpool_proof'));

        $pushOnly = boolval($this->_request->get('push_only'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The requested journey (CarpoolProof) was not found');
        }

        if ($subscription->getUser()->getId() != $carpoolProof->getDriver()->getId()) {
            throw new BadRequestHttpException('A journey can initiate a subscription only if the user associated with the subscription is the one who posted the trip');
        }

        $this->_em->remove($subscription->getCommitmentProofJourney());

        $subscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstShortDistanceJourney($carpoolProof, $pushOnly);

        return $subscription;
    }
}
