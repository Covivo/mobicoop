<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class BankAccountTokenTest extends TestCase
{
    private $_bankAccountToken;

    public function setUp(): void
    {
        $externalBankAccountNumber = '1234567890';
        $country = 'FR';
        $accountHolderName = 'John Doe';
        $currency = 'EUR';
        $this->_bankAccountToken = new BankAccountToken($externalBankAccountNumber, $country, $accountHolderName, $currency);
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsAnArray()
    {
        $this->assertIsArray($this->_bankAccountToken->buildBody());
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsARightArray()
    {
        $result = '{"bank_account":{"account_number":"1234567890","country":"FR","account_holder_name":"John Doe","account_holder_type":"individual","currency":"EUR"}}';
        $this->assertEquals($result, json_encode($this->_bankAccountToken->buildBody()));
    }
}
