<?php

namespace App\Incentive\Service\Manager;

use App\Tests\Mocks\CEEUserMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class SubscriptionManagerTest extends TestCase
{
    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    public function setUp(): void
    {
        $this->_subscriptionManager = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateTimeStampTokens'])
            ->getMock()
        ;
    }

    /**
     * @test
     */
    public function updateTimestampTokens()
    {
        $user = CEEUserMock::getUser();

        $this->assertInstanceOf('App\User\Entity\User', $this->_subscriptionManager->updateTimestampTokens($user));
    }
}
