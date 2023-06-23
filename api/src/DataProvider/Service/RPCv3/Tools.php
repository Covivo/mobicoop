<?php

namespace App\DataProvider\Service\RPCv3;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\Geography\Entity\Address;
use App\Incentive\Resource\CeeSubscriptions;
use App\Service\DrivingLicenceService;
use App\Service\PhoneService;
use App\User\Entity\User;

class Tools
{
    public const POSITION_ORIGIN = 'origin';
    public const POSITION_DESTINATION = 'destination';

    public const DRIVER = 'driver';
    public const PASSENGER = 'passenger';

    public const SHORT_TYPE = 'short';
    public const LONG_TYPE = 'long';

    public const FAMILY_NAME_TRUNC_LEN = 3;

    private const ALLOWED_CARPOOLER_TYPES = [self::DRIVER, self::PASSENGER];
    private const DEFAULT_SUBSTITUTION_CHARACTER = ' ';

    /**
     * @var CarpoolProof
     */
    private $_currentCarpoolProof;

    /**
     * @var int
     */
    private $_phoneNumberTruncLength;

    /**
     * @var string
     */
    private $_prefix;

    public function __construct(int $phoneNumberTruncLength, string $prefix)
    {
        $this->_phoneNumberTruncLength = $phoneNumberTruncLength;
        $this->_prefix = $prefix;
    }

    /**
     * Set the value of _currentCarpoolProof.
     */
    public function setCurrentCarpoolProof(CarpoolProof $carpoolProof): self
    {
        $this->_currentCarpoolProof = $carpoolProof;

        return $this;
    }

    public function getDistance(): int
    {
        return
            !is_null($this->_currentCarpoolProof->getAsk()->getMatching())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching()->getCommonDistance())
                ? $this->_currentCarpoolProof->getAsk()->getMatching()->getCommonDistance() : 0;
    }

    /**
     * Matches the sha of a concatenated string such as: sha256(phone_number-last_name).
     */
    public function getIdentityKey(string $carpoolerType): ?string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        $phoneNumber = $this->_getInternationalPhone($carpoolerType);

        return hash(
            'sha256',
            $phoneNumber.'-'.(!is_null($carpooler) ? $this->_familyNameToUppercase($carpooler->getFamilyName()) : null)
        );
    }

    public function getCommitmentDate(): string
    {
        $getter = 'getMobConnect'.ucfirst($this->getProofType()).'DistanceJourney';

        return !is_null($this->_currentCarpoolProof->{$getter}()->getSubscription()->getCommitmentProofDate())
            ? $this->_currentCarpoolProof->{$getter}()->getSubscription()->getCommitmentProofDate()->format(CarpoolProofGouvProvider::ISO8601) : null;
    }

    public function getDrivingLicenceNumber(string $carpoolerType): ?string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        $drivingLicenceService = new DrivingLicenceService($carpooler->getDrivingLicenceNumber());

        return $drivingLicenceService->isDrivingLicenceNumberValid()
            ? $carpooler->getDrivingLicenceNumber() : null;
    }

    public function getFamilyNameTrunc(string $carpoolerType): string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        $familynameTrunc = substr($carpooler->getFamilyName(), 0, self::FAMILY_NAME_TRUNC_LEN);

        $diff = self::FAMILY_NAME_TRUNC_LEN - strlen($familynameTrunc);

        if ($diff) {
            for ($i = 0; $i < $diff; ++$i) {
                $familynameTrunc .= self::DEFAULT_SUBSTITUTION_CHARACTER;
            }
        }

        return $familynameTrunc;
    }

    public function getPhoneNumber(string $carpoolerType): ?string
    {
        return $this->_getInternationalPhone($carpoolerType);
    }

    public function getPhoneTruncNumber(string $carpoolerType): ?string
    {
        return $this->_getInternationalPhone($carpoolerType, true, $this->_phoneNumberTruncLength);
    }

    public function getProofType(): string
    {
        return
            !is_null($this->_currentCarpoolProof->getAsk())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching()->getCommonDistance())
            ? (
                $this->_currentCarpoolProof->getAsk()->getMatching()->getCommonDistance() >= CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS
                ? self::LONG_TYPE : self::SHORT_TYPE
            )
            : null;
    }

    public function getStartTimeGeopoint(): array
    {
        $startDatetime = !is_null($this->_currentCarpoolProof->getPickUpPassengerDate())
            ? $this->_currentCarpoolProof->getPickUpPassengerDate()->format(CarpoolProofGouvProvider::ISO8601)
            : $this->_getStartDateTime();

        $originAddress = !is_null($this->_currentCarpoolProof->getPickUpPassengerAddress())
            ? $this->_currentCarpoolProof->getPickUpPassengerAddress()
            : $this->_getOriginAddress();

        return $this->_getTimeGeopoint($startDatetime, $originAddress);
    }

    public function getEndTimeGeopoint(): array
    {
        $endDatetime = !is_null($this->_currentCarpoolProof->getDropOffPassengerDate())
            ? $this->_currentCarpoolProof->getDropOffPassengerDate()->format(CarpoolProofGouvProvider::ISO8601)
            : $this->_getEndDateTime();

        $destinationAddress = !is_null($this->_currentCarpoolProof->getDropOffPassengerAddress())
            ? $this->_currentCarpoolProof->getDropOffPassengerAddress()
            : $this->_getDestinationAddress();

        return $this->_getTimeGeopoint($endDatetime, $destinationAddress);
    }

    public function getOperatorJourneyId(): string
    {
        return (string) (!is_null($this->_prefix) ? $this->_prefix : '').(string) $this->_currentCarpoolProof->getId();
    }

    public function getOperatorUserId(string $carpoolerType): string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        return (string) (!is_null($this->_prefix) ? $this->_prefix : '').(string) $carpooler->getId();
    }

    public function getOver18(string $carpoolerType)
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        $over18 = null;
        if (!is_null($carpooler->getBirthDate())) {
            $over18 = $carpooler->getBirthDate()->diff(new \DateTime('now'))->y >= 18;
        }

        return $over18;
    }

    private function _familyNameToUppercase(string $familyName): string
    {
        $familyName = htmlentities($familyName, ENT_NOQUOTES, 'utf-8');
        $familyName = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $familyName);
        $familyName = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $familyName);
        $familyName = preg_replace('#&[^;]+;#', '', $familyName);

        return str_replace('\'', ' ', $familyName);
    }

    private function _getCarpooler(string $carpoolerType): ?User
    {
        if (!in_array($carpoolerType, self::ALLOWED_CARPOOLER_TYPES)) {
            return null;
        }

        switch ($carpoolerType) {
            case self::DRIVER:
                return $this->_currentCarpoolProof->getDriver();

            case self::PASSENGER:
                return $this->_currentCarpoolProof->getPassenger();
        }

        return null;
    }

    private function _getInternationalPhone(string $carpoolerType, bool $trunc = false, int $truncLen = null): ?string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        if (!is_null($carpooler)) {
            $phoneService = new PhoneService($carpooler->getTelephone(), PhoneService::FR);

            return $phoneService->getInternationalPhoneNumber($trunc, $truncLen);
        }

        return null;
    }

    private function _getTimeGeopoint(?\DateTimeInterface $datetime, ?Address $address): array
    {
        return [
            'datetime' => !is_null($datetime) ? $datetime->format(CarpoolProofGouvProvider::ISO8601) : null,
            'lat' => !is_null($address) ? floatval($address->getLatitude()) : null,
            'lon' => !is_null($address) ? floatval($address->getLongitude()) : null,
        ];
    }

    private function _getStartDatetime(): ?\DateTime
    {
        return !is_null($this->_currentCarpoolProof->getAsk()->getMatching()) && !is_null($this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria())
            ? \DateTime::createFromFormat(
                'Y-m-d H:m',
                $this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria()->getFromDate()->format('Y-m-d ').$this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria()->getFromTime()->format('H:m')
            ) : null;
    }

    private function _getEndDatetime(): ?\DateTime
    {
        $startDatetime = $this->_getStartDatetime();

        if (is_null($startDatetime)) {
            return null;
        }

        $endDatetime = clone $startDatetime;

        return
            !is_null($this->_currentCarpoolProof->getAsk())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching()->getNewDuration())
            ? $endDatetime->add(new \DateInterval('PT'.$this->_currentCarpoolProof->getAsk()->getMatching()->getNewDuration().'S'))
            : null;
    }

    private function _getWaypoints(): array
    {
        return
            !is_null($this->_currentCarpoolProof)
            && !is_null($this->_currentCarpoolProof->getAsk())
            && !is_null($this->_currentCarpoolProof->getAsk()->getMatching())
            ? $this->_currentCarpoolProof->getAsk()->getMatching()->getWaypoints()
            : [];
    }

    private function _getOriginAddress(): ?Address
    {
        $originWaypoint = array_values(array_filter(
            $this->_getWaypoints(),
            function ($waypoint) {
                return 0 === $waypoint->getPosition() && false === $waypoint->isDestination();
            }
        ));

        return !empty($originWaypoint) ? $originWaypoint[0]->getAddress() : null;
    }

    private function _getDestinationAddress(): ?Address
    {
        $destinationWaypoint = array_values(array_filter(
            $this->_getWaypoints(),
            function ($waypoint) {
                return true === $waypoint->isDestination();
            }
        ));

        return !empty($destinationWaypoint) ? $destinationWaypoint[0]->getAddress() : null;
    }
}
