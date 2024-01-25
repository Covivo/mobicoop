<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Service\Manager\TimestampTokenManager;

abstract class CommitSubscription extends UpdateSubscription
{
    /**
     * @var Proposal
     */
    protected $_proposal;

    /**
     * @var CarpoolProof
     */
    protected $_carpoolProof;

    protected function _commitSubscription()
    {
        $httpResponse = $this->_apiProvider->patchSubscription($this->_subscription, $this->_getCommitmentParams());

        $this->_subscription->addLog($httpResponse, Log::TYPE_COMMITMENT);

        if ($this->_apiProvider->hasRequestErrorReturned($httpResponse)) {
            return null;
        }

        $this->_subscription = $this->_timestampTokenManager->setSubscriptionTimestampToken($this->_subscription, TimestampTokenManager::TIMESTAMP_TOKEN_TYPE_COMMITMENT);

        $journey = (
            $this->_pushOnlyMode
            ? (
                $this->_subscription instanceof LongDistanceSubscription
                ? $this->_subscription->getCommitmentProofJourneyFromInitialProposal($this->_proposal)
                : $this->_subscription->getCommitmentProofJourneyFromCarpoolProof($this->_carpoolProof)
            )
            : (
                $this->_subscription instanceof LongDistanceSubscription
                ? new LongDistanceJourney($this->_proposal)
                : new ShortDistanceJourney($this->_carpoolProof)
            )
        );

        $this->_subscription->setCommitmentProofJourney($journey);
        $this->_subscription->setCommitmentProofDate(new \DateTime());

        $this->_em->flush();

        return $journey;
    }

    private function _getCommitmentParams(): array
    {
        return $this->_subscription instanceof LongDistanceSubscription
            ? [
                SpecificFields::JOURNEY_ID => LongDistanceSubscription::COMMITMENT_PREFIX.$this->_proposal->getId(),
                SpecificFields::JOURNEY_PUBLISH_DATE => $this->_proposal->getCreatedDate()->format('Y-m-d'),
            ]
            : [
                SpecificFields::JOURNEY_ID => $this->_eecInstance->getCarpoolProofPrefix().$this->_carpoolProof->getId(),
                SpecificFields::JOURNEY_START_DATE => $this->_carpoolProof->getPickUpDriverDate()->format('Y-m-d'),
            ];
    }
}
