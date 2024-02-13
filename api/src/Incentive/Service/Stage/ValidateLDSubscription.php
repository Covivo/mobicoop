<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\DateService;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolItemProvider;
use App\Incentive\Service\Provider\JourneyProvider;
use App\Incentive\Service\Provider\SubscriptionProvider;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;

class ValidateLDSubscription extends ValidateSubscription
{
    /**
     * @var LongDistanceSubscription
     */
    protected $_subscription;

    /**
     * @var LongDistanceJourneyRepository
     */
    private $_ldJourneyRepository;

    /**
     * @var CarpoolPayment
     */
    private $_carpoolPayment;

    public function __construct(
        EntityManagerInterface $em,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        CarpoolPayment $carpoolPayment,
        bool $pushOnlyMode = false
    ) {
        $this->_em = $em;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolPayment = $carpoolPayment;
        $this->_pushOnlyMode = $pushOnlyMode;

        $this->_build();
    }

    public function execute()
    {
        foreach (CarpoolItemProvider::getCarpoolItemFromCarpoolPayment($this->_carpoolPayment) as $carpoolItem) {
            $this->_subscription = SubscriptionProvider::getLDSubscriptionFromCarpoolItem($carpoolItem);

            $journeyProvider = new JourneyProvider($this->_ldJourneyRepository);
            $journey = $journeyProvider->getJourneyFromCarpoolItem($carpoolItem);

            if (
                is_null($this->_subscription)
                || $this->_subscription->hasExpired()
                || is_null($carpoolItem->getCarpoolProof())
                || (!$this->_pushOnlyMode && !is_null($journey))
            ) {
                return;
            }

            $carpoolProof = $carpoolItem->getCarpoolProof();

            // Use case where there is not yet a LD journey associated with the carpoolitem
            if ($this->_subscription->isCommitmentJourney($journey)) {
                $this->_executeForCommitmentJourney($journey, $carpoolItem, $carpoolProof);

                return;
            }

            if (
                !$this->_subscription->isComplete()
                && !$this->_pushOnlyMode
                && is_null($journey)
                && CarpoolProofValidator::isEecCompliant($carpoolProof)
            ) {
                $this->_executeForStandardJourney($carpoolItem);
            }
        }
    }

    protected function _executeForStandardJourney(CarpoolItem $carpoolItem)
    {
        if (SubscriptionValidator::canSubscriptionBeRecommited($this->_subscription)) {
            $stage = new AutoRecommitSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription);
            $stage->execute();

            return;
        }

        $journey = new LongDistanceJourney();
        $journey = $this->_updateJourney($journey, $carpoolItem);

        $this->_subscription->addLongDistanceJourney($journey);

        $this->_em->flush();
    }

    protected function _executeForCommitmentJourney(LongDistanceJourney $journey, CarpoolItem $carpoolItem, CarpoolProof $carpoolProof)
    {
        // Process for commitment journey
        switch (true) {
            case $carpoolProof->isStatusPending(): return;

            case CarpoolProofValidator::isStatusError($carpoolProof):
            case CarpoolProofValidator::isDowngradedType($carpoolProof):
                $stage = new ProofInvalidate($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $journey);
                $stage->execute();

                return;
        }

        $httpResponse = $this->_apiProvider->patchSubscription(
            $this->_subscription,
            [
                SpecificFields::JOURNEY_COST_SHARING_DATE => $this->_carpoolPayment->getUpdatedDate()->format('Y-m-d'),
                SpecificFields::HONOR_CERTIFICATE => $this->_honorCertificateService->generateHonourCertificate($this->_subscription),
            ]
        );

        if ($this->_apiProvider->hasRequestErrorReturned($httpResponse)) {
            $this->_subscription->addLog($httpResponse, Log::TYPE_ATTESTATION);

            return;
        }

        $this->_subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($this->_subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

        $this->_subscription->setExpirationDate(DateService::getExpirationDate($this->_subscription->getValidityPeriodDuration()));
        $this->_subscription->setCommitmentProofJourney($this->_updateJourney($this->_subscription->getCommitmentProofJourney(), $carpoolItem));

        $this->_em->flush();
    }

    private function _updateJourney(LongDistanceJourney $journey, CarpoolItem $carpoolItem): LongDistanceJourney
    {
        return $journey->updateJourney(
            $carpoolItem,
            $this->_carpoolPayment,
            $this->getCarpoolersNumber($carpoolItem->getAsk()),
            $this->getAddressesLocality($carpoolItem)
        );
    }
}
