<?php

namespace App\Incentive\Service\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Validator\SubscriptionValidator;
use App\Tests\Mocks\Incentive\LdSubscriptionMock;
use App\Tests\Mocks\Incentive\SdSubscriptionMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class SubscriptionValidatorTest extends TestCase
{
    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantBool($subscription)
    {
        $this->assertIsBool(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantFalse($subscription)
    {
        $this->assertFalse(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantTrue($subscription)
    {
        $this->assertTrue(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    // ---------------------------------------

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedBool($subscription)
    {
        $this->assertIsBool(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedFalse($subscription)
    {
        $this->assertFalse(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedTrue($subscription)
    {
        $this->assertTrue(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    public function dataSusbcriptionsNotEecCompliant()
    {
        return [
            [SdSubscriptionMock::getCommitedSubscription()],
            [LdSubscriptionMock::getCommitedSubscription()],
        ];
    }

    public function dataSusbcriptionsEecCompliant()
    {
        $sdSubscription = SdSubscriptionMock::getValidatedSubscription();

        $ldSubscription = LdSubscriptionMock::getValidatedSubscription();

        return [
            [$sdSubscription],
            [$ldSubscription],
        ];
    }
}
