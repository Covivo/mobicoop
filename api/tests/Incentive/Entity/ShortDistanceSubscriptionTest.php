<?php

namespace App\Incentive\Entity;

use App\Tests\Mocks\CEEUserMock;
use App\Tests\Mocks\MobConnectSubscriptionResponseMock;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ShortDistanceSubscriptionTest extends TestCase
{
    /**
     * @var ShortDistanceSubscription
     */
    private $_subscription;

    /**
     * @var User
     */
    private $_user;

    public function setUp(): void
    {
        $this->_user = CEEUserMock::getUser();
        $mobConnectSubscriptionResponse = MobConnectSubscriptionResponseMock::getResponse();

        $this->_subscription = new ShortDistanceSubscription($this->_user, $mobConnectSubscriptionResponse);
    }

    /**
     * @test
     */
    public function setVersion()
    {
        $this->_subscription->setCreatedAt(new \DateTime('2023-11-14'));
        $this->_subscription->setVersion();

        $this->assertIsInt($this->_subscription->getVersion());

        $this->_subscription->setCreatedAt(new \DateTime('2023-01-02'));
        $this->_subscription->setVersion();
        $this->assertIsInt($this->_subscription->getVersion());

        // Tester les versions
    }
}
