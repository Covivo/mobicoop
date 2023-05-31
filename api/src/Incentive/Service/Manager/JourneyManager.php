<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Event\FirstLongDistanceJourneyPublishedEvent;
use App\Incentive\Event\FirstShortDistanceJourneyPublishedEvent;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Event\ElectronicPaymentValidatedEvent;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JourneyManager extends MobConnectManager
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_journeyValidation = $journeyValidation;
    }

    public function userProofsRecovery(User $driver, string $subscriptionType): bool
    {
        $result = false;

        $carpoolProofs = $this->_carpoolProofRepository->findUserCEEEligibleProof($driver, $subscriptionType);

        foreach ($carpoolProofs as $proof) {
            switch ($subscriptionType) {
                case MobConnectManager::LONG_SUBSCRIPTION_TYPE:
                    if (is_null($driver->getLongDistanceSubscription()->getCommitmentProofDate()) && empty($driver->getLongDistanceSubscription()->getJourneys())) {
                        $proposal = $driver === $proof->getAsk()->getMatching()->getProposalOffer()->getUser()
                            ? $proof->getAsk()->getMatching()->getProposalOffer()->getUser() : $proof->getAsk()->getMatching()->getProposalRequest()->getUser();

                        $event = new FirstLongDistanceJourneyPublishedEvent($proposal);
                        $this->_eventDispatcher->dispatch(FirstLongDistanceJourneyPublishedEvent::NAME, $event);
                    }

                    $carpoolPayment = $this->_getCarpoolPaymentFromCarpoolProof($proof);

                    $event = new ElectronicPaymentValidatedEvent($carpoolPayment);
                    $this->_eventDispatcher->dispatch(ElectronicPaymentValidatedEvent::NAME, $event);

                    break;

                case MobConnectManager::SHORT_SUBSCRIPTION_TYPE:
                    if (is_null($driver->getShortDistanceSubscription()->getCommitmentProofDate()) && empty($driver->getShortDistanceSubscription()->getJourneys())) {
                        $event = new FirstShortDistanceJourneyPublishedEvent($proof);
                        $this->_eventDispatcher->dispatch(FirstShortDistanceJourneyPublishedEvent::NAME, $event);
                    }

                    $event = new CarpoolProofValidatedEvent($proof);
                    $this->_eventDispatcher->dispatch(CarpoolProofValidatedEvent::NAME, $event);

                    break;
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Step 9 - Long distance journey.
     */
    public function declareFirstLongDistanceJourney(Proposal $proposal)
    {
        $this->setDriver($proposal->getUser());

        $params = [
            'Date de publication du trajet' => $proposal->getCreatedDate()->format(self::DATE_FORMAT),
        ];

        $patchResponse = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);

        $subscription = $this->getDriver()->getLongDistanceSubscription();
        $subscription->addLog($patchResponse, Log::TYPE_COMMITMENT);

        $log = 204 === $patchResponse->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the proposal '.$proposal->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$proposal->getId();

        $this->_loggerService->log($log);

        $journey = new LongDistanceJourney($proposal);

        $subscription->setCommitmentProofJourney($journey);
        $subscription->setCommitmentProofDate(new \DateTime());

        $timestampResponse = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
        $subscription->addLog($timestampResponse, Log::TYPE_TIMESTAMP_COMMITMENT);

        if (!is_null($timestampResponse->getCommitmentProofTimestampToken())) {
            $subscription->setCommitmentProofTimestampToken($timestampResponse->getCommitmentProofTimestampToken());
            $subscription->setCommitmentProofTimestampSigningTime($timestampResponse->getCommitmentProofTimestampSigningTime());
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
            'Date de départ du trajet' => $carpoolProof->getPickUpDriverDate()->format(self::DATE_FORMAT),
        ];

        $patchResponse = $this->patchSubscription($this->getDriver()->getShortDistanceSubscription()->getSubscriptionId(), $params);

        $subscription = $this->getDriver()->getShortDistanceSubscription();
        $subscription->addLog($patchResponse, Log::TYPE_COMMITMENT);

        $log = 204 === $patchResponse->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the carpoolProof '.$carpoolProof->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$carpoolProof->getId();

        $this->_loggerService->log($log);

        $journey = new ShortDistanceJourney($carpoolProof);

        $subscription->setCommitmentProofJourney($journey);
        $subscription->setCommitmentProofDate(new \DateTime());

        $timestampResponse = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
        $subscription->addLog($timestampResponse, Log::TYPE_TIMESTAMP_COMMITMENT);

        if (!is_null($timestampResponse->getCommitmentProofTimestampToken())) {
            $subscription->setCommitmentProofTimestampToken($timestampResponse->getCommitmentProofTimestampToken());
            $subscription->setCommitmentProofTimestampSigningTime($timestampResponse->getCommitmentProofTimestampSigningTime());
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

            $longDistanceJourneysNumber = count($subscription->getJourneys()->toArray());

            if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $longDistanceJourneysNumber) {
                return;
            }

            if (!$this->_journeyValidation->isCarpoolProofValidLongDistanceJourney($carpoolProof)) {
                continue;
            }

            $journey = $this->getLongDistanceCommitmentJourney($carpoolProof, $subscription);

            if (!is_null($journey)) {
                $params = [
                    'Date de partage des frais' => $carpoolPayment->getUpdatedDate()->format(self::DATE_FORMAT),
                    "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(),
                ];

                $patchResponse = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
                $subscription->addLog($patchResponse, Log::TYPE_ATTESTATION);

                $timestampResponse = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
                $subscription->addLog($timestampResponse, Log::TYPE_TIMESTAMP_ATTESTATION);

                if (!is_null($timestampResponse->getHonorCertificateProofTimestampToken())) {
                    $subscription->setHonorCertificateProofTimestampToken($timestampResponse->getHonorCertificateProofTimestampToken());
                    $subscription->setHonorCertificateProofTimestampSigningTime($timestampResponse->getHonorCertificateProofTimestampSigningTime());
                }

                $subscription->setExpirationDate($this->getExpirationDate());
            } else {
                $journey = new LongDistanceJourney();
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

        $shortDistanceJourneysNumber = count($subscription->getJourneys()->toArray());

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

        $journey = $this->getShortDistanceCommitmentJourney($carpoolProof, $subscription);

        if (!is_null($journey)) {
            $params = [
                "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(false),
            ];

            $patchResponse = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
            $subscription->addLog($patchResponse, Log::TYPE_ATTESTATION);

            $timestampResponse = $this->getDriverSubscriptionTimestamps($subscription->getSubscriptionId());
            $subscription->addLog($timestampResponse, Log::TYPE_TIMESTAMP_ATTESTATION);

            if (!is_null($timestampResponse->getHonorCertificateProofTimestampToken())) {
                $subscription->setHonorCertificateProofTimestampToken($timestampResponse->getHonorCertificateProofTimestampToken());
                $subscription->setHonorCertificateProofTimestampSigningTime($timestampResponse->getHonorCertificateProofTimestampSigningTime());
            }

            $subscription->setExpirationDate($this->getExpirationDate());
        } else {
            $journey = new ShortDistanceJourney($carpoolProof);
        }

        $journey->updateJourney($carpoolProof, $this->getRPCOperatorId($carpoolProof->getId()), $this->getCarpoolersNumber($carpoolProof->getAsk()));
        $subscription->addShortDistanceJourney($journey);

        if (self::SHORT_DISTANCE_TRIP_THRESHOLD === $shortDistanceJourneysNumber) {
            $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
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
                && $item->getItemDate()->format(self::DATE_FORMAT) === $carpoolProof->getStartDriverDate()->format(self::DATE_FORMAT);
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

    private function _getCarpoolProofsFromCarpoolPayment(CarpoolPayment $carpoolPayment): array
    {
        /**
         * @var CarpoolItem[]
         */
        $filteredCarpoolItems = array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth());
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
                && CarpoolItem::CREDITOR_STATUS_ONLINE === $carpoolItem->getCreditorStatus()
            ) {
                $filteredCarpoolProofs = array_filter($carpoolItem->getAsk()->getCarpoolProofs(), function (CarpoolProof $carpoolProof) use ($driver) {
                    return $carpoolProof->getDriver() === $driver;
                });

                $carpoolProofs = array_merge($carpoolProofs, $filteredCarpoolProofs);
            }
        }

        return $carpoolProofs;
    }

    private function _isPaymentValidated(CarpoolProof $carpoolProof): bool
    {
        if (is_null($this->_getCarpoolPaymentFromCarpoolProof($carpoolProof))) {
            return false;
        }

        return true;
    }
}
