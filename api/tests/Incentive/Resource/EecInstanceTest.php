<?php

namespace App\Incentive\Resource;

use App\Tests\Mocks\Incentive\EecInstanceMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class EecInstanceTest extends TestCase
{
    /**
     * @test
     */
    public function getAvailableFalsy()
    {
        $this->assertFalse(EecInstanceMock::getEecInstanceUnavailable1()->getAvailable());
        $this->assertFalse(EecInstanceMock::getEecInstanceUnavailable1()->isAvailable());

        $this->assertFalse(EecInstanceMock::getEecInstanceUnavailable2()->getAvailable());
        $this->assertFalse(EecInstanceMock::getEecInstanceUnavailable2()->isAvailable());
    }

    /**
     * @test
     */
    public function getAvailableTruly()
    {
        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable1()->getAvailable());
        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable1()->isAvailable());

        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable2()->getAvailable());
        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable2()->isAvailable());

        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable3()->getAvailable());
        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable3()->isAvailable());

        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable4()->getAvailable());
        $this->assertTrue(EecInstanceMock::getEecInstanceAvailable4()->isAvailable());
    }
}
