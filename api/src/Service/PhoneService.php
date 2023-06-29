<?php

namespace App\Service;

class PhoneService
{
    public const PHONE_PREFIX = '+';
    public const PATTERN_START = '/^';
    public const PATTERN_END = '$/';

    public const FR = 'FR';

    public const COUNTRY_PHONE_PREFIX = [
        'AD' => 376,
        'AL' => 355,
        'AM' => 374,
        'AT' => 43,
        'BA' => 387,
        'BE' => 32,
        'BG' => 359,
        'BY' => 375,
        'CH' => 41,
        'CY' => 357,
        'CZ' => 420,
        'DE' => 49,
        'DK' => 45,
        'EE' => 372,
        'ES' => 34,
        'FI' => 358,
        self::FR => 33,
        'GB' => 44,
        'GI' => 350,
        'GR' => 30,
        'HR' => 385,
        'HU' => 36,
        'IE' => 353,
        'IS' => 354,
        'IT' => 39,
        'LI' => 423,
        'LT' => 370,
        'LU' => 352,
        'LV' => 371,
        'MC' => 377,
        'MD' => 373,
        'ME' => 382,
        'MK' => 389,
        'MT' => 356,
        'NL' => 31,
        'NO' => 47,
        'PL' => 48,
        'PT' => 351,
        'RO' => 40,
        'RS' => 381,
        'SE' => 46,
        'SI' => 386,
        'SK' => 421,
        'SM' => 378,
        'UA' => 380,
        'VA' => 379,
        'XK' => 383,
    ];

    private const INTERNATIONAL_PHONE_PATTERN = [
        'AD' => null,
        'AL' => null,
        'AM' => null,
        'AT' => null,
        'BA' => null,
        'BE' => null,
        'BG' => null,
        'BY' => null,
        'CH' => null,
        'CY' => null,
        'CZ' => null,
        'DE' => null,
        'DK' => null,
        'EE' => null,
        'ES' => null,
        'FI' => null,
        self::FR => '(([1-9](([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2}))))',
        'GB' => null,
        'GI' => null,
        'GR' => null,
        'HR' => null,
        'HU' => null,
        'IE' => null,
        'IS' => null,
        'IT' => null,
        'LI' => null,
        'LT' => null,
        'LU' => null,
        'LV' => null,
        'MC' => null,
        'MD' => null,
        'ME' => null,
        'MK' => null,
        'MT' => null,
        'NL' => null,
        'NO' => null,
        'PL' => null,
        'PT' => null,
        'RO' => null,
        'RS' => null,
        'SE' => null,
        'SI' => null,
        'SK' => null,
        'SM' => null,
        'UA' => null,
        'VA' => null,
        'XK' => null,
    ];

    private const PHONE_PATTERN = [
        'AD' => null,
        'AL' => null,
        'AM' => null,
        'AT' => null,
        'BA' => null,
        'BE' => null,
        'BG' => null,
        'BY' => null,
        'CH' => null,
        'CY' => null,
        'CZ' => null,
        'DE' => null,
        'DK' => null,
        'EE' => null,
        'ES' => null,
        'FI' => null,
        self::FR => '((01|02|03|04|05|06|07|08|09)([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2}))',
        'GB' => null,
        'GI' => null,
        'GR' => null,
        'HR' => null,
        'HU' => null,
        'IE' => null,
        'IS' => null,
        'IT' => null,
        'LI' => null,
        'LT' => null,
        'LU' => null,
        'LV' => null,
        'MC' => null,
        'MD' => null,
        'ME' => null,
        'MK' => null,
        'MT' => null,
        'NL' => null,
        'NO' => null,
        'PL' => null,
        'PT' => null,
        'RO' => null,
        'RS' => null,
        'SE' => null,
        'SI' => null,
        'SK' => null,
        'SM' => null,
        'UA' => null,
        'VA' => null,
        'XK' => null,
    ];

    private const MOBILE_PHONE_PATTERN = [
        'AD' => null,
        'AL' => null,
        'AM' => null,
        'AT' => null,
        'BA' => null,
        'BE' => null,
        'BG' => null,
        'BY' => null,
        'CH' => null,
        'CY' => null,
        'CZ' => null,
        'DE' => null,
        'DK' => null,
        'EE' => null,
        'ES' => null,
        'FI' => null,
        self::FR => '((06|07)([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2}))',
        'GB' => null,
        'GI' => null,
        'GR' => null,
        'HR' => null,
        'HU' => null,
        'IE' => null,
        'IS' => null,
        'IT' => null,
        'LI' => null,
        'LT' => null,
        'LU' => null,
        'LV' => null,
        'MC' => null,
        'MD' => null,
        'ME' => null,
        'MK' => null,
        'MT' => null,
        'NL' => null,
        'NO' => null,
        'PL' => null,
        'PT' => null,
        'RO' => null,
        'RS' => null,
        'SE' => null,
        'SI' => null,
        'SK' => null,
        'SM' => null,
        'UA' => null,
        'VA' => null,
        'XK' => null,
    ];

    /**
     * @var string
     */
    private $_countryCode;

    /**
     * @var string
     */
    private $_normalizedPhoneNumber;

    /**
     * @var string
     */
    private $_originalPhoneNumber;

    /**
     * @var string
     */
    private $_internationalPhoneNumber;

    public function __construct(string $countryCode, ?string $phoneNumber)
    {
        $this->_setCountryCode($countryCode);
        $this->_setOriginalPhoneNumber($phoneNumber);
    }

    /**
     * Get the value of _originalPhoneNumber.
     */
    public function getOriginalPhoneNumber(): ?string
    {
        return $this->_originalPhoneNumber;
    }

    /**
     * Get the value of _internationalPhoneNumber.
     *
     * @param bool     $trunc    Indicates whether the returned number should be truncated
     * @param null|int $truncLen Specifies the number of characters to return
     */
    public function getInternationalPhoneNumber(bool $trunc = false, int $truncLen = null): ?string
    {
        if (
            !$this->_isCountryCodeSupported()
            || is_null(self::COUNTRY_PHONE_PREFIX[$this->_getCountryCode()])
            || is_null(self::INTERNATIONAL_PHONE_PATTERN[$this->_getCountryCode()])
        ) {
            return null;
        }

        if ($this->_isInternationalPhoneNumber()) {
            $this->_internationalPhoneNumber = $this->_getNormalizedPhoneNumber();
        } elseif ($this->_isRegularPhoneNumber()) {
            $this->_setInternationalPhoneNumber();
        }

        $len = strlen(self::PHONE_PREFIX.self::COUNTRY_PHONE_PREFIX[$this->_getCountryCode()]) + $truncLen - 1;

        return $trunc
            ? (is_null($truncLen) ? $this->_internationalPhoneNumber : substr($this->_internationalPhoneNumber, 0, $len))
            : $this->_internationalPhoneNumber;
    }

    private function _buildPattern(string $content)
    {
        return self::PATTERN_START.$content.self::PATTERN_END;
    }

    private function _setInternationalPhoneNumber(): self
    {
        switch ($this->_getCountryCode()) {
            case 'FR':
                $this->_internationalPhoneNumber = substr_replace($this->_getNormalizedPhoneNumber(), self::PHONE_PREFIX.self::COUNTRY_PHONE_PREFIX[$this->_getCountryCode()], 0, 1);

                break;

            default:
                // code...
                break;
        }

        return $this;
    }

    private function _isCountryCodeSupported(): bool
    {
        return array_key_exists($this->_getCountryCode(), self::COUNTRY_PHONE_PREFIX);
    }

    private function _isInternationalPhoneNumber(): bool
    {
        return preg_match(
            $this->_buildPattern('\\'.self::PHONE_PREFIX.self::COUNTRY_PHONE_PREFIX[$this->_getCountryCode()].self::INTERNATIONAL_PHONE_PATTERN[$this->_getCountryCode()]),
            $this->_getNormalizedPhoneNumber()
        );
    }

    private function _isRegularPhoneNumber(): bool
    {
        return preg_match(
            $this->_buildPattern(self::PHONE_PATTERN[$this->_getCountryCode()]),
            $this->_getNormalizedPhoneNumber()
        );
    }

    /**
     * Get the value of _countryCode.
     */
    private function _getCountryCode(): string
    {
        return $this->_countryCode;
    }

    /**
     * Set the value of _countryCode.
     */
    private function _setCountryCode(string $countryCode): self
    {
        $this->_countryCode = $countryCode;

        return $this;
    }

    /**
     * Get the value of _normalizedPhoneNumber.
     */
    private function _getNormalizedPhoneNumber(): string
    {
        return $this->_normalizedPhoneNumber;
    }

    /**
     * Set the value of _normalizedPhoneNumber.
     */
    private function _setNormalizedPhoneNumber(): self
    {
        $phoneNumber = $this->_originalPhoneNumber;

        // We add the phone prefix char before the phone number if it has not been set
        if (preg_match('/^'.self::COUNTRY_PHONE_PREFIX[$this->_getCountryCode()].'/', $phoneNumber)) {
            $phoneNumber = self::PHONE_PREFIX.$phoneNumber;
        }

        // Wre replace the '00 ' prefix by '+'
        if (preg_match('/^0{2} /', $phoneNumber)) {
            $phoneNumber = self::PHONE_PREFIX.substr($phoneNumber, 3);
        }

        $phoneNumber = str_replace(' ', '', $phoneNumber);

        $this->_normalizedPhoneNumber = $phoneNumber;

        return $this;
    }

    private function _setOriginalPhoneNumber(?string $phoneNumber): self
    {
        $this->_originalPhoneNumber = $phoneNumber;

        $this->_setNormalizedPhoneNumber();

        return $this;
    }
}
