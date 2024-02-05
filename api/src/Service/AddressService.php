<?php

namespace App\Service;

use App\Geography\Entity\Address;

class AddressService
{
    public const SUFFIX_REGEXP = '((bis|Bis|BIS)|(ter|Ter|TER))';

    public const NUMBER_REGEXP = '/^(\d+)|(\d+( )?('.self::SUFFIX_REGEXP.'?))/';
    public const NUMBER_SUFFIX_REGEXP = '/(\d+)( )?'.self::SUFFIX_REGEXP.'/';

    /**
     * @var Address
     */
    private $_address;

    public function __construct(Address $address)
    {
        $this->_address = $address;
    }

    public function getAddressWithStreetNumber(): ?string
    {
        switch (true) {
            case (is_null($this->_address->getHouseNumber()) || empty($this->_address->getHouseNumber())) && (is_null($this->_address->getStreetAddress()) || empty($this->_address->getStreetAddress())):
                $response = null;

                break;

            case (is_null($this->_address->getHouseNumber()) || empty($this->_address->getHouseNumber())) && (!is_null($this->_address->getStreetAddress()) && !empty($this->_address->getStreetAddress())):
                $response = $this->_address->getStreetAddress();

                break;

            case $this->hasHouseNumberSuffix() && $this->isNumberAlreadyInStreetAddress() && !$this->hasStreetAddressSuffix():
                $response = $this->replaceNumberByHouseNumberInStreetAddress();

                break;

            default:
                $response = $this->addHouseNumberToStreetAddress();

                break;
        }

        return $response;
    }

    /**
     * @return bool|int
     */
    public function isNumberAlreadyInStreetAddress()
    {
        return preg_match(self::NUMBER_REGEXP, $this->_address->getStreetAddress());
    }

    /**
     * @return bool|int
     */
    public function hasStreetAddressSuffix()
    {
        return preg_match(self::NUMBER_SUFFIX_REGEXP, $this->_address->getStreetAddress());
    }

    /**
     * @return bool|int
     */
    public function hasHouseNumberSuffix()
    {
        return preg_match(self::NUMBER_SUFFIX_REGEXP, $this->_address->getHouseNumber());
    }

    /**
     * @return null|array|string
     */
    public function replaceNumberByHouseNumberInStreetAddress(): string
    {
        return preg_replace(self::NUMBER_REGEXP, $this->_address->getHouseNumber(), $this->_address->getStreetAddress());
    }

    public function addHouseNumberToStreetAddress(Address $address = null): string
    {
        return $this->_address->getHouseNumber().' '.is_null($address) ? $address : $this->_address->getStreetAddress();
    }
}
