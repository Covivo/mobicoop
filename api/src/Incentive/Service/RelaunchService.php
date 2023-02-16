<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RelaunchService
{
    /**
     * @var CarpoolProofRepository
     */
    private $_carpoolProofRepository;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var MobConnectSubscriptionManager
     */
    private $_subscriptionManager;

    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    /**
     * @var null|bool
     */
    private $_processForLongDistance;

    /**
     * @var LongDistanceSubscription
     */
    private $_longDistanceSubscription;

    /**
     * @var ShortDistanceSubscription
     */
    private $_shortDistanceSubscription;

    /**
     * @var User
     */
    private $_user;

    public function __construct(EntityManagerInterface $em, CarpoolProofRepository $carpoolProofRepository, MobConnectSubscriptionManager $mobConnectSubscriptionManager)
    {
        $this->_em = $em;
        $this->_carpoolProofRepository = $carpoolProofRepository;
        $this->_subscriptionManager = $mobConnectSubscriptionManager;
    }

    /**
     * Relaunch the declaration process for user carpool proofs.
     */
    public function relaunchUserProofs(?CarpoolProof $carpoolProof): void
    {
        $this->_carpoolProof = $carpoolProof;

        $this->_user = $this->_carpoolProof->getDriver();

        if (
            is_null($this->_user)
            || !CeeJourneyService::isUserProperlyConnectToMob($this->_user)
        ) {
            return;
        }

        $this->_relaunchJourneysForLongDistanceSubscription();

        $this->_relaunchJourneysForShortDistanceSubscription();
    }

    /**
     * Handles the declaration process for long distance journeys.
     */
    private function _relaunchJourneysForLongDistanceSubscription()
    {
        $this->_longDistanceSubscription = $this->_user->getLongDistanceSubscription();

        if (is_null($this->_longDistanceSubscription)) {
            return;
        }

        $this->_setProcessForLongDistance(true);

        $this->_relaunchJourneys();
    }

    /**
     * Handles the declaration process for short distance journeys.
     */
    private function _relaunchJourneysForShortDistanceSubscription()
    {
        $this->_shortDistanceSubscription = $this->_user->getShortDistanceSubscription();

        if (is_null($this->_shortDistanceSubscription)) {
            return;
        }

        $this->_setProcessForLongDistance(false);

        $this->_relaunchJourneys();
    }

    /**
     * Returns a list of journeys that have not been declared.
     */
    private function _getCarpoolProofs(): array
    {
        $allreadyDeclaredJourneysIds = $this->_isProcessForLongDistance()
            ? array_map(function ($journey) { return $journey->getId(); }, $this->_longDistanceSubscription->getLongDistanceJourneys()->toArray())
            : array_map(function ($journey) { return $journey->getId(); }, $this->_shortDistanceSubscription->getShortDistanceJourneys()->toArray());

        return $this->_carpoolProofRepository->findCarpoolProofForEccRelaunch(
            $this->_user,
            $this->_carpoolProof->getId(),
            $allreadyDeclaredJourneysIds,
            $this->_isProcessForLongDistance()
        );
    }

    /**
     * Declare the journeys that have not been.
     */
    private function _relaunchJourneys(): void
    {
        $undeclaredCarpoolProofs = $this->_getCarpoolProofs();

        foreach ($undeclaredCarpoolProofs as $carpoolProof) {
            $carpoolPayment = $this->_isProcessForLongDistance()
                ? CeeJourneyService::getCarpoolPaymentFromCarpoolProof($this->_carpoolProof)
                : null
            ;

            $this->_subscriptionManager->updateSubscription($carpoolProof, $carpoolPayment);
        }

        $this->_setProcessForLongDistance(null);
    }

    /**
     * Get the value of _isProcessForLongDistance.
     */
    private function _isProcessForLongDistance(): ?bool
    {
        return $this->_processForLongDistance;
    }

    /**
     * Set the value of _isProcessForLongDistance.
     *
     * @param null|bool $_isProcessForLongDistance
     */
    private function _setProcessForLongDistance(?bool $processForLongDistance): self
    {
        $this->_processForLongDistance = $processForLongDistance;

        return $this;
    }
}
