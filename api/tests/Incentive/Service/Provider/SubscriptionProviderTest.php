<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Payment\Entity\CarpoolItem;
use App\Tests\Mocks\Incentive\LdSubscriptionMock;
use App\Tests\Mocks\Incentive\SdSubscriptionMock;
use App\Tests\Mocks\User\UserMock;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class SubscriptionProviderTest extends TestCase
{
    /**
     * @dataProvider dataSubscriptionFromType
     *
     * @test
     */
    public function getSubscriptionFromTypeNotFoundException(string $subscriptionType, int $susbcriptionId)
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())
            ->method('find')
            ->willReturn(null)
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository)
        ;

        $this->expectException(NotFoundHttpException::class);
        SubscriptionProvider::getSubscriptionFromType($entityManager, $subscriptionType, $susbcriptionId);
    }

    /**
     * @dataProvider dataSubscription
     *
     * @test
     *
     * @param mixed $subscription
     * @param mixed $expected
     */
    public function getSubscriptionFromTypeSubscription($subscription, $expected)
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())
            ->method('find')
            ->willReturn($subscription)
        ;

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository)
        ;

        $this->assertInstanceOf($expected, SubscriptionProvider::getSubscriptionFromType(
            $entityManager,
            '',
            9999999
        ));
    }

    // ------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function getLDSubscriptionFromCarpoolItemNull()
    {
        $this->assertNull(SubscriptionProvider::getLDSubscriptionFromCarpoolItem(new CarpoolItem()));
    }

    /**
     * @test
     */
    public function getLDSubscriptionFromCarpoolItemLdSubscription()
    {
        $ldSubscription = LdSubscriptionMock::getNewSubscription();
        $user = UserMock::getUserEec();
        $user->setLongDistanceSubscription($ldSubscription);
        $carpoolItem = new CarpoolItem();
        $carpoolItem->setCreditorUser($user);

        $this->assertInstanceOf(LongDistanceSubscription::class, SubscriptionProvider::getLDSubscriptionFromCarpoolItem($carpoolItem));
    }

    // ------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function getSubscriptionsCanBeResetArray()
    {
        $this->assertIsArray(SubscriptionProvider::getSubscriptionsCanBeReset([]));
        $this->assertIsArray(SubscriptionProvider::getSubscriptionsCanBeReset([], true));
    }

    /**
     * @dataProvider dataCanBeReset
     *
     * @test
     */
    public function getSubscriptionsCanBeResetSame(array $subscriptions)
    {
        $this->assertSame([$subscriptions[0], $subscriptions[1]], SubscriptionProvider::getSubscriptionsCanBeReset($subscriptions, true));
        $this->assertSame([$subscriptions[2]], SubscriptionProvider::getSubscriptionsCanBeReset($subscriptions));
    }

    // dataProviders ----------------------------------------------------------------------------------------------------------------------

    public function dataSubscriptionFromType(): array
    {
        return [
            ['short', 99999999],
            ['long', 99999999],
        ];
    }

    public function dataSubscription(): array
    {
        return [
            [SdSubscriptionMock::getCommitedSubscription(), ShortDistanceSubscription::class],
            [LdSubscriptionMock::getCommitedSubscription(), LongDistanceSubscription::class],
        ];
    }

    public function dataCanBeReset(): array
    {
        $sdSubscription1 = SdSubscriptionMock::getCommitedSubscription();
        $sdSubscription2 = SdSubscriptionMock::getCommitedSubscription();
        $sdSubscription3 = SdSubscriptionMock::getCompleteSubscription();

        $ldSubscription1 = LdSubscriptionMock::getCommitedSubscription();
        $ldSubscription2 = LdSubscriptionMock::getCommitedSubscription();
        $ldSubscription3 = LdSubscriptionMock::getCompleteSubscription();

        return [
            [[$sdSubscription1, $sdSubscription2, $sdSubscription3]],
            [[$ldSubscription1, $ldSubscription2, $ldSubscription3]],
        ];
    }
}
