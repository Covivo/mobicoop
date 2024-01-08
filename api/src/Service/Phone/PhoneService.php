<?php

namespace App\Service\Phone;

class PhoneService
{
    public const PHONE_PREFIX = '+';

    /**
     * @var string
     */
    private $_originalPhoneNumber;

    /**
     * @var CountryPhoneService
     */
    private $_country;

    /**
     * @var string
     */
    private $_prefix = self::PHONE_PREFIX;

    public function __construct(string $phoneNumber)
    {
        $this->_setOriginalPhoneNumber($this->_sanitize($phoneNumber));
        $this->_setCountry();
    }

    public function getPhoneNumber() {}

    public function getInternationalPhoneNumber(): string
    {
        return $this->_prefix.$this->_country->getIndicative().$this->_country->getPhoneNumber();
    }

    public function getTruncatedInternationalPhoneNumber(?int $length = null)
    {
        return substr($this->getInternationalPhoneNumber(), 0, $length + 2);
    }

    private function _sanitize(string $phoneNumber): string
    {
        return trim(preg_replace('/[^A-Za-z0-9]/', '', $phoneNumber));
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
     * Set the value of _country.
     */
    private function _setCountry(): self
    {
        $this->_country = new CountryPhoneService($this->_originalPhoneNumber);

        return $this;
    }
}
