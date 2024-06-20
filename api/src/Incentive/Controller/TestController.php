<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\Incentive\Service\Validation\JourneyValidation;
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
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        JourneyValidation $journeyValidation,
        SubscriptionManager $subscriptionManager
    ) {
        $this->_em = $em;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_journeyValidation = $journeyValidation;
        $this->_subscriptionManager = $subscriptionManager;
    }

    /**
     * @Route("/{carpoolProof}")
     */
    public function test(CarpoolProof $carpoolProof)
    {
        $event = new CarpoolProofValidatedEvent($carpoolProof);
        $this->_eventDispatcher->dispatch(CarpoolProofValidatedEvent::NAME, $event);

        return new Response('Ok');
    }
}
