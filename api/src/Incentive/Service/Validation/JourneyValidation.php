<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Service\LoggerService;

class JourneyValidation extends Validation
{
    public function __construct(LoggerService $loggerService)
    {
        parent::__construct($loggerService);
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
            !is_null($this->_driver)
            && !is_null($this->_driver->getLongDistanceSubscription())
            && is_null($this->_driver->getLongDistanceSubscription()->getCommitmentProofDate())
            && empty($this->_driver->getLongDistanceSubscription()->getLongDistanceJourneys()->toArray())
        ;
    }

    /**
     * Checks if the journey is a valid first short distance journey. true if:
     *      - there is a driver and he has subscribed to a short distance subscription
     *      - the subscription has not been initialized
     *      - there is no recorded journey.
     */
    public function isFirstValidShortECCJourney(): bool
    {
        return
            !is_null($this->_driver)
            && !is_null($this->_driver->getShortDistanceSubscription())
            && is_null($this->_driver->getShortDistanceSubscription()->getCommitmentProofDate())
            && empty($this->_driver->getShortDistanceSubscription()->getShortDistanceJourneys()->toArray())
        ;
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
            !is_null($proposal->getCriteria())
            && $proposal->getCriteria()->isDriver()
            && !is_null($proposal->getCriteria()->getDirectionDriver())
            && !is_null($proposal->getCriteria()->getDirectionDriver()->getDistance())
            && $this->isDistanceLongDistance($proposal->getCriteria()->getDirectionDriver()->getDistance())
            && $this->isOriginOrDestinationFromFrance($proposal)
            && $this->isFirstValidLongECCJourney($proposal)
        ;
    }

    /**
     * Checks if the published journey corresponding with the short distance EEC standard. true if:
     *      - the driver is defined
     *      - the journey is a short distance journey
     *      - the driver and passenger pick-up addresses are defined
     *      - the journey is valid and the first.
     */
    public function isStartedJourneyValidShortECCJourney(CarpoolProof $carpoolProof): bool
    {
        $this->setDriver($carpoolProof->getDriver());

        return
            !is_null($this->_driver)
            && !is_null($carpoolProof->getAsk())
            && !is_null($carpoolProof->getAsk()->getMatching())
            && !is_null($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            && !$this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            && !is_null($carpoolProof->getPickUpDriverAddress())
            && !is_null($carpoolProof->getPickUpPassengerAddress())
            && $this->isFirstValidShortECCJourney()
        ;
    }

    /**
     * Checks if the journey is a valid long distance journey. true if :
     *       - there is a driver and he has subscribed to a long distance subscription
     *       - the journey is a long distance journey
     *       - the origin or the destination is from the reference country
     *       - the journey has not yet been declared.
     */
    public function isCarpoolProofValidLongDistanceJourney(CarpoolProof $carpoolProof): bool
    {
        $this->setDriver($carpoolProof->getDriver());

        return
            !is_null($this->_driver)
            && !is_null($this->_driver->getLongDistanceSubscription())
            && !is_null($carpoolProof->getAsk())
            && !is_null($carpoolProof->getAsk()->getMatching())
            && $this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            && $this->isOriginOrDestinationFromFrance($carpoolProof)
            && !$this->_hasLongDistanceJourneyAlreadyDeclared($carpoolProof)
        ;
    }
}
