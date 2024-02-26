<?php

namespace App\Service\Date;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class DateServiceTest extends TestCase
{
    public const DEFAULT_STRING_DATETIME = '2024-01-01 00:00';

    /**
     * @var \DateTime
     */
    private $_defaultDatetime;

    public function setUp(): void
    {
        $this->_defaultDatetime = new \DateTime(self::DEFAULT_STRING_DATETIME);
    }

    /**
     * @test
     */
    public function getNow()
    {
        $nowAsString = (new \DateTime())->add(new \DateInterval('PT1H'))->format('Y-m-d H:i');

        $this->assertSame($nowAsString, DateService::getNow()->format('Y-m-d H:i'));
    }

    /**
     * @test
     */
    public function getNowWithTimeDiff()
    {
        $now = (new \DateTime())->add(new \DateInterval('PT1H'));

        $this->assertSame($now->format('Y-m-d H:i'), DateService::getNow()->format('Y-m-d H:i'));
    }

    // ------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function addTimediffBetweenServerAndUtc()
    {
        $this->assertEquals('2024-01-01 01:00', DateService::addTimediffBetweenServerAndUtc($this->_defaultDatetime)->format('Y-m-d H:i'));
    }
}
