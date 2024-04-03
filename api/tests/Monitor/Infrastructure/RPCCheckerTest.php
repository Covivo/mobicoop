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

    public function setUp(): void
    {
        $curlDataProvider = $this->getMockBuilder(CurlDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $curlDataProvider->method('get')->willReturn(new Response(200));

        $this->_rpcChecker = new RPCChecker($curlDataProvider, '', '');
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
        $this->assertEquals('ok', $this->_rpcChecker->check());
    }
}
