<?php

namespace App\Incentive\Service\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Validator\UserValidator;
use App\Tests\Mocks\Incentive\LdSubscriptionMock;
use App\Tests\Mocks\Incentive\SdSubscriptionMock;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class UserValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function hasUserEECSubscribedBool()
    {
        $this->assertIsBool(UserValidator::hasUserEECSubscribed(new User()));
    }

    /**
     * @test
     */
    public function hasUserEECSubscribedFalse()
    {
        $this->assertFalse(UserValidator::hasUserEECSubscribed(new User()));
    }

    /**
     * @dataProvider dataSusbcriptionsEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function hasUserEECSubscribedTrue($subscription)
    {
        $user = new User();

        if ($subscription instanceof LongDistanceSubscription) {
            $user->setLongDistanceSubscription($subscription);
        } else {
            $user->setShortDistanceSubscription($subscription);
        }

        $this->assertTrue(UserValidator::hasUserEECSubscribed($user));
    }

    public function dataSusbcriptionsEecCompliant()
    {
        $sdSubscription = SdSubscriptionMock::getNewSubscription();
        $ldSubscription = LdSubscriptionMock::getNewSubscription();

        return [
            [$sdSubscription],
            [$ldSubscription],
        ];
    }
}
