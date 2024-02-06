<?php

namespace App\Service;

use App\Geography\Entity\Address;
use App\Tests\Mocks\Geography\AddressMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class AddressServiceTest extends TestCase
{
    /**
     * @var Address
     */
    private $_address;

    /**
     * @var AddressService
     */
    private $_addressService;

    public function setUp(): void
    {
        $this->_address = AddressMock::getAddress();
        $this->_addressService = new AddressService($this->_address);
    }

    /**
     * @test
     */
    public function getAddressWithStreetNumberNull()
    {
        $this->assertNull($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('');
        $this->assertNull($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setStreetAddress('');
        $this->assertNull($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('');
        $this->_address->setStreetAddress('');
        $this->assertNull($this->_addressService->getAddressWithStreetNumber());
    }

    /**
     * @test
     */
    public function getAddressWithStreetNumberString()
    {
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertIsString($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertIsString($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertIsString($this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('9 bis');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertIsString($this->_addressService->getAddressWithStreetNumber());
    }

    /**
     * @test
     */
    public function getAddressWithStreetNumberSameValue()
    {
        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertSame('9 rue de la Monnaie', $this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('9 bis');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertSame('9 bis rue de la Monnaie', $this->_addressService->getAddressWithStreetNumber());

        $this->_address->setHouseNumber('9 TER');
        $this->_address->setStreetAddress('5 rue de la Monnaie');
        $this->assertSame('9 TER rue de la Monnaie', $this->_addressService->getAddressWithStreetNumber());
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function isNumberAlreadyInStreetAddressBool()
    {
        $this->_address->setStreetAddress('5 rue de la Monnaie');

        $this->assertIsBool($this->_addressService->isNumberAlreadyInStreetAddress());
    }

    /**
     * @test
     */
    public function isNumberAlreadyInStreetAddressFalse()
    {
        $this->_address->setStreetAddress('rue de la Monnaie');

        $this->assertFalse($this->_addressService->isNumberAlreadyInStreetAddress());
    }

    /**
     * @test
     */
    public function isNumberAlreadyInStreetAddressTrue()
    {
        $this->_address->setStreetAddress('5 rue de la Monnaie');

        $this->assertTrue($this->_addressService->isNumberAlreadyInStreetAddress());
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function hasStreetAddressSuffixBool()
    {
        $this->_address->setStreetAddress('5ter rue de la Monnaie');

        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());
    }

    /**
     * @test
     */
    public function hasStreetAddressSuffixFalse()
    {
        $this->_address->setStreetAddress('5 rue de la Monnaie');

        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());
    }

    /**
     * @test
     */
    public function hasStreetAddressSuffixTrue()
    {
        $this->_address->setStreetAddress('5bis rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());

        $this->_address->setStreetAddress('5Bis rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());

        $this->_address->setStreetAddress('5BIS rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());

        $this->_address->setStreetAddress('5ter rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());

        $this->_address->setStreetAddress('5Ter rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());

        $this->_address->setStreetAddress('5TER rue de la Monnaie');
        $this->assertIsBool($this->_addressService->hasStreetAddressSuffix());
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function hasHouseNumberSuffixBool()
    {
        $this->_address->setHouseNumber('5bis');
        $this->assertIsBool($this->_addressService->hasHouseNUmberSuffix());
    }

    /**
     * @test
     */
    public function hasHouseNumberSuffixFalse()
    {
        $this->_address->setHouseNumber('548');

        $this->assertFalse($this->_addressService->hasHouseNUmberSuffix());
    }

    /**
     * @test
     */
    public function hasHouseNumberSuffixTrue()
    {
        $this->_address->setHouseNumber('5bis');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());

        $this->_address->setHouseNumber('5Bis');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());

        $this->_address->setHouseNumber('5BIS');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());

        $this->_address->setHouseNumber('5ter');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());

        $this->_address->setHouseNumber('5Ter');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());

        $this->_address->setHouseNumber('5TER');
        $this->assertTrue($this->_addressService->hasHouseNUmberSuffix());
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function replaceNumberByHouseNumberInStreetAddressString()
    {
        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('5 rue de la Monnaie');

        $this->assertIsString($this->_addressService->replaceNumberByHouseNumberInStreetAddress());
    }

    /**
     * @test
     */
    public function replaceNumberByHouseNumberInStreetAddressSameValue()
    {
        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('5 rue de la Monnaie');

        $this->assertSame('9 rue de la Monnaie', $this->_addressService->replaceNumberByHouseNumberInStreetAddress());

        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('5bis rue de la Monnaie');

        $this->assertSame('9 rue de la Monnaie', $this->_addressService->replaceNumberByHouseNumberInStreetAddress());
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function addHouseNumberToStreetAddressString()
    {
        $this->_address->setHouseNumber('5');
        $this->_address->setStreetAddress('rue de la Monnaie');

        $this->assertIsString($this->_addressService->addHouseNumberToStreetAddress());
    }

    /**
     * @test
     */
    public function addHouseNumberToStreetAddressSameValue()
    {
        $this->_address->setHouseNumber('9');
        $this->_address->setStreetAddress('rue de la Monnaie');

        $this->assertSame('9 rue de la Monnaie', $this->_addressService->addHouseNumberToStreetAddress());

        $this->_address->setStreetAddress('boulevard Louis Sicre');
        $this->assertSame('9 boulevard Louis Sicre', $this->_addressService->addHouseNumberToStreetAddress($this->_address));
    }
}
