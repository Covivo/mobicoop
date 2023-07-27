<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Event\ConfirmDirectPaymentEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/relaunch")
 */
class RelaunchController extends AbstractController
{
    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    public function __construct(EventDispatcherInterface $eventDispatcher, JourneyValidation $journeyValidation)
    {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_journeyValidation = $journeyValidation;
    }

    /**
     * @Route("/proposal/{proposal}")
     */
    public function relaunchProposal(Proposal $proposal)
    {
        if ($this->_journeyValidation->isPublishedJourneyValidLongECCJourney($proposal)) {
            $event = new FirstLongDistanceJourneyPublishedEvent($proposal);
            $this->_eventDispatcher->dispatch(FirstLongDistanceJourneyPublishedEvent::NAME, $event);
        }

        return new Response('Processing is complete');
    }

    /**
     * @Route("/payment/{carpoolItem}")
     */
    public function relaunchPayment(CarpoolItem $carpoolItem)
    {
        switch ($carpoolItem->getCreditorStatus()) {
            case CarpoolItem::CREDITOR_STATUS_ONLINE:
                // code...
                break;

            case CarpoolItem::CREDITOR_STATUS_DIRECT:
                $event = new ConfirmDirectPaymentEvent($carpoolItem);
                $this->_eventDispatcher->dispatch(ConfirmDirectPaymentEvent::NAME, $event);

                break;
        }

        return new Response('Processing is complete');
    }

    /**
     * Short distance - Step 17.
     *
     * @Route("/proof-validate/{carpoolProof}", requirements={"carpoolProof"="\d+"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function relaunchProofValidation(CarpoolProof $carpoolProof)
    {
        $event = new CarpoolProofValidatedEvent($carpoolProof);
        $this->_eventDispatcher->dispatch(CarpoolProofValidatedEvent::NAME, $event);

        return new Response('Processing is complete');
    }
}
