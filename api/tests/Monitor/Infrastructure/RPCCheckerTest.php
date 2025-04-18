<?php

namespace App\Monitor\Infrastructure;

use App\Carpool\Entity\CarpoolProof;
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
    private $_carpoolProofService;
    private $_carpoolProof;

    public function setUp(): void
    {
        $this->_curlDataProvider = $this->getMockBuilder(CurlDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_carpoolProofService = $this->getMockBuilder(CarpoolProofService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_carpoolProof = new CarpoolProof();
        $this->_carpoolProof->setCreatedDate(new \DateTime('now'));

        $this->_rpcChecker = new RPCChecker($this->_curlDataProvider, $this->_carpoolProofService, 'http://rpcuri.io', 'RPCTOKEN', 'Mobicoop_');
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
        $this->_carpoolProofService->method('getLastCarpoolProof')->willReturn($this->_carpoolProof);
        $this->_curlDataProvider->method('get')->willReturn(new Response(200, '[{"operator_journey_id":"TestMobicoop3_76785"}]'));
        $this->assertEquals($this->_getOkReturn(), $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKo()
    {
        $this->_carpoolProofService->method('getLastCarpoolProof')->willReturn($this->_carpoolProof);
        $this->_curlDataProvider->method('get')->willReturn(new Response(200));
        $this->assertEquals($this->_getKoReturn(), $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKoForUncountableResponse()
    {
        $this->_carpoolProofService->method('getLastCarpoolProof')->willReturn($this->_carpoolProof);
        $this->_curlDataProvider->method('get')->willReturn(new Response(200, 'this response is uncountable'));
        $this->assertEquals($this->_getKoReturn(), $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsOkIfNoCarpoolProofGenerated()
    {
        $this->_carpoolProofService->method('getLastCarpoolProof')->willReturn(null);
        $this->assertEquals('{"message":"OK"}', $this->_rpcChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKoWhenNoCarpoofValidatedSinceLast()
    {
        $this->_carpoolProofService->method('getLastCarpoolProof')->willReturn($this->_carpoolProof);
        $this->_curlDataProvider->method('get')->willReturn(new Response(200));
        $this->assertEquals($this->_getKoReturn(), $this->_rpcChecker->check());
    }

    private function _getOkReturn(): string
    {
        $now = new \DateTime();

        return '{"message":"OK","lastCarpoolProofId":null,"minDate":"'.$now->format('Y-m-d').'T00:00:00Z"}';
    }

    private function _getKoReturn(): string
    {
        $now = new \DateTime();

        return '{"message":"KO","lastCarpoolProofId":null,"minDate":"'.$now->format('Y-m-d').'T00:00:00Z"}';
    }
}
