<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Waypoint;
use App\Geography\Entity\Address;
use App\Incentive\Resource\CeeStatus;
use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;

/**
 * Provides functions necessary for the CEE journeys validation.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
abstract class CeeJourneyService
{
    public const REFERENCE_COUNTRY = 'France';
    public const REFERENCE_DATE = '2023-01-01';
    public const REFERENCE_PERIOD = 3;                   // Period expressed in years
    public const REFERENCE_TIME_LIMIT = 3;           // In months
    public const LOW_THRESHOLD_PROOF = 1;
    public const RPC_NUMBER_STATUS = 'OK';
    public const LONG_DISTANCE_TRIP_THRESHOLD = 3;
    public const SHORT_DISTANCE_TRIP_THRESHOLD = 10;

    /**
     * @var Matching
     */
    private $_matching;

    /**
     * Returns if an address is located in the REFERENCE_COUNTRY.
     */
    private function __isAddressInFrance(Address $address): bool
    {
        return self::REFERENCE_COUNTRY === $address->getAddressCountry();
    }

    /**
     * Returns if a distance is considered long.
     */
    private function __isLongDistance(int $distance): bool
    {
        return CeeStatus::LONG_DISTANCE_MINIMUM_IN_METERS < $distance;
    }

    /**
     * Returns if the origin or the destination of a journey is located in the REFERENCE_COUNTRY.
     */
    private function __isOriginOrDestinationFromReferenceCountry(): bool
    {
        $waypoints = self::$_matching->getWaypoints();

        $startAddress = array_filter($waypoints, function (Waypoint $waypoint) {
            return 0 === $waypoint->getPosition();
        })[0]->getAddress();
        $endAddress = array_filter($waypoints, function (Waypoint $waypoint) {
            return $waypoint->isDestination();
        })[0]->getAddress();

        return self::__isAddressInFrance($startAddress) || self::__isAddressInFrance($endAddress);
    }

    /**
     * Returns if a distance is considered short.
     */
    private function __isShortDistance(int $distance): bool
    {
        return CeeStatus::LONG_DISTANCE_MINIMUM_IN_METERS >= $distance;
    }

    /**
     * Returns if the user profile is considered as valid for ECC sheets.
     */
    private function __isUserValid(User $user): bool
    {
        return
            !is_null($user->getDrivingLicenseNumber())
            && !is_null($user->getTelephone())
            && !is_null($user->getPhoneValidatedDate())
        ;
    }

    /**
     * Returns if the carpooling payment has been settled.
     */
    private function __hasBeenCarpoolPaymentRegularized(CarpoolProof $carpoolProof): bool
    {
        return !empty(array_filter($carpoolProof->getAsk()->getCarpoolItems(), function (CarpoolItem $carpoolItem) use ($carpoolProof) {
            return
                (CarpoolItem::CREDITOR_STATUS_ONLINE === $carpoolItem->getCreditorStatus() || CarpoolItem::CREDITOR_STATUS_DIRECT === $carpoolItem->getCreditorStatus())
                && $carpoolItem->getCreditorUser() === $carpoolProof->getDriver()
                && $carpoolItem->getDebtorUser() === $carpoolProof->getPassenger()
            ;
        }));
    }

    /**
     * Sets the carpool matching.
     */
    private function __setMatchingFromCarpoolProof(CarpoolProof $carpoolProof): self
    {
        self::$_matching = !is_null($carpoolProof->getAsk()) && !is_null($carpoolProof->getAsk()->getMatching())
                ? $carpoolProof->getAsk()->getMatching() : null;

        return $this;
    }

    // * PUBLIC FUNCTIONS ---------------------------------------------------------------------------------------------------------------------------

    public static function isDateExpired(\DateTime $date): bool
    {
        return $date < new \DateTime('now');
    }

    /**
     * The user has not made any valid long-distance journeys for 3 years.
     */
    public static function isUserAccountReadyForLongDistanceSubscription(User $user): bool
    {
        $carpoolProofs = $user->getCarpoolProofsAsDriver();

        if (!self::__isUserValid($user)) {
            return false;
        }

        if (is_null($carpoolProofs) || empty($carpoolProofs)) {
            return true;
        }

        $today = new \DateTime('now');
        $startDate = clone $today;
        $startDate = $startDate->sub(new \DateInterval('P'.self::REFERENCE_PERIOD.'Y'));

        return empty(array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) use ($startDate, $today) {
            self::__setMatchingFromCarpoolProof($carpoolProof);

            return
                self::__isLongDistance(self::$_matching->getCommonDistance())
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
                && self::__isOriginOrDestinationFromReferenceCountry()
                && $startDate <= $carpoolProof->getStartDriverDate() && $carpoolProof->getStartDriverDate() <= $today
            ;
        }));
    }

    /**
     * The user has not made any valid short-distance journeys since the reference date.
     */
    public static function isUserAccountReadyForShortDistanceSubscription(User $user): bool
    {
        $carpoolProofs = $user->getCarpoolProofsAsDriver();

        if (!self::__isUserValid($user)) {
            return false;
        }

        if (is_null($carpoolProofs) || empty($carpoolProofs)) {
            return true;
        }

        return empty(array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) {
            self::__setMatchingFromCarpoolProof($carpoolProof);

            return
                self::__isShortDistance(self::$_matching->getCommonDistance())
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
                && self::__isOriginOrDestinationFromReferenceCountry()
                && $carpoolProof->getStartDriverDate() < \DateTime::createFromFormat('Y-m-d', self::REFERENCE_DATE)
            ;
        }));
    }

    /**
     * Returns if the trip is valid for a long distance for EEC sheet.
     */
    public static function isValidLongDistanceJourney(CarpoolProof $carpoolProof): bool
    {
        self::__setMatchingFromCarpoolProof($carpoolProof);

        return
            CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
            && !is_null(self::$_matching)
            && self::__isLongDistance(self::$_matching->getCommonDistance())
            && self::__isOriginOrDestinationFromReferenceCountry()
            && !is_null($carpoolProof->getAsk()->getCriteria())
            && self::__hasBeenCarpoolPaymentRegularized($carpoolProof)
        ;
    }

    /**
     * Returns if the trip is valid for a short distance for EEC sheet.
     */
    public static function isValidShortDistanceJourney(CarpoolProof $carpoolProof): bool
    {
        self::__setMatchingFromCarpoolProof($carpoolProof);

        return
            CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
            && !is_null(self::$_matching)
            && self::__isShortDistance(self::$_matching->getCommonDistance())
            && self::__isOriginOrDestinationFromReferenceCountry()
        ;
    }
}
