<?php

namespace App\Incentive\Service\Checker;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Incentive\Service\LoggerService;

class JourneyChecker extends Checker
{
    public function __construct(LoggerService $loggerService)
    {
        parent::__construct($loggerService);
    }

    /**
     * Checks if the journey is a valid first long distance journey.
     */
    public function isFirstValidLongECCJourney(): bool
    {
        if (is_null($this->_driver->getLongDistanceSubscription())) {
            $this->_loggerService->log('The driver '.$this->_driver->getId().' has not subscribed to long distance incentive');

            return false;
        }

        if (!is_null($this->_driver->getLongDistanceSubscription()->getCommitmentProofDate())) {
            $this->_loggerService->log('The long distance subscription has already been initialized with a journey');

            return false;
        }

        if (!empty($this->_driver->getLongDistanceSubscription()->getLongDistanceJourneys()->toArray())) {
            $this->_loggerService->log('There is already at least one declared long-distance journey');

            return false;
        }

        return true;
    }

    /**
     * Checks if the journey is a valid first short distance journey.
     */
    public function isFirstValidShortECCJourney(): bool
    {
        if (is_null($this->_driver->getShortDistanceSubscription())) {
            $this->_loggerService->log('The driver '.$this->_driver->getId().' has not subscribed to short distance incentive');

            return false;
        }

        if (!is_null($this->_driver->getShortDistanceSubscription()->getCommitmentProofDate())) {
            $this->_loggerService->log('The short distance subscription has already been initialized with a journey');

            return false;
        }

        if (!empty($this->_driver->getShortDistanceSubscription()->getShortDistanceJourneys()->toArray())) {
            $this->_loggerService->log('There is already at least one declared short-distance journey');

            return false;
        }

        return true;
    }

    /**
     * Checks if the published journey corresponding with the long distance EEC standard.
     */
    public function isPublishedJourneyValidLongECCJourney(Proposal $proposal): bool
    {
        $this->_loggerService->log('Checks if the '.$proposal->getId().' journey corresponding to the long distance EEC standard');

        if (
            is_null($proposal->getCriteria())
            || is_null($proposal->getCriteria()->isDriver())
        ) {
            $this->_loggerService->log('The journey '.$proposal->getId().' is not a driver journey');

            return false;
        }

        $this->setDriver($proposal->getUser());

        if (!$this->isFirstValidLongECCJourney($proposal)) {
            return false;
        }

        if (
            is_null($proposal->getCriteria()->getDirectionDriver())
            || !$this->isDistanceLongDistance($proposal->getCriteria()->getDirectionDriver()->getDistance())
        ) {
            $this->_loggerService->log('The journey '.$proposal->getId().' is not a long distance journey');

            return false;
        }

        if (!$this->isOriginOrDestinationFromFrance($proposal)) {
            $this->_loggerService->log('The journey '.$proposal->getId().' is not from or to the '.self::REFERENCE_COUNTRY);

            return false;
        }

        $this->_loggerService->log('The journey '.$proposal->getId().' corresponding to the long distance EEC standard');

        return true;
    }

    /**
     * Checks if the published journey corresponding with the short distance EEC standard.
     */
    public function isStartedJourneyValidShortECCJourney(CarpoolProof $carpoolProof): bool
    {
        $this->_loggerService->log('Checks if the '.$carpoolProof->getId().' carpool proof corresponding to the short distance EEC standard');

        if (is_null($carpoolProof->getDriver())) {
            $this->_loggerService->log('The journey '.$carpoolProof->getId().' has no defined driver');

            return false;
        }

        $this->setDriver($carpoolProof->getDriver());

        if (!$this->isFirstValidShortECCJourney()) {
            return false;
        }

        if (
            is_null($carpoolProof->getAsk())
            || is_null($carpoolProof->getAsk()->getMatching())
            || is_null($carpoolProof->getAsk()->getMatching()->getCommonDistance())
            || $this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())
        ) {
            $this->_loggerService->log('The carpool proof '.$carpoolProof->getId().' is not a short distance proof');

            return false;
        }

        if (is_null($carpoolProof->getPickUpDriverAddress()) || is_null($carpoolProof->getPickUpPassengerAddress())) {
            $this->_loggerService->log('For the carpool proof '.$carpoolProof->getId().' one of the 2 carpoolers has not certified the pick-up');

            return false;
        }

        $this->_loggerService->log('The carpool proof '.$carpoolProof->getId().' corresponding to the short distance EEC standard');

        return true;
    }
}
