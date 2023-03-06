<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Service\Checker\JourneyChecker;
use App\Incentive\Service\LoggerService;
use Doctrine\ORM\EntityManagerInterface;

class JourneyManager extends MobConnectManager
{
    public function __construct(
        EntityManagerInterface $em,
        JourneyChecker $journeyChecker,
        LoggerService $loggerService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $journeyChecker, $loggerService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);
    }

    /**
     * Step 9 - Long distance journey.
     */
    public function declareFirstLongDistanceJourney(Proposal $proposal)
    {
        $this->setDriver($proposal->getUser());

        $params = [
            'Date de publication du trajet' => $proposal->getCreatedDate()->format('Y-m-d'),
        ];

        $response = $this->patchSubscription($this->getDriver()->getLongDistanceSubscription()->getSubscriptionId(), $params);

        $subscription = $this->getDriver()->getLongDistanceSubscription();
        $subscription->setCommitmentProofDate(new \DateTime());

        $journey = new LongDistanceJourney($proposal);
        $journey->setHttpRequestStatus($response->getCode());

        $subscription->addLongDistanceJourney($journey);

        $this->_em->flush();
    }

    /**
     * Step 17 - Long distance journey.
     */
    public function updateFirstLongDistanceJouney()
    {
    }

    /**
     * Step 9 - Short distance journey.
     */
    public function declareFirstShortDistanceJourney(CarpoolProof $carpoolProof)
    {
        $this->setDriver($carpoolProof->getDriver());

        $params = [
            'Identifiant du Trajet' => $this->getRPCOperatorId($carpoolProof->getId()),
            'Date de départ du trajet' => $carpoolProof->getPickUpPassengerDate()->format('Y-m-d'),
        ];

        $response = $this->patchSubscription($this->getDriver()->getShortDistanceSubscription()->getSubscriptionId(), $params);

        $subscription = $this->getDriver()->getShortDistanceSubscription();
        $subscription->setCommitmentProofDate(new \DateTime());

        $journey = new ShortDistanceJourney($carpoolProof);
        $journey->setHttpRequestStatus($response->getCode());

        $subscription->addShortDistanceJourney($journey);

        $this->_em->flush();
    }

    /**
     * Step 17 - Short distance journey.
     */
    public function updateFirstShortDistanceJourney()
    {
    }
}
