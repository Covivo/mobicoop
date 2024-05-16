<?php

namespace App\Service;

/**
 * This service can be used to check the validity of a driver's license number.
 * The patterns used conform to the definition of the Carpool Evidence Registry services.
 *
 * @author Olivier FIllol <olivier.fillol@mobicoop.org>
 */
class DrivingLicenceService
{
    public const AFTER_1975_PATTERN = '/^[0-9]{12}$/';
    public const BEFORE_1975_PATTERN = '/^[A-Z0-9]{1,15}[0-9]{4}$/';
    public const OLDEST_PATTERN = '/^[A-Z0-9]{1,15}$/';
    public const FOREIGN_PATTERN = '/^99-.*$/';

    /**
     * @var string
     */
    private $_drivingLicenceNumber;

    public function __construct(?string $drivingLicenceNumber)
    {
        $this->_drivingLicenceNumber = $drivingLicenceNumber;
    }

    public function isDrivingLicenceNumberValid(): bool
    {
        return
            $this->isDrivingLicenceValidSWithPre1975tandard()
            || $this->isDrivingLicenceValidSWithAfter1975tandard()
            || $this->isDrivingLicenceValidWithOldest()
            || $this->isDrivingLicenceValidSWithForeigntandard();
    }

    public function isDrivingLicenceValidSWithPre1975tandard(): bool
    {
        return preg_match(
            self::BEFORE_1975_PATTERN,
            $this->_drivingLicenceNumber
        );
    }

    public function isDrivingLicenceValidSWithAfter1975tandard(): bool
    {
        return preg_match(
            self::AFTER_1975_PATTERN,
            $this->_drivingLicenceNumber
        );
    }

    public function isDrivingLicenceValidWithOldest(): bool
    {
        return preg_match(
            self::OLDEST_PATTERN,
            $this->_drivingLicenceNumber
        );
    }

    public function isDrivingLicenceValidSWithForeigntandard(): bool
    {
        return preg_match(
            self::FOREIGN_PATTERN,
            $this->_drivingLicenceNumber
        );
    }
}
