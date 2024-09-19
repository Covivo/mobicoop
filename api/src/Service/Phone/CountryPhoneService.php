<?php

namespace App\Service\Phone;

class CountryPhoneService
{
    public const AD = 'AD';
    public const AL = 'AL';
    public const AM = 'AM';
    public const AT = 'AT';
    public const BA = 'BA';
    public const BE = 'BE';
    public const BG = 'BG';
    public const BY = 'BY';
    public const CH = 'CH';
    public const CY = 'CY';
    public const CZ = 'CZ';
    public const DE = 'DE';
    public const DK = 'DK';
    public const EE = 'EE';
    public const ES = 'ES';
    public const FI = 'FI';
    public const FR = 'FR';
    public const GB = 'GB';
    public const GI = 'GI';
    public const GR = 'GR';
    public const HR = 'HR';
    public const HU = 'HU';
    public const IE = 'IE';
    public const IS = 'IS';
    public const IT = 'IT';
    public const LI = 'LI';
    public const LT = 'LT';
    public const LU = 'LU';
    public const LV = 'LV';
    public const MC = 'MC';
    public const MD = 'MD';
    public const ME = 'ME';
    public const MK = 'MK';
    public const MT = 'MT';
    public const NL = 'NL';
    public const NO = 'NO';
    public const PL = 'PL';
    public const PT = 'PT';
    public const RO = 'RO';
    public const RS = 'RS';
    public const SE = 'SE';
    public const SI = 'SI';
    public const SK = 'SK';
    public const SM = 'SM';
    public const UA = 'UA';
    public const VA = 'VA';
    public const XK = 'XK';
    public const GP = 'GP';
    public const MQ = 'MQ';
    public const NC = 'NC';
    public const GY = 'GY';
    public const PH = 'PH';
    public const RE = 'RE';
    public const YT = 'YT';

    public const COUNTRY_PHONE_PREFIX = [
        'GR' => 30,
        'NL' => 31,
        'BE' => 32,
        self::FR => 33,
        'ES' => 34,
        'HU' => 36,
        'IT' => 39,
        'RO' => 40,
        'CH' => 41,
        'AT' => 43,
        'GB' => 44,
        'DK' => 45,
        'NO' => 47,
        'PL' => 48,
        'DE' => 49,
        'SE' => 46,
        'PH' => 63,
        'GI' => 350,
        'PT' => 351,
        'LU' => 352,
        'IE' => 353,
        'IS' => 354,
        'AL' => 355,
        'MT' => 356,
        'CY' => 357,
        'FI' => 358,
        'BG' => 359,
        'LT' => 370,
        'LV' => 371,
        'EE' => 372,
        'MD' => 373,
        'AM' => 374,
        'BY' => 375,
        'AD' => 376,
        'MC' => 377,
        'SM' => 378,
        'VA' => 379,
        'UA' => 380,
        'RS' => 381,
        'ME' => 382,
        'XK' => 383,
        'HR' => 385,
        'SI' => 386,
        'BA' => 387,
        'MK' => 389,
        'CZ' => 420,
        'SK' => 421,
        'LI' => 423,
        'GP' => 590,
        'GY' => 594,
        'MQ' => 596,
        'NC' => 687,
        'RE' => 262,
        'YT' => 262,
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
        'GP' => null,
        'MQ' => null,
        'GY' => null,
        'PH' => null,
        'NC' => null,
        'RE' => null,
        'YT' => null,
    ];

    /**
     * @var string
     */
    private $_originalPhoneNumber;

    /**
     * @var string
     */
    private $_phoneNumber;

    /**
     * @var null|string
     */
    private $_code;

    /**
     * @var null|string
     */
    private $_indicative;

    public function __construct(string $phoneNumber)
    {
        $this->_setOriginalPhoneNumber($phoneNumber);

        $this->_build();
    }

    /**
     * Get the value of _code.
     */
    public function getCode(): ?string
    {
        return $this->_code;
    }

    /**
     * Get the value of _indicative.
     */
    public function getIndicative(): ?string
    {
        return $this->_indicative;
    }

    /**
     * Get the value of _phoneNumber.
     */
    public function getPhoneNumber(): string
    {
        return $this->_phoneNumber;
    }

    /**
     * Set the value of _phoneNumber.
     */
    private function _setPhoneNumber(int $offset = 0): self
    {
        $this->_phoneNumber = substr($this->_phoneNumber, $offset);

        return $this;
    }

    private function _build()
    {
        $this->_removePrefix();
        $this->_removeCountryExitCode();
        $this->_defineCountry();
    }

    private function _defineCountry()
    {
        foreach (self::COUNTRY_PHONE_PREFIX as $key => $code) {
            // Define by country code
            if (preg_match('/^'.$code.'/', $this->_phoneNumber)) {
                $this->_setCode($key);
                $this->_setIndicative($code);

                $this->_setPhoneNumber(strlen($this->_indicative));
            }
        }

        // Define by default for France
        if (preg_match('/^'.self::PHONE_PATTERN[self::FR].'$/', $this->_phoneNumber)) {
            $this->_setCode(self::FR);
            $this->_setIndicative(self::COUNTRY_PHONE_PREFIX[self::FR]);

            $this->_setPhoneNumber(1);
        }
    }

    /**
     * Set the value of _code.
     */
    private function _setCode(string $code): self
    {
        $this->_code = $code;

        return $this;
    }

    private function _removePrefix(): string
    {
        $this->_phoneNumber = preg_match('/^\\'.PhoneService::PHONE_PREFIX.'/', $this->_originalPhoneNumber)
            ? substr($this->_originalPhoneNumber, 1)
            : $this->_originalPhoneNumber;

        return $this->_phoneNumber;
    }

    private function _removeCountryExitCode(): string
    {
        $this->_phoneNumber = preg_match('/^0{2}/', $this->_phoneNumber)
            ? substr($this->_originalPhoneNumber, 2)
            : $this->_phoneNumber;

        return $this->_phoneNumber;
    }

    /**
     * Set the value of _originalPhoneNumber.
     */
    private function _setOriginalPhoneNumber(string $originalPhoneNumber): self
    {
        $this->_originalPhoneNumber = $originalPhoneNumber;

        return $this;
    }

    /**
     * Set the value of _indicative.
     */
    private function _setIndicative(string $indicative)
    {
        $this->_indicative = $indicative;

        return $this;
    }
}
