<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\DateService;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use Doctrine\ORM\EntityManagerInterface;

class ValidateSDSubscription extends ValidateSubscription
{
    /**
     * @var ?ShortDistanceSubscription
     */
    protected $_subscription;

    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    public function __construct(
        EntityManagerInterface $em,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        CarpoolProof $carpoolProof,
        bool $pushOnlyMode = false
    ) {
        $this->_em = $em;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;

        $this->_build();
    }

    public function execute()
    {
        if (is_null($this->_subscription) || $this->_subscription->hasExpired()) {
            return;
        }

        // There is not commitment journey
        if (is_null($this->_subscription->getCommitmentProofJourney())) {
            $stage = new CommitSDSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription, $this->_carpoolProof);
            $stage->execute();
        }

        if (CarpoolProofValidator::isCarpoolProofSubscriptionCommitmentProof($this->_subscription, $this->_carpoolProof)) {
            $this->_executeForCommitmentJourney();
        } else {
            if (
                $this->_subscription->isComplete()
                || $this->_pushOnlyMode
                || !CarpoolProofValidator::isEecCompliant($this->_carpoolProof)
                || !CarpoolProofValidator::isCarpoolProofOriginOrDestinationFromFrance($this->_carpoolProof)
            ) {
                return;
            }

            $this->_executeForStandardJourney();
        }

        if ($this->_subscription->isComplete()) {
            $this->_subscription->setBonusStatus(Subscription::BONUS_STATUS_PENDING);
        }

        $this->_em->flush();
    }

    protected function _build()
    {
        $this->_setApiProvider();

        $this->_honorCertificateService = new HonourCertificateService();

        $this->_subscription = !is_null($this->_carpoolProof->getDriver()) && !is_null($this->_carpoolProof->getDriver()->getShortDistanceSubscription())
            ? $this->_carpoolProof->getDriver()->getShortDistanceSubscription()
            : null;
    }

    protected function _executeForStandardJourney()
    {
        if (SubscriptionValidator::canSubscriptionBeRecommited($this->_subscription)) {
            $stage = new AutoRecommitSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription);
            $stage->execute();

            return;
        }

        $journey = new ShortDistanceJourney($this->_carpoolProof);
        $journey->updateJourney(
            $this->_carpoolProof,
            $this->_eecInstance->getCarpoolProofPrefix().$this->_carpoolProof->getId(),
            $this->getCarpoolersNumber($this->_carpoolProof->getAsk())
        );
        $this->_subscription->addShortDistanceJourney($journey);

        $this->_em->flush();
    }

    protected function _executeForCommitmentJourney()
    {
        if (
            !CarpoolProofValidator::isEecCompliant($this->_carpoolProof)
            || !CarpoolProofValidator::isCarpoolProofOriginOrDestinationFromFrance($this->_carpoolProof)
        ) {
            $stage = new ProofInvalidate($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription->getCommitmentProofJourney());
            $stage->execute();

            return;
        }

        $httpResponse = $this->_apiProvider->patchSubscription(
            $this->_subscription,
            [
                SpecificFields::HONOR_CERTIFICATE => $this->_honorCertificateService->generateHonourCertificate($this->_subscription),
            ]
        );

        if ($this->_apiProvider->hasRequestErrorReturned($httpResponse)) {
            $this->_subscription->addLog($httpResponse, Log::TYPE_ATTESTATION);

            return;
        }

        $this->_subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($this->_subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE);

        $this->_subscription->setExpirationDate(DateService::getExpirationDate($this->_subscription->getValidityPeriodDuration()));

        $this->_subscription->setCommitmentProofJourney($this->_updateJourney($this->_subscription->getCommitmentProofJourney()));
    }

    private function _updateJourney(ShortDistanceJourney $journey): ShortDistanceJourney
    {
        return $journey->updateJourney(
            $this->_carpoolProof,
            $this->_eecInstance->getCarpoolProofPrefix().$this->_carpoolProof->getId(),
            $this->getCarpoolersNumber($this->_carpoolProof->getAsk())
        );
    }
}
