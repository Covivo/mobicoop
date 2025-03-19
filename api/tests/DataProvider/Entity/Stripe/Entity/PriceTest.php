<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class PriceTest extends TestCase
{
    private $_price;

    public function setUp(): void
    {
        $this->_price = new Price('EUR', 100, 'name');
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsAnArray()
    {
        $this->assertIsArray($this->_price->buildBody());
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsARightArray()
    {
        $result = '{"currency":"EUR","unit_amount":100,"product_data":{"name":"name"}}';
        $this->assertEquals($result, json_encode($this->_price->buildBody()));
    }
}
