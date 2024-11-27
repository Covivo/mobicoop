<?php

namespace App\Monitor\Infrastructure;

use App\Communication\Repository\NotifiedRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class NotificationAbuseCheckerTest extends TestCase
{
    public const OK = ['message' => 'OK'];
    public const KO = ['message' => 'KO'];

    private const ABUSES = [[
        'user_id' => 34,
        'notification_id' => 33,
        'nb_notif' => 6,
        'max_emmitted_per_day' => 5,
        'nb_blocked' => 6,
    ]];
    private const NO_ABUSES = [];
    private $_notificationAbuseChecker;
    private $_notifiedRepository;

    public function setUp(): void
    {
        $this->_notifiedRepository = $this->getMockBuilder(NotifiedRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_notificationAbuseChecker = new NotificationAbuseChecker($this->_notifiedRepository);
    }

    /**
     * @test
     */
    public function testCheckOkReturnsAString()
    {
        $this->_notifiedRepository->method('findNotifiedAbuses')->willReturn(self::NO_ABUSES);
        $this->assertIsString($this->_notificationAbuseChecker->check());
    }

    /**
     * @test
     */
    public function testCheckKoReturnsAString()
    {
        $this->_notifiedRepository->method('findNotifiedAbuses')->willReturn(self::ABUSES);
        $this->assertIsString($this->_notificationAbuseChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsOk()
    {
        $this->_notifiedRepository->method('findNotifiedAbuses')->willReturn(self::NO_ABUSES);
        $this->assertEquals($this->_getOkReturn(), $this->_notificationAbuseChecker->check());
    }

    /**
     * @test
     */
    public function testCheckReturnsKo()
    {
        $this->_notifiedRepository->method('findNotifiedAbuses')->willReturn(self::ABUSES);
        $this->assertEquals($this->_getKoReturn(), $this->_notificationAbuseChecker->check());
    }

    private function _getOkReturn(): string
    {
        return json_encode(self::OK, JSON_UNESCAPED_SLASHES);
    }

    private function _getKoReturn(): string
    {
        $return = self::KO;
        $return['details'] = self::ABUSES;

        return json_encode($return, JSON_UNESCAPED_SLASHES);
    }
}
