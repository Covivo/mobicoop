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
     * Checks if the published journey corresponding with the long distance EEC standard.
     */
    public function isPublishedJourneyValidLongECCJourney(Proposal $proposal): bool
    {
        $this->_loggerService->log('Checks if the '.$proposal->getId().' journey corresponding to the long distance EEC standard');

        if (
            is_null($proposal->getCriteria())
            || !$proposal->getCriteria()->isDriver()
        ) {
            $this->_loggerService->log('The journey '.$proposal->getId().' is not a driver journey');

            return false;
        }

        $driver = $proposal->getUser();

        if (is_null($driver)) {
            $this->_loggerService->log('The journey '.$proposal->getId().' must have a driver');

            return false;
        }

        if (is_null($driver->getLongDistanceSubscription())) {
            $this->_loggerService->log('The driver '.$driver->getId().' has not subscribed to long distance incentive');

            return false;
        }

        if (!empty($driver->getLongDistanceSubscription()->getLongDistanceJourneys()->toArray())) {
            $this->_loggerService->log('There is already at least one declared long-distance journey');

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

        $driver = $carpoolProof->getDriver();

        if (is_null($driver)) {
            $this->_loggerService->log('The proof must have a driver');

            return false;
        }

        if (is_null($driver->getShortDistanceSubscription())) {
            $this->_loggerService->log('The driver '.$driver->getId().' has not subscribed to short distance incentive');

            return false;
        }

        if (!empty($driver->getShortDistanceSubscription()->getShortDistanceJourneys()->toArray())) {
            $this->_loggerService->log('There is already at least one declared short-distance journey');

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
