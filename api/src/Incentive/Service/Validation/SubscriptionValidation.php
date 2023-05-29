<?php

namespace App\Incentive\Service\Validation;

use App\Incentive\Entity\Flat\LongDistanceSubscription;
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
            SubscriptionManager::STATUS_VALIDATED === $subscription->getStatus()
            && (
                is_null($subscription->getIncentiveProofTimestampToken())
                || is_null($subscription->getCommitmentProofTimestampToken())
                || is_null($subscription->getHonorCertificateProofTimestampToken())
            );
    }

    /**
     * Undocumented function.
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isSubscriptionReadyForVerify($subscription): bool
    {
        return
            is_null($subscription->getStatus())
            && (
                $subscription instanceof LongDistanceSubscription
                ? $this->_journeyValidation->isLDJourneysReadyForVerify($subscription->getJourneys()) // Todo, le paiement a bien été effectué
                : $this->_journeyValidation->isSDJourneysReadyForVerify($subscription->getJourneys()) // Todo, la preuve est bien de classe C
            )
            && !is_null($subscription->getCommitmentProofDate())
            && $subscription->getCommitmentProofDate() <= $this->_verificationDeadline
            && is_null($subscription->getVerificationDate());
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
