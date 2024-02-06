<?php

namespace App\Service;

use App\Geography\Entity\Address;

class AddressService
{
    public const BIS_SUFFIX = 'bis';
    public const TER_SUFFIX = 'ter';

    public const SUFFIX_REGEXP = '('.self::BIS_SUFFIX.'|'.self::TER_SUFFIX.')';

    public const NUMBER_REGEXP = '/^(\d+)|(\d+( )?('.self::SUFFIX_REGEXP.'?))/i';
    public const NUMBER_MANDATORY_SUFFIX_REGEXP = '/^\d+ ?(?:'.self::BIS_SUFFIX.'|'.self::TER_SUFFIX.')/i';
    public const NUMBER_OPTIONAL_SUFFIX_REGEXP = '/^\d+ ?(?:'.self::BIS_SUFFIX.'|'.self::TER_SUFFIX.')?/i';

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

            default:
                $response = $this->replaceNumberByHouseNumberInStreetAddress();

                break;
        }

        return $response;
    }

    public function isNumberAlreadyInStreetAddress(): bool
    {
        return preg_match(self::NUMBER_REGEXP, $this->_address->getStreetAddress()) > 0 ? true : false;
    }

    public function hasStreetAddressSuffix(): bool
    {
        return preg_match(self::NUMBER_OPTIONAL_SUFFIX_REGEXP, $this->_address->getStreetAddress()) > 0 ? true : false;
    }

    public function hasHouseNumberSuffix(): bool
    {
        return preg_match(self::NUMBER_MANDATORY_SUFFIX_REGEXP, $this->_address->getHouseNumber()) > 0 ? true : false;
    }

    /**
     * @return null|array|string
     */
    public function replaceNumberByHouseNumberInStreetAddress(): string
    {
        $this->_address->setStreetAddress(preg_replace(self::NUMBER_OPTIONAL_SUFFIX_REGEXP, '', $this->_address->getStreetAddress()));

        return $this->addHouseNumberToStreetAddress();
    }

    public function addHouseNumberToStreetAddress(Address $address = null): string
    {
        return trim($this->_address->getHouseNumber()).' '.trim(!is_null($address) ? $address->getStreetAddress() : $this->_address->getStreetAddress());
    }
}
