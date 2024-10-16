<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\TimestampTokenManager;
use App\Incentive\Service\Provider\CarpoolPaymentProvider;
use App\Payment\Repository\CarpoolPaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProofValidate extends ValidateSubscription
{
    /**
     * @var CarpoolProof
     */
    protected $_carpoolProof;

    public function __construct(
        EntityManagerInterface $em,
        CarpoolPaymentRepository $carpoolPaymentRepository,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        TimestampTokenManager $timestampTokenManager,
        EventDispatcherInterface $eventDispatcher,
        EecInstance $eecInstance,
        CarpoolProof $carpoolProof,
        bool $pushOnlyMode = false,
        bool $recoveryMode = false
    ) {
        $this->_em = $em;
        $this->_carpoolPaymentRepository = $carpoolPaymentRepository;
        $this->_ldJourneyRepository = $longDistanceJourneyRepository;
        $this->_timestampTokenManager = $timestampTokenManager;
        $this->_eventDispatcher = $eventDispatcher;

        $this->_eecInstance = $eecInstance;
        $this->_carpoolProof = $carpoolProof;
        $this->_pushOnlyMode = $pushOnlyMode;
        $this->_recoveryMode = $recoveryMode;
    }

    public function execute()
    {
        $distanceTraveled = !is_null($this->_carpoolProof->getAsk()) && !is_null($this->_carpoolProof->getAsk()->getMatching())
            ? $this->_carpoolProof->getAsk()->getMatching()->getCommonDistance() : null;

        switch (true) {
            // Use case when the distance cannot be obtained
            case is_null($distanceTraveled):
                // Use case for long distance journey but payment has not yet been made
            case $this->_eecInstance->getLdMinimalDistance() <= $distanceTraveled && is_null($this->_carpoolProof->getSuccessfullPayment()):
                return;

                // Use case for a long distance journey where payment has been made
            case $this->_eecInstance->getLdMinimalDistance() <= $distanceTraveled && !is_null($this->_carpoolProof->getSuccessfullPayment()):
                $carpoolItem = $this->_carpoolProof->getCarpoolItem();

                $carpoolPayment = !is_null($carpoolItem)
                    ? CarpoolPaymentProvider::getCarpoolPaymentFromCarpoolItem($this->_carpoolPaymentRepository, $carpoolItem) : null;

                if (is_null($carpoolItem) || is_null($carpoolPayment)) {
                    return;
                }

                $stage = new ValidateLDSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $carpoolPayment, $this->_pushOnlyMode, $this->_recoveryMode);
                $stage->execute();

                return;
        }

        $stage = new ValidateSDSubscription($this->_em, $this->_ldJourneyRepository, $this->_timestampTokenManager, $this->_eventDispatcher, $this->_eecInstance, $this->_carpoolProof, $this->_pushOnlyMode, $this->_recoveryMode);
        $stage->execute();
    }
}
