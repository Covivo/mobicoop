<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Service\Checker\JourneyChecker;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;

class JourneyManager extends MobConnectManager
{
    /**
     * @var LongDistanceJourneyRepository
     */
    private $_longDistanceRepository;

    public function __construct(
        EntityManagerInterface $em,
        JourneyChecker $journeyChecker,
        LoggerService $loggerService,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $journeyChecker, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_longDistanceRepository = $longDistanceJourneyRepository;
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

        $response = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);

        $subscription = $this->getDriver()->getLongDistanceSubscription();
        $subscription->setCommitmentProofDate(new \DateTime());

        $this->_em->flush();
    }

    /**
     * Step 17 - Long distance journey.
     */
    public function receivingPayment(CarpoolPayment $carpoolPayment)
    {
        /**
         * @var CarpoolProof[]
         */
        $carpoolProofs = $this->getCarpoolProofsFromCarpoolPayment($carpoolPayment);

        foreach ($carpoolProofs as $carpoolProof) {
            $this->setDriver($carpoolProof->getDriver());

            $subscription = $this->_driver->getLongDistanceSubscription();

            $longDistanceJourneysNumber = count($this->_driver->getLongDistanceSubscription()->getLongDistanceJourneys()->toArray());

            if (self::LONG_DISTANCE_TRIP_THRESHOLD >= $longDistanceJourneysNumber) {
                continue;
            }

            $journey = new LongDistanceJourney();

            if (empty($this->_driver->getLongDistanceSubscription()->getLongDistanceJourneys()->toArray())) {
                $params = [
                    'Date de partage des frais' => $carpoolPayment->getUpdatedDate(),
                    "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(),
                ];

                $response = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
                $journey->setHttpRequestStatus($response->getCode());
            }

            $journey->updateJourney($carpoolProof, $carpoolPayment, $this->getCarpoolersNumber($carpoolProof->getAsk()));
            $subscription->addLongDistanceJourney($journey);
        }

        if (self::LONG_DISTANCE_TRIP_THRESHOLD === $longDistanceJourneysNumber) {
            $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
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

        $this->_em->flush();
    }

    /**
     * Step 17 - Short distance journey.
     */
    public function validationOfProof(CarpoolProof $carpoolProof)
    {
        $this->setDriver($carpoolProof->getDriver());

        $subscription = $this->getDriver()->getShortDistanceSubscription();

        $shortDistanceJourneysNumber = count($subscription->getShortDistanceJourneys()->toArray());

        // Checks :
        //    - The driver has purchased a short-distance journey incentive
        //    - The short distance journey number is not
        //    - The journey is not a long distance journey
        //    - The journey is a C type
        //    - The journey origin and/or destination is the référence country
        if (
            is_null($subscription)
            || self::SHORT_DISTANCE_TRIP_THRESHOLD >= $shortDistanceJourneysNumber
            || is_null($carpoolProof->getAsk())
            || is_null($carpoolProof->getAsk()->getMatching())
            || CarpoolProof::TYPE_HIGH !== $carpoolProof->getType()
            || $this->_journeyChecker->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            || !$this->_journeyChecker->isOriginOrDestinationFromFrance($carpoolProof)
        ) {
            return false;
        }

        $journey = new ShortDistanceJourney();

        if (empty($subscription->getShortDistanceJourneys())) {
            $params = [
                "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(false),
            ];

            $response = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
            $journey->setHttpRequestStatus($response->getCode());
        }

        $journey->updateJourney($carpoolProof, $this->getRPCOperatorId($carpoolProof->getId()), $this->getCarpoolersNumber($carpoolProof->getAsk()));
        $subscription->addShortDistanceJourney($journey);

        if (self::SHORT_DISTANCE_TRIP_THRESHOLD === $shortDistanceJourneysNumber) {
            $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
    }
}
