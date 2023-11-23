<?php

namespace App\Incentive\Controller\Subscription;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LdSubscriptionUpdate extends SubscriptionUpdate
{
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        parent::__construct($requestStack, $em, $journeyManager);
    }

    public function __invoke(LongDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolPayment
         */
        $carpoolPayment = $this->_em->getRepository(CarpoolPayment::class)->find($this->_request->get('carpool_payment'));

        $pushOnly = boolval($this->_request->get('push_only'));

        if (is_null($carpoolPayment)) {
            throw new NotFoundHttpException('The requested payment was not found');
        }

        $this->_journeyManager->receivingElectronicPayment($carpoolPayment, $pushOnly);

        return $subscription;
    }
}
