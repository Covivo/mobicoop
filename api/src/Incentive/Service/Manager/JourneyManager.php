<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Incentive\Resource\CeeSubscriptions;
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
     * @var ShortDistanceJourneyRepository
     */
    private $_shortDistanceJourneyRepository;

    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    private $_pushOnlyMode = false;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        CarpoolItemRepository $carpoolItemRepository,
        EntityManagerInterface $em,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        TimestampTokenManager $timestampTokenManager,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        ShortDistanceJourneyRepository $shortDistanceJourneyRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_carpoolItemRepository = $carpoolItemRepository;
        $this->_longDistanceJourneyRepository = $longDistanceJourneyRepository;
        $this->_shortDistanceJourneyRepository = $shortDistanceJourneyRepository;

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
    public function declareFirstLongDistanceJourney(Proposal $proposal, bool $pushOnly = false): ?LongDistanceJourney
    {
        $this->setDriver($proposal->getUser());

        $params = [
            SpecificFields::JOURNEY_ID => LongDistanceSubscription::COMMITMENT_PREFIX.$proposal->getId(),
            SpecificFields::JOURNEY_PUBLISH_DATE => $proposal->getCreatedDate()->format(self::DATE_FORMAT),
        ];

        $subscription = $this->getDriver()->getLongDistanceSubscription();

        if (is_null($subscription)) {
            return null;
        }

        $patchResponse = $this->patchSubscription($subscription->getSubscriptionId(), $params);

        if ($this->hasRequestErrorReturned($patchResponse)) {
            return null;
        }

        $subscription->addLog($patchResponse, Log::TYPE_COMMITMENT);

        $log = 204 === $patchResponse->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the proposal '.$proposal->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$proposal->getId();

        $this->_loggerService->log($log);

        $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_COMMITMENT);

        if ($pushOnly) {
            $this->_em->flush();

            return null;
        }

        $journey = new LongDistanceJourney($proposal);

        $subscription->setCommitmentProofJourney($journey);
        $subscription->setCommitmentProofDate(new \DateTime());

        $this->_em->flush();

        $subscription->setVersion();

        $this->_em->flush();

        return $journey;
    }

    /**
     * Step 9 - Short distance journey.
     */
    public function declareFirstShortDistanceJourney(CarpoolProof $carpoolProof, bool $pushOnly = false): ?ShortDistanceJourney
    {
        $this->setDriver($carpoolProof->getDriver());

        $params = [
            SpecificFields::JOURNEY_ID => $this->getRPCOperatorId($carpoolProof->getId()),
            SpecificFields::JOURNEY_START_DATE => $carpoolProof->getPickUpDriverDate()->format(self::DATE_FORMAT),
        ];

        $subscription = $this->getDriver()->getShortDistanceSubscription();

        if (is_null($subscription)) {
            return null;
        }

        $patchResponse = $this->patchSubscription($subscription->getSubscriptionId(), $params);

        if ($this->hasRequestErrorReturned($patchResponse)) {
            return null;
        }

        $subscription->addLog($patchResponse, Log::TYPE_COMMITMENT);

        $log = 204 === $patchResponse->getCode()
            ? 'The subscription '.$subscription->getId().' has been patch successfully with the carpoolProof '.$carpoolProof->getId()
            : 'The subscription '.$subscription->getId().' was not patch with the carpoolProof '.$carpoolProof->getId();

        $this->_loggerService->log($log);

        $subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_COMMITMENT);

        if ($pushOnly) {
            $this->_em->flush();

            return null;
        }

        $journey = new ShortDistanceJourney($carpoolProof);

        $subscription->setCommitmentProofJourney($journey);
        $subscription->setCommitmentProofDate(new \DateTime());

        $this->_em->flush();

        $subscription->setVersion();

        $this->_em->flush();

        return $journey;
    }

    /**
     * Step 17 - Electronic payment is validated for a long distance journey. All carpooling compliant with the CEE standard will be processed.
     */
    public function receivingElectronicPayment(CarpoolPayment $carpoolPayment, bool $pushOnly = false)
    {
        $this->_currentCarpoolPayment = $carpoolPayment;
        $this->_pushOnlyMode = $pushOnly;

        $this->_loggerService->log('Step 17 - Processing the carpoolPayment ID '.$this->_currentCarpoolPayment->getId());

        /**
         * @var CarpoolItem[]
         */
        $carpoolItems = $this->_getEECCarpoolItemsFromCarpoolPayment($this->_currentCarpoolPayment);

        foreach ($carpoolItems as $carpoolItem) {
            $this->_currentCarpoolItem = $carpoolItem;

            $this->_subscriptionConfirmationForLDJourney();
        }

        $this->_loggerService->log('Step 17 - End of treatment');
    }

    /**
     * Step 17 - Validation of proof for a short distance journey,.
     */
    public function validationOfProof(CarpoolProof $carpoolProof, bool $pushOnly = false)
    {
        $this->_pushOnlyMode = $pushOnly;

        $this->setDriver($carpoolProof->getDriver());

        $distanceTraveled = $this->getDistanceTraveled($carpoolProof);

        switch (true) {
            case is_null($distanceTraveled):
                // Use case when the distance cannot be obtained
                $this->_loggerService->log('Step 17 - The distance traveled cannot be determined for proof '.$carpoolProof->getId().'.');

                return;

            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distanceTraveled && !$this->isJourneyPaid($carpoolProof):
                // Use case for long distance journey but payment has not yet been made
                $this->_loggerService->log('Step 17 - The distance traveled for the proof '.$carpoolProof->getId().' has been determined to be long distance but payment has not yet been made.');

                return;

            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distanceTraveled && $this->isJourneyPaid($carpoolProof):
                // Use case for a long distance journey where payment has been made

                if (is_null($this->_currentCarpoolItem)) {
                    $this->_currentCarpoolItem = $carpoolProof->getCarpoolItem();
                    if (is_null($this->_currentCarpoolItem)) {
                        return;
                    }
                }

                if (is_null($this->_currentCarpoolPayment)) {
                    $this->_currentCarpoolPayment = $this->_currentCarpoolItem->getSuccessfullPayment();
                    if (is_null($this->_currentCarpoolPayment)) {
                        return;
                    }
                }

                $this->_subscriptionConfirmationForLDJourney();

                return;
        }

        // Use case for short distance journey
        $this->_loggerService->log('Step 17 - We start the processing process for a short distance trip.');

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
            if (!$commitmentJourney->isEECCompliant()) {
                $this->_resetSubscription($subscription, $commitmentJourney);

                return;
            }

            $params = [
                SpecificFields::HONOR_CERTIFICATE => $this->getHonorCertificate(false),
            ];

            $this->_loggerService->log('Step 17 - Journey update and sending honor attestation');

            $patchResponse = $this->patchSubscription($this->getDriverShortSubscriptionId(), $params);

            if ($this->hasRequestErrorReturned($patchResponse)) {
                return;
            }

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
                $this->_pushOnlyMode
                || self::SHORT_DISTANCE_TRIP_THRESHOLD <= $shortDistanceJourneysNumber
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

        $subscription->setVersion();

        $this->_em->flush();
    }

    /**
     * Step 17 - Unvalidation of proof.
     * Resets a short distance subscription when the commitment journey has not been validated by the RPC.
     */
    public function invalidationOfProof(CarpoolProof $carpoolProof): void
    {
        // Rechercher avant traitement si la preuve est associée à une souscription CEE
        $journey = $this->_getEECJourneyFromCarpoolProof($carpoolProof);

        if (is_null($journey)) {
            // Use case, the CarpoolProof does not correspond to any EEC journey.
            return;
        }

        $this->setDriver($carpoolProof->getDriver());

        $distanceTraveled = $this->getDistanceTraveled($carpoolProof);

        switch (true) {
            case is_null($distanceTraveled):
                // Use case when the distance cannot be obtained
                $this->_loggerService->log('Step 17 - The distance traveled cannot be determined for proof '.$carpoolProof->getId().'.');

                return;

            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distanceTraveled && $journey instanceof LongDistanceJourney:
                // Use case for long distance journey
                $this->_currentSubscription = $this->getDriver()->getLongDistanceSubscription();

                break;

            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS > $distanceTraveled && $journey instanceof ShortDistanceJourney:
                // Use case for short distance journey
                $this->_currentSubscription = $this->getDriver()->getShortDistanceSubscription();

                break;
        }

        $this->_invalidateJourney($journey);
    }

    /**
     * Returns the CEE journey if the proof of carpooling matches it.
     *
     * @return null|LongDistanceJourney|ShortDistanceJourney
     */
    private function _getEECJourneyFromCarpoolProof(CarpoolProof $carpoolProof)
    {
        $journey = $this->_longDistanceJourneyRepository->findOneByCarpoolItemOrProposal(
            $carpoolProof->getCarpoolItem(),
            $this->getDriverPassengerProposalForCarpoolItem($carpoolProof->getCarpoolItem(), self::DRIVER)
        );

        if (is_null($journey)) {
            $journey = $this->_shortDistanceJourneyRepository->findOneByCarpoolProof($carpoolProof);
        }

        return $journey;
    }

    /**
     * Step 17 - Process for long distance journey. Processing of a single carpool if it complies with the CEE standard.
     */
    private function _subscriptionConfirmationForLDJourney(): void
    {
        $this->_loggerService->log('Step 17 - We start the processing process for a long distance trip.');

        $this->setDriver($this->_currentCarpoolItem->getCreditorUser());

        $this->_currentSubscription = $this->_driver->getLongDistanceSubscription();

        if (
            is_null($this->_currentSubscription)
            || $this->_currentSubscription->hasExpired()
            || is_null($this->_currentCarpoolItem->getCarpoolProof())
            || $this->carpoolItemAlreadyTreated()
        ) {
            return;
        }

        $this->_currentCarpoolProof = $this->_currentCarpoolItem->getCarpoolProof();

        $journey = $this->_longDistanceJourneyRepository->findOneByCarpoolItemOrProposal(
            $this->_currentCarpoolItem,
            $this->getDriverPassengerProposalForCarpoolItem($this->_currentCarpoolItem, self::DRIVER)
        );

        // Use case where there is not yet a LD journey associated with the carpoolitem
        if (is_null($journey) && !$this->_pushOnlyMode) {
            if ($this->_currentCarpoolProof->isEECCompliant()) {
                // Processes for journeys that are not the commitment journey
                $this->_addLDJourneyToSubscription();
            }

            return;
        }

        // Process for commitment journey
        switch (true) {
            case $this->_currentCarpoolProof->isStatusPending(): return;

            case $this->_currentCarpoolProof->isStatusError():
            case $this->_currentCarpoolProof->isCarpoolProofDowngraded():
                $this->_invalidateJourney($journey);

                return;
        }

        $this->_loggerService->log('Step 17 - Processing the carpoolItem ID'.$this->_currentCarpoolItem->getId().'with normal process');

        if ($this->_currentSubscription->isCommitmentJourney($journey)) {
            $this->_updateSubscriptionForCommitmentJourney();
        }
    }

    private function carpoolItemAlreadyTreated(): bool
    {
        $longDistanceJourneys = $this->_longDistanceJourneyRepository->findBy(['carpoolItem' => $this->_currentCarpoolItem]);
        if (is_array($longDistanceJourneys) && count($longDistanceJourneys) > 0) {
            return true;
        }

        return false;
    }

    private function _getEECCarpoolItemsFromCarpoolPayment(CarpoolPayment $carpoolPayment): array
    {
        return array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
                && $carpoolItem->isEECompliant();
        });
    }

    private function _getCarpoolPaymentFromCarpoolItem(CarpoolItem $carpoolItem): ?CarpoolPayment
    {
        $distance = $carpoolItem->getRelativeDistance();

        if (is_null($distance)) {
            return null;
        }

        if (is_null($this->getDistanceType($distance))) {
            return null;
        }

        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function (CarpoolPayment $carpoolPayment) {
            return $carpoolPayment->isEecCompliant();
        }));

        return !(empty($carpoolPayments)) ? $carpoolPayments[0] : null;
    }

    private function _updateShortDistanceJourney(ShortDistanceJourney $journey, CarpoolProof $carpoolProof): ShortDistanceJourney
    {
        return $journey->updateJourney($carpoolProof, $this->getRPCOperatorId($carpoolProof->getId()), $this->getCarpoolersNumber($carpoolProof->getAsk()));
    }

    private function _addLDJourneyToSubscription()
    {
        if (self::LONG_DISTANCE_TRIP_THRESHOLD <= $this->_currentSubscription->getJourneysNumber()) {
            return;
        }

        $journey = new LongDistanceJourney();

        $this->_currentSubscription->addLongDistanceJourney($journey);

        $journey->updateJourney(
            $this->_currentCarpoolItem,
            $this->_currentCarpoolPayment,
            $this->getCarpoolersNumber($this->_currentCarpoolItem->getAsk()),
            $this->getAddressesLocality($this->_currentCarpoolItem)
        );

        ${$this}->_currentSubscription->setVersion();

        $this->_em->flush();
    }

    private function _updateSubscriptionForCommitmentJourney()
    {
        // L'attestation sur l'honneur doit être transmise à mob
        $this->_loggerService->log('Step 17 - Processing for the commitment journey');

        $patchResponse = $this->patchSubscription(
            $this->getDriverLongSubscriptionId(),
            [
                SpecificFields::JOURNEY_COST_SHARING_DATE => $this->_currentCarpoolPayment->getUpdatedDate()->format(self::DATE_FORMAT),
                SpecificFields::HONOR_CERTIFICATE => $this->getHonorCertificate(),
            ]
        );

        if ($this->hasRequestErrorReturned($patchResponse)) {
            return;
        }

        $this->_currentSubscription->addLog($patchResponse, Log::TYPE_ATTESTATION);
        $this->_currentSubscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($this->_currentSubscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

        $this->_currentSubscription->setExpirationDate($this->getExpirationDate());

        $this->_currentSubscription->getCommitmentProofJourney()->updateJourney(
            $this->_currentCarpoolItem,
            $this->_currentCarpoolPayment,
            $this->getCarpoolersNumber($this->_currentCarpoolItem->getAsk()),
            $this->getAddressesLocality($this->_currentCarpoolItem)
        );

        $this->_em->flush();
    }

    /**
     * @param LongDistanceJourney|ShortDistanceJourney $journey
     */
    private function _resetSubscription($journey = null)
    {
        $this->_loggerService->log('Step 17 - The commitment journey is invalid. We remove it from subscription');

        $this->_removeMobJourneyReference();

        $this->_currentSubscription = $this->_currentSubscription->reset();

        // If there are other journeys associated with the subscription, then we declare the 1st one as the commitment journey
        if (!empty($this->_currentSubscription->getJourneys())) {
            $journey = $this->_currentSubscription->getJourneys()[0];

            if ($journey instanceof LongDistanceJourney && !is_null($journey->getInitialProposal())) {
                $this->declareFirstLongDistanceJourney($journey->getInitialProposal());
            }

            if ($journey instanceof ShortDistanceJourney && !is_null($journey->getCarpoolProof())) {
                $this->declareFirstShortDistanceJourney($journey->getCarpoolProof());
            }
        }

        $this->_em->flush();
    }

    /**
     * @param LongDistanceJourney|ShortDistanceJourney $journey
     */
    private function _invalidateJourney($journey)
    {
        if ($this->_currentSubscription->isCommitmentJourney($journey)) {
            return $this->_resetSubscription($this->_currentSubscription, $journey);
        }

        $this->_currentSubscription->removeJourney($journey);

        $this->_em->flush();
    }

    private function _removeMobJourneyReference()
    {
        if ($this->hasSubscriptionCommited($this->_currentSubscription->getSubscriptionId())) {
            // TODO: Remove subscription commitment (journey and token) on moB API
        }
    }
}
