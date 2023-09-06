<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class JourneyManager extends MobConnectManager
{
    /**
     * @var TimestampTokenManager
     */
    protected $_timestampTokenManager;

    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var CarpoolItemRepository
     */
    private $_carpoolItemRepository;

    /**
     * @var LongDistanceJourneyRepository
     */
    private $_longDistanceJourneyRepository;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        CarpoolItemRepository $carpoolItemRepository,
        EntityManagerInterface $em,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        TimestampTokenManager $timestampTokenManager,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;

        $this->_journeyValidation = $journeyValidation;
    }

    public function userProofsRecovery(User $driver, string $subscriptionType): bool
    {
        $this->setDriver($driver);

        $result = false;

        switch ($subscriptionType) {
            case MobConnectManager::LONG_SUBSCRIPTION_TYPE:
                /**
                 * @var CarpoolItem[]
                 */
                $carpoolItems = $this->_carpoolItemRepository->findUserEECEligibleItem($driver);

                foreach ($carpoolItems as $item) {
                    if (
                        is_null($driver->getLongDistanceSubscription()->getCommitmentProofDate())
                        && empty($driver->getLongDistanceSubscription()->getJourneys())
                    ) {
                        $proposal = $item->getProposalAccordingUser($this->getDriver());

                        $this->declareFirstLongDistanceJourney($proposal);
                    }

                    $carpoolPayment = $this->_getCarpoolPaymentFromCarpoolItem($item);

                    if (!is_null($carpoolPayment) && CarpoolPayment::STATUS_SUCCESS === $carpoolPayment->getStatus()) {
                        $this->receivingElectronicPayment($carpoolPayment);
                    }

                    $result = true;
                }

                break;

            case MobConnectManager::SHORT_SUBSCRIPTION_TYPE:
                $carpoolProofs = $this->_carpoolProofRepository->findUserCEEEligibleProof($driver, $subscriptionType);

                foreach ($carpoolProofs as $carpoolProof) {
                    if (
                        is_null($driver->getShortDistanceSubscription()->getCommitmentProofDate())
                        && empty($driver->getShortDistanceSubscription()->getJourneys())
                    ) {
                        $this->declareFirstShortDistanceJourney($carpoolProof);
                    }

                    $this->validationOfProof($carpoolProof);

                    $result = true;
                }

                break;
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
            'Identifiant du trajet' => LongDistanceSubscription::COMMITMENT_PREFIX.$proposal->getId(),
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
    public function declareFirstShortDistanceJourney(CarpoolProof $carpoolProof): ShortDistanceJourney
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

        return $journey;
    }

    /**
     * Step 17 - Electronic payment is validated for a long distance journey.
     */
    public function receivingElectronicPayment(CarpoolPayment $carpoolPayment)
    {
        if (CarpoolPayment::STATUS_SUCCESS !== $carpoolPayment->getStatus()) {
            return;
        }

        $this->_loggerService->log('Step 17 - Processing the carpoolPayment ID'.$carpoolPayment->getId());

        /**
         * @var CarpoolItem[]
         */
        $carpoolItems = $this->_getCarpoolItemsFromCarpoolPayment($carpoolPayment);

        foreach ($carpoolItems as $carpoolItem) {
            if ($this->carpoolItemAlreadyTreated($carpoolItem)) {
                continue;
            }

            $this->setDriver($carpoolItem->getCreditorUser());

            $subscription = $this->_driver->getLongDistanceSubscription();

            if (is_null($subscription) || $subscription->hasExpired()) {
                continue;
            }

            $longDistanceJourneysNumber = count($subscription->getJourneys()->toArray());

            if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $longDistanceJourneysNumber) {
                continue;
            }

            if (!$this->_journeyValidation->isCarpoolItemValidLongDistanceJourney($carpoolItem)) {
                continue;
            }

            $this->_loggerService->log('Step 17 - Processing the carpoolItem ID'.$carpoolItem->getId());

            if (
                $this->_isLDJourneyCommitmentJourney($subscription, $carpoolItem)
                || (
                    empty($subscription->getJourneys()->toArray())
                    && !is_null($subscription->getCommitmentProofTimestampToken())
                )
            ) {
                $this->_loggerService->log('Step 17 - Processing for the commitment journey');

                $journey = is_null($subscription->getCommitmentProofJourney())
                    ? new LongDistanceJourney() : $subscription->getCommitmentProofJourney();

                $params = [
                    'Date de partage des frais' => $carpoolPayment->getUpdatedDate()->format(self::DATE_FORMAT),
                    "Attestation sur l'Honneur" => $this->getHonorCertificate(),
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

        $this->_loggerService->log('Step 17 - End of treatment');
    }

    /**
     * Step 17 - Validation of proof for a short distance journey,.
     */
    public function validationOfProof(CarpoolProof $carpoolProof)
    {
        $this->setDriver($carpoolProof->getDriver());

        $subscription = $this->getDriver()->getShortDistanceSubscription();

        if (is_null($subscription) || $subscription->hasExpired()) {
            return;
        }

        $shortDistanceJourneysNumber = count($subscription->getJourneys()->toArray());

        $commitmentJourney = $subscription->getCommitmentProofJourney();

        // There is not commitment journey
        if (is_null($commitmentJourney)) {
            if ($this->_journeyValidation->isStartedJourneyValidShortECCJourney($carpoolProof)) {
                $this->_loggerService->log('Step 17 - We declare a new commitment journey');

                $commitmentJourney = $this->declareFirstShortDistanceJourney($carpoolProof);
            } else {
                return;
            }
        }

        // Processing with commitment journey
        if ($commitmentJourney->getCarpoolProof()->getId() === $carpoolProof->getId()) {
            // The journey is not EEC compliant : we are removing it from short distance trips and resetting the subscription
            if (!$commitmentJourney->isCompliant()) {
                $this->resetShortDistanceSubscription($subscription, $commitmentJourney);

                return;
            }

            $params = [
                "Attestation sur l'Honneur" => $this->getHonorCertificate(false),
            ];

            $this->_loggerService->log('Step 17 - Journey update and sending honor attestation');
            $patchResponse = $this->patchSubscription($this->getDriverShortSubscriptionId(), $params);
            $subscription->addLog($patchResponse, Log::TYPE_ATTESTATION);

            $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

            $subscription->setExpirationDate($this->getExpirationDate());

            $commitmentJourney = $this->_updateShortDistanceJourney($commitmentJourney, $carpoolProof);
        } else {
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

            $this->_loggerService->log('Step 17 - Added a normal journey');
            $journey = new ShortDistanceJourney($carpoolProof);
            $journey = $this->_updateShortDistanceJourney($journey, $carpoolProof);
            $subscription->addShortDistanceJourney($journey);
        }

        if (self::SHORT_DISTANCE_TRIP_THRESHOLD === $shortDistanceJourneysNumber) {
            $subscription->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
    }

    public function resetShortDistanceSubscription(ShortDistanceSubscription $subscription, ShortDistanceJourney $journey): ShortDistanceSubscription
    {
        $this->_loggerService->log('Step 17 - The commitment journey is invalid. We remove it from subscription');
        $subscription->removeShortDistanceJourney($journey);

        $this->_em->flush();

        return $subscription;
    }

    private function carpoolItemAlreadyTreated(CarpoolItem $carpoolItem): bool
    {
        $longDistanceJourneys = $this->_longDistanceJourneyRepository->findBy(['carpoolItem' => $carpoolItem]);
        if (is_array($longDistanceJourneys) && count($longDistanceJourneys) > 0) {
            return true;
        }

        return false;
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

    private function _getCarpoolPaymentFromCarpoolItem(CarpoolItem $carpoolItem): ?CarpoolPayment
    {
        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function (CarpoolPayment $carpoolPayment) {
            return $carpoolPayment->isEecCompliant();
        }));

        return !(empty($carpoolPayments)) ? $carpoolPayments[0] : null;
    }

    private function _updateShortDistanceJourney(ShortDistanceJourney $journey, CarpoolProof $carpoolProof): ShortDistanceJourney
    {
        return $journey->updateJourney($carpoolProof, $this->getRPCOperatorId($carpoolProof->getId()), $this->getCarpoolersNumber($carpoolProof->getAsk()));
    }
}
