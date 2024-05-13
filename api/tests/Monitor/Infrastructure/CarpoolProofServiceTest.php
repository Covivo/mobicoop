<?php

namespace App\Monitor\Infrastructure;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\CarpoolProofRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofServiceTest extends TestCase
{
    private $_carpoolProofService;
    private $_carpoolProofRepository;

    public function setUp(): void
    {
        $this->_carpoolProofRepository = $this->getMockBuilder(CarpoolProofRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_carpoolProofService = new CarpoolProofService($this->_carpoolProofRepository);
    }

    /**
     * @test
     */
    public function testGetCarpoolProofReturnsACarpoolProof()
    {
        $this->_carpoolProofRepository->method('findLastCarpoolProof')->willReturn(new CarpoolProof());
        $this->assertInstanceOf(CarpoolProof::class, $this->_carpoolProofService->getLastCarpoolProof());
    }

    /**
     * @test
     */
    public function testGetCarpoolProofReturnsNull()
    {
        $this->_carpoolProofRepository->method('findLastCarpoolProof')->willReturn(null);
        $this->assertNull($this->_carpoolProofService->getLastCarpoolProof());
    }
}
