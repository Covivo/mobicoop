<?php

namespace App\DataProvider\Service\RPCv3;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\Geography\Entity\Address;
use App\Service\DrivingLicenceService;
use App\Service\PhoneService;
use App\User\Entity\User;

class Tools
{
    public const POSITION_ORIGIN = 'origin';
    public const POSITION_DESTINATION = 'destination';

    public const DRIVER = 'driver';
    public const PASSENGER = 'passenger';

    private const ALLOWED_CARPOOLER_TYPES = [self::DRIVER, self::PASSENGER];

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
     *
     * @param CarpoolProof $_currentCarpoolProof
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
            $phoneNumber.'-'.(!is_null($carpooler) ? $carpooler->getFamilyName() : null)
        );
    }

    public function getDrivingLicenceNumber(string $carpoolerType): ?string
    {
        $carpooler = $this->_getCarpooler($carpoolerType);

        $drivingLicenceService = new DrivingLicenceService($carpooler->getDrivingLicenceNumber());

        return $drivingLicenceService->isDrivingLicenceNumberValid()
            ? $carpooler->getDrivingLicenceNumber() : null;
    }

    public function getPhoneNumber(string $carpoolerType): ?string
    {
        return $this->_getInternationalPhone($carpoolerType);
    }

    public function getPhoneTruncNumber(string $carpoolerType): ?string
    {
        return $this->_getInternationalPhone($carpoolerType, true, $this->_phoneNumberTruncLength);
    }

    public function getStartTimeGeopoint(): array
    {
        $datetime = !is_null($this->_currentCarpoolProof->getPickUpPassengerDate())
            ? $this->_currentCarpoolProof->getPickUpPassengerDate() : null;

        $geopoint = !is_null($this->_currentCarpoolProof->getPickUpPassengerAddress())
            ? $this->_getGeopoint($this->_currentCarpoolProof->getPickUpPassengerAddress(), self::POSITION_ORIGIN) : null;

        return $this->_getTimeGeopoint($datetime, $geopoint['lat'], $geopoint['lon']);
    }

    public function getEndTimeGeopoint(): array
    {
        $datetime = !is_null($this->_currentCarpoolProof->getDropOffPassengerDate())
            ? $this->_currentCarpoolProof->getDropOffPassengerDate() : null;

        $geopoint = !is_null($this->_currentCarpoolProof->getDropOffPassengerAddress())
            ? $this->_getGeopoint($this->_currentCarpoolProof->getDropOffPassengerAddress(), self::POSITION_DESTINATION) : null;

        return $this->_getTimeGeopoint($datetime, $geopoint['lat'], $geopoint['lon']);
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

    private function _getTimeGeopoint(?\DateTimeInterface $datetime, ?float $lat, ?float $lon): array
    {
        return [
            'datetime' => !is_null($datetime) ? $datetime->format(CarpoolProofGouvProvider::ISO8601) : null,
            'lat' => $lat,
            'lon' => $lon,
        ];
    }

    private function _getStartDatetime(): ?\DateTime
    {
        return !is_null($this->_currentCarpoolProof->getAsk()->getMatching()) && !is_null($this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria())
            ? \DateTime::createFromFormat(
                'Y-m-d H:m',
                $this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria()->getFromDate()->format('Y-m-d ').$this->_currentCarpoolProof->getAsk()->getMatching()->getCriteria()->getFromTime('H:m')
            ) : null;
    }

    private function _getGeopoint(Address $address): array
    {
        return [
            'lat' => $address->getLatitude(),
            'lon' => $address->getLongitude(),
        ];
    }
}
