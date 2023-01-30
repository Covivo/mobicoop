<?php

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Matching;
use App\Geography\Entity\Address;
use App\Incentive\Entity\Log;
use App\Incentive\Resource\CeeSubscriptions;
use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;
use Psr\Log\LoggerInterface;

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

    // LOG
    private const ACCOUNT_READY_FOR_LONG_SUBSCRIPTION = 'is_user_account_ready_for_long_subscription';
    private const ACCOUNT_READY_FOR_SHORT_SUBSCRIPTION = 'is_user_account_ready_for_short_subscription';
    private const VALID_LONG_DISTANCE_JOURNEY = 'is_valid_long_distance_journey';
    private const VALID_SHORT_DISTANCE_JOURNEY = 'is_valid_short_distance_journey';

    /**
     * @var Matching
     */
    private static $_matching;

    /**
     * @var LoggerInterface
     */
    private static $_logger;

    /**
     * Returns if an address is located in the REFERENCE_COUNTRY.
     */
    private function __isAddressInFrance(?Address $address): bool
    {
        if (is_null($address)) {
            return false;
        }

        return self::REFERENCE_COUNTRY === $address->getAddressCountry();
    }

    /**
     * Returns if a distance is considered long.
     */
    private function __isLongDistance(int $distance): bool
    {
        return CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS < $distance;
    }

    /**
     * Returns if the origin or the destination of a journey is located in the REFERENCE_COUNTRY.
     */
    private function __isOriginOrDestinationFromReferenceCountry(): bool
    {
        if (is_null(self::$_matching)) {
            return true;
        }

        $waypoints = self::$_matching->getWaypoints();

        $startAddress = null;
        foreach ($waypoints as $waypoint) {
            if (0 === $waypoint->getPosition()) {
                $startAddress = $waypoint->getAddress();

                break;
            }
        }

        $endAddress = null;
        foreach ($waypoints as $waypoint) {
            if ($waypoint->isDestination()) {
                $endAddress = $waypoint->getAddress();

                break;
            }
        }

        if (is_null($startAddress)) {
            throw new \LogicException('No start Address');
        }
        if (is_null($endAddress)) {
            throw new \LogicException('No end Address');
        }

        return self::__isAddressInFrance($startAddress) || self::__isAddressInFrance($endAddress);
    }

    /**
     * Returns if a distance is considered short.
     */
    private function __isShortDistance(int $distance): bool
    {
        return CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS >= $distance;
    }

    /**
     * Returns if the user profile is considered as valid for ECC sheets.
     */
    private function __isUserValid(User $user): bool
    {
        return
            !is_null($user->getDrivingLicenceNumber())
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
    private function __setMatchingFromCarpoolProof(CarpoolProof $carpoolProof)
    {
        self::$_matching = !is_null($carpoolProof->getAsk()) && !is_null($carpoolProof->getAsk()->getMatching())
                ? $carpoolProof->getAsk()->getMatching() : null;
    }

    // * PUBLIC FUNCTIONS ---------------------------------------------------------------------------------------------------------------------------

    public static function isDateAfterReferenceDate(\DateTime $date): bool
    {
        return new \DateTime(self::REFERENCE_DATE) <= $date;
    }

    public static function isDateInPeriod(\DateTime $dateToCheck): bool
    {
        $dateEndPeriod = new \DateTime('now');
        $dateStartPeriod = clone $dateEndPeriod;
        $dateStartPeriod = $dateStartPeriod->sub(new \DateInterval('P'.self::REFERENCE_PERIOD.'M'));

        return $dateStartPeriod <= $dateToCheck && $dateToCheck <= $dateEndPeriod;
    }

    /**
     * The user has not made any valid long-distance journeys for 3 years.
     */
    public static function isUserAccountReadyForLongDistanceSubscription(User $user, LoggerInterface $logger): bool
    {
        $carpoolProofs = $user->getCarpoolProofsAsDriver();
        $isUserValid = self::__isUserValid($user);

        if (is_null($carpoolProofs) || empty($carpoolProofs)) {
            $isEmptyCarpoolProof = true;
            new Log($logger, self::ACCOUNT_READY_FOR_LONG_SUBSCRIPTION, $user, [Log::IS_USER_VALID => $isUserValid, Log::IS_CARPOOL_PROOFS_VALID => $isEmptyCarpoolProof]);

            return $isEmptyCarpoolProof;
        }

        $today = new \DateTime('now');

        $filteredCarpoolProofs = array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) {
            self::__setMatchingFromCarpoolProof($carpoolProof);

            return
                self::__isLongDistance(self::$_matching->getCommonDistance())
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
                && self::__isOriginOrDestinationFromReferenceCountry()
                && self::isDateInPeriod($carpoolProof->getStartDriverDate())
            ;
        });

        new Log($logger, self::ACCOUNT_READY_FOR_LONG_SUBSCRIPTION, $user, [Log::IS_USER_VALID => $isUserValid, Log::IS_CARPOOL_PROOFS_VALID => is_null($carpoolProofs) || !empty($carpoolProofs) || empty($filteredCarpoolProofs)]);

        if (!self::__isUserValid($user)) {
            return false;
        }

        return empty($filteredCarpoolProofs);
    }

    /**
     * The user has not made any valid short-distance journeys since the reference date.
     */
    public static function isUserAccountReadyForShortDistanceSubscription(User $user, LoggerInterface $logger): bool
    {
        $carpoolProofs = $user->getCarpoolProofsAsDriver();

        $isUserValid = self::__isUserValid($user);

        if (is_null($carpoolProofs) || empty($carpoolProofs)) {
            $isEmptyCarpoolProof = true;
            new Log($logger, self::ACCOUNT_READY_FOR_SHORT_SUBSCRIPTION, $user, [Log::IS_USER_VALID => $isUserValid, Log::IS_CARPOOL_PROOFS_VALID => $isEmptyCarpoolProof]);

            return $isEmptyCarpoolProof;
        }

        $filteredCarpoolProofs = array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) {
            self::__setMatchingFromCarpoolProof($carpoolProof);

            return
                self::__isShortDistance(self::$_matching->getCommonDistance())
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
                && self::__isOriginOrDestinationFromReferenceCountry()
                && self::isDateAfterReferenceDate($carpoolProof->getStartDriverDate())
            ;
        });

        new Log($logger, self::ACCOUNT_READY_FOR_SHORT_SUBSCRIPTION, $user, [Log::IS_USER_VALID => $isUserValid, Log::IS_CARPOOL_PROOFS_VALID => is_null($carpoolProofs) || empty($carpoolProofs) || empty($filteredCarpoolProofs)]);

        if (!self::__isUserValid($user)) {
            return false;
        }

        return empty($filteredCarpoolProofs);
    }

    /**
     * Returns if the trip is valid for a long distance for EEC sheet.
     */
    public static function isValidLongDistanceJourney(CarpoolProof $carpoolProof, LoggerInterface $logger): bool
    {
        self::__setMatchingFromCarpoolProof($carpoolProof);

        new Log($logger, self::VALID_LONG_DISTANCE_JOURNEY, $carpoolProof->getDriver(), [Log::CARPOOL_PROOF_ID => $carpoolProof->getId(), Log::TYPE_C => CarpoolProof::TYPE_HIGH === $carpoolProof->getType(), Log::MATCHING_ID => !is_null(self::$_matching) ? self::$_matching->getId() : 0, Log::IS_LONG_DISTANCE => self::__isLongDistance(self::$_matching->getCommonDistance()), Log::IS_FROM_FRANCE => self::__isOriginOrDestinationFromReferenceCountry(), Log::IS_PAYMENT_REGULARIZED => self::__hasBeenCarpoolPaymentRegularized($carpoolProof)]);

        return
            self::__isLongDistance(self::$_matching->getCommonDistance())
            && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
            && self::__isOriginOrDestinationFromReferenceCountry()
            && self::__hasBeenCarpoolPaymentRegularized($carpoolProof)
            && self::isDateInPeriod($carpoolProof->getStartDriverDate())
        ;
    }

    /**
     * Returns if the trip is valid for a short distance for EEC sheet.
     */
    public static function isValidShortDistanceJourney(CarpoolProof $carpoolProof, LoggerInterface $logger): bool
    {
        self::__setMatchingFromCarpoolProof($carpoolProof);

        new Log($logger, self::VALID_SHORT_DISTANCE_JOURNEY, $carpoolProof->getDriver(), [Log::TYPE_C => CarpoolProof::TYPE_HIGH === $carpoolProof->getType(), Log::MATCHING_ID => !is_null(self::$_matching) ? self::$_matching->getId() : 0, Log::IS_LONG_DISTANCE => self::__isLongDistance(self::$_matching->getCommonDistance()), Log::IS_FROM_FRANCE => self::__isOriginOrDestinationFromReferenceCountry()]);

        return
            self::__isShortDistance(self::$_matching->getCommonDistance())
            && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()
            && self::__isOriginOrDestinationFromReferenceCountry()
            && !self::isDateAfterReferenceDate($carpoolProof->getStartDriverDate())
        ;
    }
}
