<?php

namespace App\Incentive\Entity;

use App\Tests\Mocks\CEESubscriptionDefinitionMock;
use App\Tests\Mocks\CEEUserMock;
use App\Tests\Mocks\MobConnectSubscriptionResponseMock;
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

    public function setUp(): void
    {
        $user = CEEUserMock::getUser();
        $subscriptionDefinition = CEESubscriptionDefinitionMock::getSubscriptionDefinition(ShortDistanceSubscription::SUBSCRIPTION_TYPE);
        $mobConnectSubscriptionResponse = MobConnectSubscriptionResponseMock::getResponse();

        $this->_subscription = new ShortDistanceSubscription($user, $mobConnectSubscriptionResponse, $subscriptionDefinition);
    }

    /**
     * @test
     */
    public function setVersion()
    {
        $this->_subscription->setVersion(0);
        $this->assertIsInt($this->_subscription->getVersion());
    }
}
