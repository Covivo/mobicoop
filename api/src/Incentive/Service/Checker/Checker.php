<?php

namespace App\Incentive\Service\Checker;

use App\Carpool\Entity\Proposal;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;

abstract class Checker
{
    public const LONG_DISTANCE_THRESHOLD = CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS;
    public const REFERENCE_COUNTRY = 'France';

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

    protected function isDistanceLongDistance(int $distance): bool
    {
        return self::LONG_DISTANCE_THRESHOLD <= $distance;
    }

    protected function isOriginOrDestinationFromFrance($journey): bool
    {
        switch (true) {
            case $journey instanceof Proposal:
                return $this->_isOriginOrDestinationFromFranceForProposal($journey);

            default:
                throw new \LogicException('The class '.get_class($journey).' cannot be processed');
        }
    }

    private function _isOriginOrDestinationFromFranceForProposal(Proposal $proposal): bool
    {
        $waypoints = $proposal->getWaypoints();

        if (empty($waypoints)) {
            $this->_loggerService->log('There is no origin or destination point for the journey '.$proposal->getId());

            return false;
        }

        foreach ($waypoints as $waypoint) {
            if (self::REFERENCE_COUNTRY === $waypoint->getAddress()->getAddressCountry()) {
                return true;
            }
        }

        return false;
    }
}
