<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolItemProvider;
use App\Incentive\Service\Provider\JourneyProvider;
use App\Incentive\Service\Provider\SubscriptionProvider;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateLDSubscription extends ValidateSubscription
{
    /**
     * @var CarpoolItem
     */
    private $_carpoolItem;

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
        bool $pushOnlyMode = false,
        bool $recoveryMode = false
    ) {
        $this->_em = $em;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolPayment = $carpoolPayment;
        $this->_pushOnlyMode = $pushOnlyMode;
        $this->_recoveryMode = $recoveryMode;

        $this->_build();
    }

    public function execute(): void
    {
        foreach (CarpoolItemProvider::getCarpoolItemFromCarpoolPayment($this->_carpoolPayment) as $this->_carpoolItem) {
            $this->_subscription = SubscriptionProvider::getLDSubscriptionFromCarpoolItem($this->_carpoolItem);

            if (is_null($this->_subscription)) {
                continue;
            }

            $carpoolProof = $this->_carpoolItem->getCarpoolProof();

            if (
                !$this->_recoveryMode
                && (
                    $this->_subscription->hasExpired()
                    || is_null($carpoolProof)
                )
            ) {
                return;
            }

            $journeyProvider = new JourneyProvider($this->_ldJourneyRepository);
            $journey = $journeyProvider->getJourneyFromCarpoolItem($this->_carpoolItem);

            // Use case where there is not yet a LD journey associated with the carpoolitem
            if ($this->_subscription->isCommitmentJourney($journey)) {
                $this->_executeForCommitmentJourney($journey, $carpoolProof);

                return;
            }

            if (CarpoolProofValidator::isEecCompliant($carpoolProof)) {
                $this->_executeForStandardJourney($journey);

                return;
            }
        }
    }

    protected function _executeForStandardJourney(?LongDistanceJourney $journey): void
    {
        if (
            is_null($journey)
            && !($this->_pushOnlyMode || $this->_subscription->isComplete())
        ) {
            $journey = new LongDistanceJourney();
            $journey->updateJourney(
                $this->_carpoolItem,
                $this->_carpoolPayment,
                $this->getCarpoolersNumber($this->_carpoolItem->getAsk()),
                $this->getAddressesLocality($this->_carpoolItem)
            );

            $this->_subscription->addLongDistanceJourney($journey);

            if ($this->_subscription->isComplete()) {
                $this->_subscription->setBonusStatus(Subscription::BONUS_STATUS_PENDING);
            }

            $this->_em->flush();
        }

        if (!is_null($journey) && SubscriptionValidator::canSubscriptionBeRecommited($this->_subscription)) {
            $stage = new RecommitSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $journey);
            $stage->execute();
        }
    }

    protected function _executeForCommitmentJourney(LongDistanceJourney $journey, CarpoolProof $carpoolProof): void
    {
        // Process for commitment journey
        switch (true) {
            case $carpoolProof->isStatusPending(): return;

            case CarpoolProofValidator::isStatusError($carpoolProof):
            case CarpoolProofValidator::isDowngradedType($carpoolProof):
                $stage = new ProofInvalidate($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $journey);
                $stage->execute();

                return;
        }

        $httpQueryParams = [
            SpecificFields::JOURNEY_COST_SHARING_DATE => $this->_carpoolPayment->getUpdatedDate()->format('Y-m-d'),
            SpecificFields::HONOR_CERTIFICATE => $this->_honorCertificateService->generateHonourCertificate($this->_subscription),
        ];

        try {
            $this->_apiProvider->patchSubscription($this->_subscription, $httpQueryParams);
        } catch (HttpException $exception) {
            $this->_subscription->addLog($exception, Log::TYPE_ATTESTATION, $httpQueryParams);

            $this->_em->flush();

            return;
        }

        $this->_subscription->getCommitmentProofJourney()->updateJourney(
            $this->_carpoolItem,
            $this->_carpoolPayment,
            $this->getCarpoolersNumber($this->_carpoolItem->getAsk()),
            $this->getAddressesLocality($this->_carpoolItem)
        );

        $this->_updateSubscription();

        $this->_em->flush();
    }
}
