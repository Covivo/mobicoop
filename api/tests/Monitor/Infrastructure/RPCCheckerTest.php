<?php

namespace App\Monitor\Infrastructure;

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
        $this->_rpcChecker = new RPCChecker(new CurlDataProvider(RPCChecker::RPC_URI));
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
