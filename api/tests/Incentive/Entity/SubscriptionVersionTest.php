<?php

namespace App\Incentive\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Tests\Mocks\CEEUserMock;
use App\Tests\Mocks\MobConnectSubscriptionResponseMock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
final class SubscriptionVersionTest extends TestCase
{
    /**
     * @var LongDistanceSubscription
     */
    private $_ldSubscription;

    /**
     * @var ShortDistanceSubscription
     */
    private $_sdSubscription;

    /**
     * @var SubscriptionVersion
     */
    private $_subscriptionVersion;

    /**
     * @var LongDistanceJourney
     */
    private $_ldJourney;

    /**
     * @var ShortDistanceJourney
     */
    private $_sdJourney;

    /**
     * @var \DateTime
     */
    private $_dateBefore;
    private $_dateAfter;
    private $_dateBeforeDeadline;
    private $_dateAfterDeadline;
    private $_deadlineDate;
    private $_dateBeforePublishedDeadline;
    private $_dateAfterPublishedDeadline;
    private $_publishedDeadlineDate;
    private $_now;

    public function setUp(): void
    {
        // Dates
        $this->_now = new \DateTime('now');
        $this->_deadlineDate = new \DateTime(SubscriptionVersion::EEC_VERSION_STANDARD_DEADLINE);

        $this->_publishedDeadlineDate = clone $this->_deadlineDate;
        $this->_publishedDeadlineDate = $this->_publishedDeadlineDate->add(new \DateInterval('P1M'));

        $this->_dateBefore = clone $this->_now;
        $this->_dateBefore = $this->_dateBefore->sub(new \DateInterval('P1M'));
        $this->_dateAfter = clone $this->_now;
        $this->_dateAfter = $this->_dateAfter->add(new \DateInterval('P1M'));

        $this->_dateBeforeDeadline = clone $this->_deadlineDate;
        $this->_dateBeforeDeadline = $this->_dateBeforeDeadline->sub(new \DateInterval('P1M'));
        $this->_dateAfterDeadline = clone $this->_deadlineDate;
        $this->_dateAfterDeadline = $this->_dateAfterDeadline->add(new \DateInterval('P1M'));

        $this->_dateBeforePublishedDeadline = clone $this->_publishedDeadlineDate;
        $this->_dateBeforePublishedDeadline = $this->_dateBeforePublishedDeadline->sub(new \DateInterval('P1M'));
        $this->_dateAfterPublishedDeadline = clone $this->_publishedDeadlineDate;
        $this->_dateAfterPublishedDeadline = $this->_dateAfterPublishedDeadline->add(new \DateInterval('P1M'));

        $user = CEEUserMock::getUser();
        $mobConnectSubscriptionResponse = MobConnectSubscriptionResponseMock::getResponse();

        $this->_ldSubscription = new LongDistanceSubscription($user, $mobConnectSubscriptionResponse);
        $this->_ldSubscription->setCreatedAt($this->_now);
        $this->_sdSubscription = new ShortDistanceSubscription($user, $mobConnectSubscriptionResponse);
        $this->_sdSubscription->setCreatedAt($this->_now);

        $this->_ldJourney = new LongDistanceJourney(new Proposal());
        $this->_ldJourney->setCreatedAt($this->_now);
        $this->_sdJourney = new ShortDistanceJourney(new CarpoolProof());
        $this->_sdJourney->setCreatedAt($this->_now);

        $this->_ldSubscription->addLongDistanceJourney($this->_ldJourney);
        $this->_ldSubscription->setCommitmentProofJourney($this->_ldJourney);

        $this->_sdSubscription->addShortDistanceJourney($this->_sdJourney);
        $this->_sdSubscription->setCommitmentProofJourney($this->_sdJourney);

        $this->_subscriptionVersion = new SubscriptionVersion($this->_ldSubscription);
    }

    /**
     * @test
     */
    public function isDateBeforeDeadline()
    {
        $this->assertFalse($this->_subscriptionVersion->isDateBeforeDeadline($this->_dateAfterDeadline));
        $this->assertTrue($this->_subscriptionVersion->isDateBeforeDeadline($this->_dateBeforeDeadline));
    }

    /**
     * @test
     */
    public function isDateAfterDeadline()
    {
        $this->assertFalse($this->_subscriptionVersion->isDateAfterDeadline($this->_dateBeforeDeadline));
        $this->assertTrue($this->_subscriptionVersion->isDateAfterDeadline($this->_dateAfterDeadline));
    }

    /**
     * @test
     */
    public function isDeadlinePassed()
    {
        // Please note, from January 1, 2024, it will be necessary to reverse the test functions
        $this->assertFalse($this->_subscriptionVersion->isDeadlinePassed());
        // $this->assertTrue($this->_subscriptionVersion->isDeadlinePassed());
    }

    /**
     * @test
     */
    public function isDateComing()
    {
        $this->assertFalse($this->_subscriptionVersion->isDateComing($this->_dateBefore));
        $this->assertTrue($this->_subscriptionVersion->isDateComing($this->_dateAfter));
    }

    /**
     * @test
     */
    public function isSubscriptionBeforeIncentiveDeadline()
    {
        $this->_subscriptionVersion->getCurrentSubscription()->setCreatedAt($this->_dateAfterDeadline);
        $this->assertFalse($this->_subscriptionVersion->isSubscriptionBeforeIncentiveDeadline());

        $this->_subscriptionVersion->getCurrentSubscription()->setCreatedAt($this->_dateBeforeDeadline);
        $this->assertTrue($this->_subscriptionVersion->isSubscriptionBeforeIncentiveDeadline());
    }

    /**
     * @test
     */
    public function isSubscriptionAfterIncentiveDeadline()
    {
        $this->_subscriptionVersion->getCurrentSubscription()->setCreatedAt($this->_dateBeforeDeadline);
        $this->assertFalse($this->_subscriptionVersion->isSubscriptionAfterIncentiveDeadline());

        $this->_subscriptionVersion->getCurrentSubscription()->setCreatedAt($this->_dateAfterDeadline);
        $this->assertTrue($this->_subscriptionVersion->isSubscriptionAfterIncentiveDeadline());
    }

    /**
     * @test
     */
    public function isCommitmentBeforeIncentiveDeadline()
    {
        $this->_subscriptionVersion->getCurrentCommitmentJourney()->setCreatedAt($this->_dateAfterDeadline);
        $this->assertFalse($this->_subscriptionVersion->isCommitmentBeforeIncentiveDeadline());

        $this->_subscriptionVersion->getCurrentCommitmentJourney()->setCreatedAt($this->_dateBeforeDeadline);
        $this->assertTrue($this->_subscriptionVersion->isCommitmentBeforeIncentiveDeadline());
    }

    /**
     * @test
     */
    public function isCommitmentAfterIncentiveDeadline()
    {
        $this->_subscriptionVersion->getCurrentCommitmentJourney()->setCreatedAt($this->_dateBeforeDeadline);
        $this->assertFalse($this->_subscriptionVersion->isCommitmentAfterIncentiveDeadline());

        $this->_subscriptionVersion->getCurrentCommitmentJourney()->setCreatedAt($this->_dateAfterDeadline);
        $this->assertTrue($this->_subscriptionVersion->isCommitmentAfterIncentiveDeadline());
    }

    /**
     * @test
     */
    public function isDateBeforePublishedDeadline()
    {
        $this->assertFalse($this->_subscriptionVersion->isDateBeforePublishedDeadline($this->_dateAfterPublishedDeadline));
        $this->assertTrue($this->_subscriptionVersion->isDateBeforePublishedDeadline($this->_dateBeforePublishedDeadline));
    }

    /**
     * @test
     */
    public function isDateAfterPublishedDeadline()
    {
        $this->assertFalse($this->_subscriptionVersion->isDateAfterPublishedDeadline($this->_dateBeforePublishedDeadline));
        $this->assertTrue($this->_subscriptionVersion->isDateAfterPublishedDeadline($this->_dateAfterPublishedDeadline));
    }

    /**
     * @test
     */
    public function getVersion()
    {
        $this->removeCommitmentJourney();
        $this->assertIsString($this->_subscriptionVersion->getVersion());
    }

    public function build() {}

    private function removeCommitmentJourney()
    {
        $this->_subscriptionVersion->getCurrentSubscription()->setCommitmentProofJourney(null);
        $this->_subscriptionVersion = new SubscriptionVersion($this->_subscriptionVersion->getCurrentSubscription());
    }

    private function addCommitmentJourney()
    {
        $this->_subscriptionVersion->getCurrentSubscription()->setCommitmentProofJourney($this->_ldJourney);
    }
}
