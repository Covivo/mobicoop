<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;

class JourneyManager extends MobConnectManager
{
    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    public function __construct(
        EntityManagerInterface $em,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_journeyValidation = $journeyValidation;
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

        $log = 204 === $response->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the proposal '.$proposal->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$proposal->getId()
        ;

        $this->_loggerService->log($log);

        $subscription->setCommitmentProofDate(new \DateTime());

        $response = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
        if (!is_null($response->getCommitmentProofTimestampToken())) {
            $subscription->setCommitmentProofTimestampToken($response->getCommitmentProofTimestampToken());
            $subscription->setCommitmentProofTimestampSigningTime($response->getCommitmentProofTimestampSigningTime());
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
            'Identifiant du trajet' => $this->getRPCOperatorId($carpoolProof->getId()),
            'Date de départ du trajet' => $carpoolProof->getPickUpPassengerDate()->format('Y-m-d'),
        ];

        $response = $this->patchSubscription($this->getDriver()->getShortDistanceSubscription()->getSubscriptionId(), $params);

        $subscription = $this->getDriver()->getShortDistanceSubscription();

        $log = 204 === $response->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the carpoolProof '.$carpoolProof->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$carpoolProof->getId()
        ;

        $this->_loggerService->log($log);

        $subscription->setCommitmentProofDate(new \DateTime());

        $response = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
        if (!is_null($response->getCommitmentProofTimestampToken())) {
            $subscription->setCommitmentProofTimestampToken($response->getCommitmentProofTimestampToken());
            $subscription->setCommitmentProofTimestampSigningTime($response->getCommitmentProofTimestampSigningTime());
        }

        $this->_em->flush();
    }

    /**
     * Step 17 - Direct payment is confirmed for a long distance journey.
     */
    public function directPaymentConfirmed(CarpoolItem $carpoolItem)
    {
        $this->setDriver($carpoolItem->getCreditorUser());

        $subscription = $this->_driver->getLongDistanceSubscription();

        if (is_null($subscription)) {
            return;
        }

        $longDistanceJourneysNumber = count($subscription->getLongDistanceJourneys()->toArray());

        if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $longDistanceJourneysNumber) {
            return;
        }

        // Array of carpoolProof where driver is the carpoolItem driver
        $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) {
            return $carpoolProof->getDriver() === $this->_driver;
        });

        foreach ($filteredCarpoolProofs as $carpoolProof) {
            if (!$this->_journeyValidation->isCarpoolProofValidLongDistanceJourney($carpoolProof)) {
                continue;
            }

            $journey = new LongDistanceJourney();

            if (!isset($carpoolPayment) || is_null($carpoolPayment)) {
                $carpoolPayment = $this->_getCarpoolPaymentFromCarpoolItem($carpoolItem, $carpoolProof);
            }

            if (0 === $longDistanceJourneysNumber) {
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
     * Step 17 - Electronic payment is validated for a long distance journey.
     */
    public function receivingElectronicPayment(CarpoolPayment $carpoolPayment)
    {
        /**
         * @var CarpoolProof[]
         */
        $carpoolProofs = $this->_getCarpoolProofsFromCarpoolPayment($carpoolPayment);

        foreach ($carpoolProofs as $carpoolProof) {
            $this->setDriver($carpoolProof->getDriver());

            $subscription = $this->_driver->getLongDistanceSubscription();

            if (is_null($subscription)) {
                return;
            }

            $longDistanceJourneysNumber = count($subscription->getLongDistanceJourneys()->toArray());

            if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $longDistanceJourneysNumber) {
                return;
            }

            if (!$this->_journeyValidation->isCarpoolProofValidLongDistanceJourney($carpoolProof)) {
                continue;
            }

            $journey = new LongDistanceJourney();

            if (0 === $longDistanceJourneysNumber) {
                $params = [
                    'Date de partage des frais' => $carpoolPayment->getUpdatedDate(),
                    "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(),
                ];

                $response = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
                $journey->setHttpRequestStatus($response->getCode());

                $response = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
                if (!is_null($response->getHonorCertificateProofTimestampToken())) {
                    $subscription->setHonorCertificateProofTimestampToken($response->getHonorCertificateProofTimestampToken());
                    $subscription->setHonorCertificateProofTimestampSigningTime($response->getHonorCertificateProofTimestampSigningTime());
                }
            }

            $journey->updateJourney($carpoolProof, $carpoolPayment, $this->getCarpoolersNumber($carpoolProof->getAsk()));
            $subscription->addLongDistanceJourney($journey);

            if (self::LONG_DISTANCE_TRIP_THRESHOLD === $longDistanceJourneysNumber) {
                $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
            }
        }

        $this->_em->flush();
    }

    /**
     * Step 17 - Validation of proof for a short distance journey,.
     */
    public function validationOfProof(CarpoolProof $carpoolProof)
    {
        $this->setDriver($carpoolProof->getDriver());

        $subscription = $this->getDriver()->getShortDistanceSubscription();

        if (is_null($subscription)) {
            return;
        }

        $shortDistanceJourneysNumber = count($subscription->getShortDistanceJourneys()->toArray());

        // Checks :
        //    - The maximum journey threshold has not been reached
        //    - The journey is a short distance journey
        //    - The journey is a C type
        //    - The journey origin and/or destination is the reference country
        if (
            self::SHORT_DISTANCE_TRIP_THRESHOLD <= $shortDistanceJourneysNumber
            || is_null($carpoolProof->getAsk())
            || is_null($carpoolProof->getAsk()->getMatching())
            || $this->_journeyValidation->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            || CarpoolProof::TYPE_HIGH !== $carpoolProof->getType()
            || !$this->_journeyValidation->isOriginOrDestinationFromFrance($carpoolProof)
        ) {
            return;
        }

        $journey = new ShortDistanceJourney();

        if (empty($subscription->getShortDistanceJourneys()->toArray())) {
            $params = [
                "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(false),
            ];

            $response = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
            $journey->setHttpRequestStatus($response->getCode());

            $response = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
            if (!is_null($response->getHonorCertificateProofTimestampToken())) {
                $subscription->setHonorCertificateProofTimestampToken($response->getHonorCertificateProofTimestampToken());
                $subscription->setHonorCertificateProofTimestampSigningTime($response->getHonorCertificateProofTimestampSigningTime());
            }
        }

        $journey->updateJourney($carpoolProof, $this->getRPCOperatorId($carpoolProof->getId()), $this->getCarpoolersNumber($carpoolProof->getAsk()));
        $subscription->addShortDistanceJourney($journey);

        if (self::SHORT_DISTANCE_TRIP_THRESHOLD === $shortDistanceJourneysNumber) {
            $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
    }

    private function _getCarpoolPaymentFromCarpoolItem(CarpoolItem $carpoolItem, CarpoolProof $carpoolProof): CarpoolPayment
    {
        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function ($payment) use ($carpoolProof) {
            return $payment->getUser()->getId() === $carpoolProof->getPassenger()->getId();
        }));

        if (count($carpoolPayments) > 1) {
            return null;
        }

        return $carpoolPayments[0];
    }

    private function _getCarpoolPaymentFromCarpoolProof($carpoolProof): ?CarpoolPayment
    {
        if (
            is_null($carpoolProof->getAsk())
            || is_null($carpoolProof->getAsk()->getCarpoolItems())
        ) {
            return null;
        }

        $carpoolItems = array_filter($carpoolProof->getAsk()->getCarpoolItems(), function ($item) use ($carpoolProof) {
            return
                $item->getCreditorUser()->getId() === $carpoolProof->getDriver()->getId()
                && $item->getDebtorUser()->getId() === $carpoolProof->getPassenger()->getId()
                && $item->getItemDate()->format('Y-m-d') === $carpoolProof->getStartDriverDate()->format('Y-m-d')
            ;
        });

        if (
            empty($carpoolItems)
            || count($carpoolItems) > 1
        ) {
            return null;
        }

        $carpoolItem = array_values($carpoolItems)[0];

        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function ($payment) use ($carpoolProof) {
            return $payment->getUser()->getId() === $carpoolProof->getPassenger()->getId();
        }));

        if (count($carpoolPayments) > 1) {
            return null;
        }

        return $carpoolPayments[0];
    }

    private function _getCarpoolProofsFromCarpoolPayment(CarpoolPayment $carpoolPayment): array
    {
        /**
         * @var CarpoolItem[]
         */
        $filteredCarpoolItems = array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
            ;
        });

        $carpoolProofs = [];

        foreach ($filteredCarpoolItems as $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            // Checks :
            //    - The driver has purchased a long-distance journey incentive
            //    - The journey is a long distance journey
            //    - The journey origin and/or destination is the référence country
            if (
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
                && !is_null($driver->getLongDistanceSubscription())
                && !is_null($carpoolItem->getAsk())
                && !is_null($carpoolItem->getAsk()->getMatching())
                && $this->_journeyValidation->isDistanceLongDistance($carpoolItem->getAsk()->getMatching()->getCommonDistance())
                && !empty($carpoolItem->getAsk()->getMatching()->getWaypoints())
                && $this->_journeyValidation->isOriginOrDestinationFromFrance($carpoolItem->getAsk()->getMatching())
            ) {
                $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) use ($driver) {
                    return $carpoolProof->getDriver() === $driver;
                });

                $carpoolProofs = array_merge($carpoolProofs, $filteredCarpoolProofs);
            }

            return $carpoolProofs;
        }
    }

    private function _isPaymentValidated(CarpoolProof $carpoolProof): bool
    {
        if (is_null($this->_getCarpoolPaymentFromCarpoolProof($carpoolProof))) {
            return false;
        }

        return true;
    }
}
