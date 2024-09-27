<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Event\InvalidAuthenticationEvent;
use App\Incentive\Validator\APIAuthenticationValidator;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    protected function _commitSubscription(): bool
    {
        $httpQueryParams = $this->_getCommitmentParams();

        try {
            $this->_apiProvider->patchSubscription($this->_subscription, $httpQueryParams);
        } catch (HttpException $exception) {
            $this->_subscription->addLog($exception, Log::TYPE_COMMITMENT, $httpQueryParams);

            if (APIAuthenticationValidator::isApiAuthenticationError($exception)) {
                $event = new InvalidAuthenticationEvent($this->_subscription->getUser());
                $this->_eventDispatcher->dispatch(InvalidAuthenticationEvent::NAME, $event);
            }

            $this->_em->flush();

            return false;
        }

        $token = $this->_timestampTokenManager->getLatestToken($this->_subscription);
        $this->_subscription->setCommitmentProofTimestampToken($token->getTimestampToken());
        $this->_subscription->setCommitmentProofTimestampSigningTime($token->getSigningTime());

        $journey = (
            $this->_pushOnlyMode
            ? (
                $this->_subscription instanceof LongDistanceSubscription
                ? $this->_subscription->getCommitmentProofJourneyFromInitialProposal($this->_proposal)
                : $this->_subscription->getCommitmentProofJourneyFromCarpoolProof($this->_carpoolProof)
            )
            : (
                $this->_subscription instanceof LongDistanceSubscription
                ? new LongDistanceJourney()
                : new ShortDistanceJourney($this->_carpoolProof)
            )
        );

        if (is_null($journey)) {
            return false;
        }

        if ($this->_subscription instanceof LongDistanceSubscription && !is_null($this->_proposal)) {
            $journey->setInitialProposal($this->_proposal);
        }

        $this->_subscription->setCommitmentProofJourney($journey);
        $this->_subscription->setCommitmentProofDate(new \DateTime());

        $this->_em->flush();

        return true;
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
