<?php

namespace App\Service\Phone;

class PhoneService
{
    public const PHONE_PREFIX = '+';
    public const DEFAULT_INDICATIVE = '33';

    /**
     * @var string
     */
    private $_originalPhoneNumber;

    /**
     * @var string
     */
    private $_phoneCode;

    /**
     * @var CountryPhoneService
     */
    private $_country;

    /**
     * @var string
     */
    private $_prefix = self::PHONE_PREFIX;

    public function __construct(string $phoneNumber, ?string $phoneCode = self::DEFAULT_INDICATIVE)
    {
        if (is_null($phoneCode)) {
            $phoneCode = self::DEFAULT_INDICATIVE;
        }
        $this->_setOriginalPhoneNumber($this->_sanitize($phoneNumber));
        $this->_setPhoneCode($phoneCode);
        $this->_setCountry();
    }

    public function getPhoneNumber() {}

    public function getInternationalPhoneNumber(): string
    {
        $indicative = $this->_phoneCode;
        if ('' !== trim($this->_country->getPhoneNumber()) && '+' == substr($this->_country->getPhoneNumber(), 0, 1)) {
            $indicative = '';
        }

        return $this->_prefix.$indicative.$this->_country->getPhoneNumber();
    }

    public function getTruncatedInternationalPhoneNumber(?int $length = null)
    {
        return substr($this->getInternationalPhoneNumber(), 0, $length + strlen($this->_phoneCode));
    }

    private function _sanitize(string $phoneNumber): string
    {
        if ('+' == substr($phoneNumber, 0, 1)) {
            return $phoneNumber;
        }

        return trim(preg_replace('/[^A-Za-z0-9]/', '', $phoneNumber));
    }

    private function _setOriginalPhoneNumber(string $originalPhoneNumber): self
    {
        if ('' !== trim($originalPhoneNumber)) {
            ('0' !== substr($originalPhoneNumber, 0, 1) && '+' !== substr($originalPhoneNumber, 0, 1)) ? $this->_originalPhoneNumber = '0'.$originalPhoneNumber : $this->_originalPhoneNumber = $originalPhoneNumber;
        }

        return $this;
    }

    private function _setPhoneCode(string $phoneCode): self
    {
        $this->_phoneCode = $phoneCode;

        return $this;
    }

    private function _setCountry(): self
    {
        $this->_country = new CountryPhoneService($this->_originalPhoneNumber);

        return $this;
    }
}
