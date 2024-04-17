<?php

namespace App\Monitor\Infrastructure;

use App\DataProvider\Entity\Response;
use App\DataProvider\Service\CurlDataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class RPCCheckerTest extends TestCase
{
    private $_rpcChecker;
    private $_curlDataProvider;

    public function setUp(): void
    {
        $this->_curlDataProvider = $this->getMockBuilder(CurlDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_rpcChecker = new RPCChecker($this->_curlDataProvider, '', '');
    }

    /**
     * @test
     */
    public function testCheckReturnsAString()
    {
        $this->assertIsString($this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsOk()
    {
        $this->_curlDataProvider->method('get')->willReturn(new Response(200, '[{"operator_journey_id":"TestMobicoop3_76785"}]'));
        $this->assertEquals('{"message":"OK"}', $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKo()
    {
        $this->_curlDataProvider->method('get')->willReturn(new Response(200));
        $this->assertEquals('{"message":"KO"}', $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKoForUncountableResponse()
    {
        $this->_curlDataProvider->method('get')->willReturn(new Response(200, 'this response is uncountable'));
        $this->assertEquals('{"message":"KO"}', $this->_rpcChecker->check());
    }
}
