<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Validation\APIAuthenticationValidation;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateSDSubscription extends ValidateSubscription
{
    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    public function __construct(
        EntityManagerInterface $em,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        EecInstance $eecInstance,
        CarpoolProof $carpoolProof,
        bool $pushOnlyMode = false,
        bool $recoveryMode = false
    ) {
        $this->_em = $em;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;
        $this->_recoveryMode = $recoveryMode;

        $this->_build();
    }

    public function execute()
    {
        if (
            !$this->_recoveryMode
            && (
                is_null($this->_subscription)
                || $this->_subscription->hasExpired()
                || !APIAuthenticationValidation::isAuthenticationValid($this->_subscription->getUser())
            )
        ) {
            return;
        }

        // There is not commitment journey
        if (is_null($this->_subscription->getCommitmentProofJourney())) {
            $stage = new CommitSDSubscription($this->_em, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $this->_subscription, $this->_carpoolProof);
            $stage->execute();
        }

        if (CarpoolProofValidator::isCarpoolProofSubscriptionCommitmentProof($this->_subscription, $this->_carpoolProof)) {
            $this->_executeForCommitmentJourney();

            return;
        }

        if (CarpoolProofValidator::isEecCompliant($this->_carpoolProof)) {
            $this->_executeForStandardJourney();

            return;
        }
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
        $journey = $this->_em->getRepository(ShortDistanceJourney::class)->findOneBy(['carpoolProof' => $this->_carpoolProof]);

        if (
            is_null($journey)
            && !($this->_pushOnlyMode || $this->_subscription->isComplete())
        ) {
            $journey = new ShortDistanceJourney($this->_carpoolProof);
            $journey->updateJourney(
                $this->_carpoolProof,
                $this->_eecInstance->getCarpoolProofPrefix().$this->_carpoolProof->getId(),
                $this->getCarpoolersNumber($this->_carpoolProof->getAsk())
            );

            $this->_subscription->addShortDistanceJourney($journey);

            if ($this->_subscription->isComplete()) {
                $this->_subscription->setBonusStatus(Subscription::BONUS_STATUS_PENDING);
            }

            $this->_em->flush();
        }

        if (!is_null($journey) && SubscriptionValidator::canSubscriptionBeRecommited($this->_subscription)) {
            $stage = new RecommitSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $this->_subscription, $journey);
            $stage->execute();
        }
    }

    protected function _executeForCommitmentJourney()
    {
        if (
            !CarpoolProofValidator::isEecCompliant($this->_carpoolProof)
            || !CarpoolProofValidator::isCarpoolProofOriginOrDestinationFromFrance($this->_carpoolProof)
        ) {
            $stage = new ProofInvalidate($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eecInstance, $this->_subscription->getCommitmentProofJourney());
            $stage->execute();

            return;
        }

        $httpQueryParams = [
            SpecificFields::HONOR_CERTIFICATE => $this->_honorCertificateService->generateHonourCertificate($this->_subscription),
        ];

        try {
            $this->_apiProvider->patchSubscription($this->_subscription, $httpQueryParams);
        } catch (HttpException $exception) {
            $this->_subscription->addLog($exception, Log::TYPE_ATTESTATION, $httpQueryParams);

            if (APIAuthenticationValidation::isApiAuthenticationError($exception)) {
                // Todo - Dispatch the event
            }

            $this->_em->flush();

            return;
        }

        $this->_subscription->getCommitmentProofJourney()->updateJourney(
            $this->_carpoolProof,
            $this->_eecInstance->getCarpoolProofPrefix().$this->_carpoolProof->getId(),
            $this->getCarpoolersNumber($this->_carpoolProof->getAsk())
        );

        $this->_updateSubscription();

        $this->_em->flush();
    }
}
