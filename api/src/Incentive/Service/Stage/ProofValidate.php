<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use Doctrine\ORM\EntityManagerInterface;

class ProofValidate extends ValidateSubscription
{
    /**
     * @var CarpoolProof
     */
    protected $_carpoolProof;

    /**
     * @var LongDistanceJourneyRepository
     */
    protected $_ldJourneyRepository;

    public function __construct(
        EntityManagerInterface $em,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EecInstance $eecInstance,
        CarpoolProof $carpoolProof,
        bool $pushOnlyMode = false
    ) {
        $this->_em = $em;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;
    }

    public function execute()
    {
        $distanceTraveled = !is_null($this->_carpoolProof->getAsk()) && !is_null($this->_carpoolProof->getAsk()->getMatching())
            ? $this->_carpoolProof->getAsk()->getMatching()->getCommonDistance() : null;

        switch (true) {
            case is_null($distanceTraveled):
                // Use case when the distance cannot be obtained

                return;

                // TODO: Put the constant into an instance variable
            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distanceTraveled && is_null($this->_carpoolProof->getSuccessfullPayment()):
                // Use case for long distance journey but payment has not yet been made

                return;

                // TODO: Put the constant into an instance variable
            case CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS <= $distanceTraveled && !is_null($this->_carpoolProof->getSuccessfullPayment()):
                // Use case for a long distance journey where payment has been made
                $carpoolItem = $this->_carpoolProof->getCarpoolItem();
                $carpoolPayment = $carpoolItem->getSuccessfullPayment();

                if (is_null($carpoolItem) || is_null($carpoolPayment)) {
                    return;
                }

                $stage = new ValidateLDSubscription($this->_em, $this->_ldJourneyRepository, $this->_eecInstance, $carpoolPayment, $this->_pushOnlyMode);
                $stage->execute();

                return;
        }

        // Use case for short distance journey
        $subscription = !is_null($this->_carpoolProof->getDriver()) && !is_null($this->_carpoolProof->getDriver()->getShortDistanceSubscription())
            ? $this->_carpoolProof->getDriver()->getShortDistanceSubscription()
            : null;

        if (is_null($subscription) || $subscription->hasExpired()) {
            return;
        }

        $stage = new ValidateSDSubscription($this->_em, $this->_timestampTokenManager, $this->_eecInstance, $subscription, $this->_carpoolProof, $this->_pushOnlyMode);
        $stage->execute();
    }
}
