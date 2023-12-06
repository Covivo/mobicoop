<?php

namespace App\Incentive\Resource;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class EecInstanceTest extends TestCase
{
    /**
     * @EecInstanceTest
     */
    private $_eecInstance;

    public function setUp(): void
    {
        $this->_eecInstance = new EecInstance();
    }

    /**
     * @test
     */
    public function getAvailableFalsy()
    {
        $this->assertFalse($this->_eecInstance->getAvailable());
        $this->assertFalse($this->_eecInstance->isAvailable());
    }

    /**
     * @test
     */
    public function getAvailableTruly()
    {
        $this->_eecInstance->setAvailable(true);
        $this->assertTrue($this->_eecInstance->getAvailable());
        $this->assertTrue($this->_eecInstance->isAvailable());
    }
}
