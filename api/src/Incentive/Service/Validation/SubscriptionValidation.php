<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Manager\MobConnectManager;
use App\Incentive\Service\Manager\SubscriptionManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SubscriptionValidation extends Validation
{
    /**
     * @var JourneyValidation
     */
    private $_journeyValidation;

    /**
     * @var int
     */
    private $_verificationDeadline;

    public function __construct(LoggerService $loggerService, JourneyValidation $journeyValidation, int $deadline)
    {
        parent::__construct($loggerService);

        $this->_setVerificationDeadline($deadline);
        $this->_journeyValidation = $journeyValidation;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isSubscriptionValidForTimestampsProcess($subscription): bool
    {
        return
            SubscriptionManager::STATUS_VALIDATED !== $subscription->getStatus()
            && (
                is_null($subscription->getIncentiveProofTimestampToken())
                || is_null($subscription->getCommitmentProofTimestampToken())
                || is_null($subscription->getHonorCertificateProofTimestampToken())
            );
    }

    /**
     * Returns if the subscription is ready to be verified. Ready if:
     * - The subscription has not been validated
     * - All tokens are present
     * - Depending on the type of subscription, the conditions specific to each are met.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isSubscriptionReadyForVerify($subscription): bool
    {
        $journeys = $subscription->getJourneys();

        if ($journeys->isEmpty()) {
            return false;
        }

        $commitmentJourney = $subscription->getCommitmentProofJourney();

        if (is_null($commitmentJourney)) {
            return false;
        }

        return
            SubscriptionManager::STATUS_VALIDATED != $subscription->getStatus()                                 // We do not recheck subscriptions that have already been validated
            && !is_null($subscription->getIncentiveProofTimestampToken())
            && !is_null($subscription->getIncentiveProofTimestampSigningTime())
            && !is_null($subscription->getCommitmentProofTimestampToken())
            && !is_null($subscription->getCommitmentProofTimestampSigningTime())
            && !is_null($subscription->getHonorCertificateProofTimestampToken())
            && !is_null($subscription->getHonorCertificateProofTimestampSigningTime())
            && $subscription instanceof LongDistanceSubscription
                ? $this->_journeyValidation->isPaymentValidForEec($commitmentJourney->getCarpoolPayment())
                : (
                    CarpoolProof::TYPE_HIGH === $commitmentJourney->getCarpoolProof()->getType()
                    && CarpoolProof::STATUS_VALIDATED === $commitmentJourney->getCarpoolProof()->getStatus()
                );
    }

    public function checkSubscriptionIdValidity(string $id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new BadRequestHttpException('The subscriptionId parameter should be an integer');
        }
    }

    public function checkSubscriptionTypeValidity(string $type)
    {
        if (!in_array($type, MobConnectManager::ALLOWED_SUBSCRIPTION_TYPES)) {
            throw new BadRequestHttpException('The subscriptionType parameter is incorrect. Please choose from: '.join(', ', MobConnectManager::ALLOWED_SUBSCRIPTION_TYPES));
        }
    }

    private function _setVerificationDeadline(int $deadline): self
    {
        $this->_verificationDeadline = new \DateTime('now');
        $this->_verificationDeadline->sub(new \DateInterval('P'.$deadline.'D'));

        return $this;
    }
}
