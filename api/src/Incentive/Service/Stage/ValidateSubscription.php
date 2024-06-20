<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\DateService;

abstract class ValidateSubscription extends UpdateSubscription
{
    /**
     * @var ?LongDistanceSubscription|?ShortDistanceSubscription
     */
    protected $_subscription;

    /**
     * @var bool
     */
    protected $_recoveryMode;

    protected function _setSubscriptionToken(): void
    {
        if (!is_null($this->_subscription)) {
            $token = $this->_timestampTokenManager->getLatestToken($this->_subscription);

            $this->_subscription->setHonorCertificateProofTimestampToken($token->getTimestampToken());
            $this->_subscription->setHonorCertificateProofTimestampSigningTime($token->getSigningTime());
        }
    }

    protected function _updateSubscription(): void
    {
        $this->_setSubscriptionToken();

        if (!is_null($this->_subscription)) {
            $this->_subscription->setExpirationDate(DateService::getExpirationDate($this->_subscription->getValidityPeriodDuration()));
        }
    }
}
