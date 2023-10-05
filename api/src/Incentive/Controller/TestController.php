<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\JourneyManager;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/EEC/test")
 */
class TestController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        JourneyValidation $journeyValidation,
        JourneyManager $journeyManager,
        SubscriptionManager $subscriptionManager
    ) {
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_journeyValidation = $journeyValidation;
        $this->_journeyManager = $journeyManager;
        $this->_subscriptionManager = $subscriptionManager;
    }

    /**
     * @Route("/{carpoolPayment}")
     */
    public function test(CarpoolPayment $carpoolPayment)
    {
        $this->_journeyManager->receivingElectronicPayment($carpoolPayment);

        return new Response('Ok');
    }
}
