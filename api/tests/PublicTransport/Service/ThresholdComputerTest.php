<?php

namespace App\PublicTransport\Service;

use App\Action\Repository\LogRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ThresholdComputerTest extends TestCase
{
    private const DEFAULT_PROVIDER = 'provider';
    private const DEFAULT_THRESHOLD = 1;
    private const DEFAULT_GRANULARITY = 'day';
    private $_logRepository;
    private $_thresholdComputer;

    public function setUp(): void
    {
        $this->_logRepository = $this->getMockBuilder(LogRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_thresholdComputer = new ThresholdComputer($this->_logRepository, self::DEFAULT_PROVIDER, self::DEFAULT_THRESHOLD, self::DEFAULT_GRANULARITY);
    }

    // @test
    public function testIsReachedReturnsBoolean()
    {
        $this->_logRepository->method('findByPtProviderAndDate')->willReturn([]);
        $this->assertIsBool($this->_thresholdComputer->isReached());
    }

    // @test
    public function testIsReachedReturnsFalseOnEmptyLogsArray()
    {
        $this->_logRepository->method('findByPtProviderAndDate')->willReturn([]);
        $this->assertFalse($this->_thresholdComputer->isReached());
    }

    // @test
    public function testIsReachedReturnsFalseWhenThresholdIsZero()
    {
        $thresholdComputer = new ThresholdComputer($this->_logRepository, self::DEFAULT_PROVIDER, 0, self::DEFAULT_GRANULARITY);
        $this->assertFalse($thresholdComputer->isReached());
    }

    public function testIsReachedReturnsTrueWhenThresholdIsReached()
    {
        $this->_logRepository->method('findByPtProviderAndDate')->willReturn([1, 2, 3]);
        $this->assertTrue($this->_thresholdComputer->isReached());
    }
}
