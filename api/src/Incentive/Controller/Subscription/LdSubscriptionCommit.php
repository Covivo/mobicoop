<?php

namespace App\Incentive\Controller\Subscription;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LdSubscriptionCommit extends SubscriptionCommit
{
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        parent::__construct($requestStack, $em, $journeyManager);
    }

    public function __invoke(LongDistanceSubscription $subscription)
    {
        /**
         * @var Proposal
         */
        $initialProposal = $this->_em->getRepository(Proposal::class)->find($this->_request->get('initial_proposal'));

        $pushOnly = boolval($this->_request->get('push_only'));

        if (is_null($initialProposal)) {
            throw new NotFoundHttpException('The requested journey (Proposal) was not found');
        }

        if ($subscription->getUser()->getId() != $initialProposal->getUser()->getId()) {
            throw new BadRequestHttpException('A journey can initiate a subscription only if the user associated with the subscription is the one who posted the trip');
        }

        $subscription->reset();

        $this->_em->flush();

        $this->_journeyManager->declareFirstLongDistanceJourney($initialProposal, $pushOnly);

        return $subscription;
    }
}
