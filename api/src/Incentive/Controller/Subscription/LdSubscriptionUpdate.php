<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Payment\Entity\CarpoolPayment;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LdSubscriptionUpdate extends SubscriptionUpdate
{
    public function __invoke(LongDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolPayment
         */
        $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find($this->_request->get('carpool_payment'));

        $pushOnlyMode = boolval($this->_request->get('push_only'));

        if (is_null($carpoolPayment)) {
            throw new NotFoundHttpException('The requested payment was not found');
        }

        $this->_subscriptionManager->validateSubscription($carpoolPayment, $pushOnlyMode);

        return $subscription;
    }
}
