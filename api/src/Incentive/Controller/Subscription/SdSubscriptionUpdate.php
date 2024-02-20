<?php

namespace App\Incentive\Controller\Subscription;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SdSubscriptionUpdate extends SubscriptionUpdate
{
    public function __invoke(ShortDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_request->get('carpool_proof'));

        $pushOnlyMode = boolval($this->_request->get('push_only'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The requested proof was not found');
        }

        $this->_subscriptionManager->validateSubscription($carpoolProof, $pushOnlyMode);

        return $subscription;
    }
}
