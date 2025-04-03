<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class TransferTest extends TestCase
{
    private $_transfer;

    public function setUp(): void
    {
        $this->_transfer = new Transfer('EUR', 100, 'destination', 'group');
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsAnArray()
    {
        $this->assertIsArray($this->_transfer->buildBody());
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsARightArray()
    {
        $result = '{"amount":100,"currency":"EUR","destination":"destination","transfer_group":"group"}';
        $this->assertEquals($result, json_encode($this->_transfer->buildBody()));
    }
}
