<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;

abstract class Validation
{
    public const LONG_DISTANCE_THRESHOLD = CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS;
    public const REFERENCE_COUNTRY = 'France';

    public const REFERENCE_DATE = '2023-01-01';
    public const REFERENCE_PERIOD = 3;                   // Period expressed in years

    /**
     * @var User
     */
    protected $_driver;

    /**
     * @var LoggerService
     */
    protected $_loggerService;

    public function __construct(LoggerService $loggerService)
    {
        $this->_loggerService = $loggerService;
    }

    public function isDistanceLongDistance(int $distance): bool
    {
        return self::LONG_DISTANCE_THRESHOLD <= $distance;
    }

    public function isOriginOrDestinationFromFrance($journey): bool
    {
        switch (true) {
            case $journey instanceof CarpoolProof:
                return $this->_isOriginOrDestinationFromFranceForCarpoolProof($journey);

            case $journey instanceof Matching:
                return $this->_isOriginOrDestinationFromFranceForMatching($journey);

            case $journey instanceof Proposal:
                return $this->_isOriginOrDestinationFromFranceForProposal($journey);

            default:
                throw new \LogicException('The class '.get_class($journey).' cannot be processed');
        }
    }

    protected function isDateAfterReferenceDate(\DateTime $date): bool
    {
        return new \DateTime(self::REFERENCE_DATE) <= $date;
    }

    protected static function isDateInPeriod(\DateTime $dateToCheck): bool
    {
        $dateEndPeriod = new \DateTime('now');
        $dateStartPeriod = clone $dateEndPeriod;
        $dateStartPeriod = $dateStartPeriod->sub(new \DateInterval('P'.self::REFERENCE_PERIOD.'M'));

        return $dateStartPeriod <= $dateToCheck && $dateToCheck <= $dateEndPeriod;
    }

    protected function isUserValid()
    {
        return
            !is_null($this->_driver->getDrivingLicenceNumber())
            && !is_null($this->_driver->getTelephone())
            && !is_null($this->_driver->getPhoneValidatedDate())
        ;
    }

    protected function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        if (is_null($this->_driver)) {
            $this->_loggerService->log('The proof must have a driver');
        }

        return $this;
    }

    private function _isOriginOrDestinationFromFranceForCarpoolProof(CarpoolProof $carpoolProof): bool
    {
        return $this->_isOriginOrDestinationFromFranceForMatching($carpoolProof->getAsk()->getMatching());
    }

    private function _isOriginOrDestinationFromFranceForMatching(Matching $matching): bool
    {
        return $this->_isOriginOrDestinationFromFranceForWaypoints($matching->getWaypoints());
    }

    private function _isOriginOrDestinationFromFranceForProposal(Proposal $proposal): bool
    {
        return $this->_isOriginOrDestinationFromFranceForWaypoints($proposal->getWaypoints());
    }

    private function _isOriginOrDestinationFromFranceForWaypoints($waypoints): bool
    {
        if (empty($waypoints)) {
            return false;
        }

        foreach ($waypoints as $waypoint) {
            if (
                !is_null($waypoint->getAddress())
                && !is_null($waypoint->getAddress()->getAddressCountry())
                && self::REFERENCE_COUNTRY === $waypoint->getAddress()->getAddressCountry()
            ) {
                return true;
            }
        }

        return false;
    }
}
