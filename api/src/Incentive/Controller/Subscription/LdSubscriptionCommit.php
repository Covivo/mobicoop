<?php

namespace App\Incentive\Controller\Subscription;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceSubscription;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LdSubscriptionCommit extends SubscriptionCommit
{
    public function __invoke(LongDistanceSubscription $subscription)
    {
        /**
         * @var Proposal
         */
        $proposal = $this->_em->getRepository(Proposal::class)->find($this->_request->get('initial_proposal'));

        if (is_null($proposal)) {
            throw new NotFoundHttpException('The requested journey (Proposal) was not found');
        }

        $pushOnlyMode = boolval($this->_request->get('push_only'));

        if ($subscription->getUser()->getId() != $proposal->getUser()->getId()) {
            throw new BadRequestHttpException('A journey can initiate a subscription only if the user associated with the subscription is the one who posted the trip');
        }

        $this->_subscriptionManager->commitSubscription($subscription, $proposal, $pushOnlyMode);

        return $subscription;
    }
}
