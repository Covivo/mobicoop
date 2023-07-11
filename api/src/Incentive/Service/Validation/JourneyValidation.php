<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Service\LoggerService;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;

class JourneyValidation extends Validation
{
    public const PROOF_ERROR_STATUS = [
        CarpoolProof::STATUS_ERROR,
        CarpoolProof::STATUS_CANCELED,
        CarpoolProof::STATUS_ACQUISITION_ERROR,
        CarpoolProof::STATUS_NORMALIZATION_ERROR,
        CarpoolProof::STATUS_FRAUD_ERROR,
        CarpoolProof::STATUS_EXPIRED,
        CarpoolProof::STATUS_CANCELED_BY_OPERATOR,
    ];

    /**
     * @var UserValidation
     */
    private $_userValidation;

    public function __construct(LoggerService $loggerService, UserValidation $userValidation)
    {
        parent::__construct($loggerService);

        $this->_userValidation = $userValidation;
    }

    /**
     * Checks if the journey is a valid first long distance journey. true if:
     *      - there is a driver and he has subscribed to a long distance subscription
     *      - the subscription has not been initialized
     *      - there is no recorded journey.
     */
    public function isFirstValidLongECCJourney(): bool
    {
        return
            $this->_userValidation->hasValidMobConnectAuth($this->_driver)
            && !is_null($this->_driver)
            && !is_null($this->_driver->getLongDistanceSubscription())
            && is_null($this->_driver->getLongDistanceSubscription()->getCommitmentProofDate())
            && empty($this->_driver->getLongDistanceSubscription()->getJourneys()->toArray());
    }

    /**
     * Checks if the journey is a valid first short distance journey. true if:
     *      - there is a driver and he has subscribed to a short distance subscription
     *      - the subscription has not been initialized
     *      - there is no recorded journey.
     */
    public function isFirstValidShortECCJourney(bool $recovery = false): bool
    {
        return
            $this->_userValidation->hasValidMobConnectAuth($this->_driver)
            && !is_null($this->_driver)
            && !is_null($this->_driver->getShortDistanceSubscription())
            && is_null($this->_driver->getShortDistanceSubscription()->getCommitmentProofDate())
            && true === $recovery
                ? empty($this->_driver->getShortDistanceSubscription()->getJourneys()->toArray()) : true;
    }

    /**
     * Checks if the payment is valid :
     *      - The payment status is 1
     *      - The payment transaction id has been set.
     */
    public function isPaymentValidForEEC(CarpoolPayment $carpoolPayment): bool
    {
        return
            CarpoolPayment::STATUS_SUCCESS === $carpoolPayment->getStatus()
            && !is_null($carpoolPayment->getTransactionId());
    }

    /**
     * Checks if the published journey (a Proposal) corresponding with the long distance EEC standard. true if:
     *      - the journey is a driver journey
     *      - the journey is a long distance journey
     *      - the origin or destination is from the reference country
     *      - the journey is valid and the first.
     */
    public function isPublishedJourneyValidLongECCJourney(Proposal $proposal): bool
    {
        $this->setDriver($proposal->getUser());

        return
            $this->_userValidation->hasValidMobConnectAuth($this->_driver)
            && !is_null($proposal->getCriteria())
            && $proposal->getCriteria()->isDriver()
            && !is_null($proposal->getCriteria()->getDirectionDriver())
            && !is_null($proposal->getCriteria()->getDirectionDriver()->getDistance())
            && $this->isDistanceLongDistance($proposal->getCriteria()->getDirectionDriver()->getDistance())
            && $this->isOriginOrDestinationFromFrance($proposal)
            && $this->isFirstValidLongECCJourney($proposal);
    }

    /**
     * Checks if the published journey corresponding with the short distance EEC standard. true if:
     *      - the driver is defined
     *      - the journey is a short distance journey
     *      - the driver and passenger pick-up addresses are defined
     *      - the journey is valid and the first.
     */
    public function isStartedJourneyValidShortECCJourney(CarpoolProof $carpoolProof, bool $recovery = false): bool
    {
        $this->setDriver($carpoolProof->getDriver());

        return
            $this->_userValidation->hasValidMobConnectAuth($this->_driver)
            && !is_null($this->_driver)
            && !is_null($carpoolProof->getAsk())
            && !is_null($carpoolProof->getAsk()->getMatching())
            && !is_null($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            && !$this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            && !is_null($carpoolProof->getPickUpDriverAddress())
            && !is_null($carpoolProof->getPickUpPassengerAddress())
            && $this->isFirstValidShortECCJourney($recovery);
    }

    /**
     * Checks if the journey is a valid long distance journey. true if :
     *       - there is a driver and he has subscribed to a long distance subscription
     *       - the journey is a long distance journey
     *       - the origin or the destination is from the reference country
     *       - the journey has not yet been declared.
     */
    public function isCarpoolItemValidLongDistanceJourney(CarpoolItem $carpoolItem): bool
    {
        $this->setDriver($carpoolItem->getCreditorUser());

        return
            !is_null($this->_driver)
            && $this->_userValidation->hasValidMobConnectAuth($this->_driver)
            && !is_null($this->_driver->getLongDistanceSubscription())
            && !is_null($carpoolItem->getAsk())
            && !is_null($carpoolItem->getAsk()->getMatching())
            && $this->isDistanceLongDistance($carpoolItem->getAsk()->getMatching()->getCommonDistance())
            && $this->isOriginOrDestinationFromFrance($carpoolItem);
    }
}
