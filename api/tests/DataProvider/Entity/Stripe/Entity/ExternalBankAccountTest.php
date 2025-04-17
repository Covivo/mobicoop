<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ExternalBankAccountTest extends TestCase
{
    private $_externalBankAccount;

    public function setUp(): void
    {
        $stripeAccountId = '1234567890';
        $bankAccountTokenId = 'bt_1234567890';
        $this->_externalBankAccount = new ExternalBankAccount($stripeAccountId, $bankAccountTokenId);
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsAnArray()
    {
        $this->assertIsArray($this->_externalBankAccount->buildBody());
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsARightArray()
    {
        $result = '{"external_account":"bt_1234567890"}';
        $this->assertEquals($result, json_encode($this->_externalBankAccount->buildBody()));
    }
}
