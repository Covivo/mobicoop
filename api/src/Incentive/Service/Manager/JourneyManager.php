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
     * @var TimestampTokenManager
     */
    private $_timestampTokenManager;

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
        TimestampTokenManager $timestampTokenManager,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_timestampTokenManager = $timestampTokenManager;
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
            'Identifiant du trajet' => 'Proposal_'.$proposal->getId(),
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

        $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_COMMITMENT);

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

        $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_COMMITMENT);

        $this->_em->flush();
    }

    /**
     * Step 17 - Electronic payment is validated for a long distance journey.
     */
    public function receivingElectronicPayment(CarpoolPayment $carpoolPayment)
    {
        /**
         * @var CarpoolItem[]
         */
        $carpoolItems = $this->_getCarpoolItemsFromCarpoolPayment($carpoolPayment);

        foreach ($carpoolItems as $carpoolItem) {
            $this->setDriver($carpoolItem->getCreditorUser());

            $subscription = $this->_driver->getLongDistanceSubscription();

            if (is_null($subscription)) {
                continue;
            }

            $longDistanceJourneysNumber = count($subscription->getJourneys()->toArray());

            if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $longDistanceJourneysNumber) {
                continue;
            }

            if (!$this->_journeyValidation->isCarpoolItemValidLongDistanceJourney($carpoolItem)) {
                continue;
            }

            if (
                $this->_isLDJourneyCommitmentJourney($subscription, $carpoolItem)
                || (
                    empty($subscription->getJourneys()->toArray())
                    && !is_null($subscription->getCommitmentProofTimestampToken())
                )
            ) {
                $journey = is_null($subscription->getCommitmentProofJourney())
                    ? new LongDistanceJourney() : $subscription->getCommitmentProofJourney();

                $params = [
                    'Date de partage des frais' => $carpoolPayment->getUpdatedDate()->format(self::DATE_FORMAT),
                    "Attestation sur l'Honneur" => $this->_honourCertificateService->generateHonourCertificate(),
                ];

                $patchResponse = $this->patchSubscription($this->getDriverLongSubscriptionId(), $params);
                $subscription->addLog($patchResponse, Log::TYPE_ATTESTATION);

                $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

                $subscription->setExpirationDate($this->getExpirationDate());
            } else {
                $journey = new LongDistanceJourney();
            }

            $journey->updateJourney(
                $carpoolItem,
                $carpoolPayment,
                $this->getCarpoolersNumber($carpoolItem->getAsk()),
                $this->getAddressesLocality($carpoolItem)
            );
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

            $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

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
            return
                $payment->getUser()->getId() === $carpoolProof->getPassenger()->getId()
                && $this->_journeyValidation->isPaymentValidForEEC($payment);
        }));

        return !empty($carpoolPayments) ? $carpoolPayments[0] : null;
    }

    private function _getCarpoolItemsFromCarpoolPayment(CarpoolPayment $carpoolPayment): array
    {
        return array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth());
        });
    }
}
