<?php

namespace App\Incentive\Controller\Subscription;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SdSubscriptionCommit extends SubscriptionCommit
{
    public function __invoke(ShortDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_request->get('carpool_proof'));

        $pushOnlyMode = boolval($this->_request->get('push_only'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The requested journey (CarpoolProof) was not found');
        }

        if ($subscription->getUser()->getId() != $carpoolProof->getDriver()->getId()) {
            throw new BadRequestHttpException('A journey can initiate a subscription only if the user associated with the subscription is the one who posted the trip');
        }

        $this->_subscriptionManager->commitSubscription($subscription, $carpoolProof, $pushOnlyMode);

        return $subscription;
    }
}
