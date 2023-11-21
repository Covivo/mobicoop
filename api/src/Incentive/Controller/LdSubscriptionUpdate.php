<?php

namespace App\Incentive\Controller;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LdSubscriptionUpdate
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;

        $this->_journeyManager = $journeyManager;
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
