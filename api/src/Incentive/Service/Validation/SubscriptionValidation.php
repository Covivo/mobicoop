<?php

namespace App\Incentive\Service\Validation;

use App\Incentive\Entity\Flat\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Manager\SubscriptionManager;

class SubscriptionValidation extends Validation
{
    public function __construct(LoggerService $loggerService)
    {
        parent::__construct($loggerService);
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
            )
        ;
    }
}
