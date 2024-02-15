<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Payment\Entity\CarpoolItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionProvider
{
    /**
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public static function getSubscriptionFromType(EntityManagerInterface $em, string $subscriptionType, int $subscriptionId)
    {
        $repository = Subscription::TYPE_LONG === $subscriptionType
            ? $em->getRepository(LongDistanceSubscription::class)
            : $em->getRepository(ShortDistanceSubscription::class);

        $subscription = $repository->find($subscriptionId);

        if (is_null($subscription)) {
            throw new NotFoundHttpException('The subscription was not found');
        }

        return $subscription;
    }

    public static function getLDSubscriptionFromCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceSubscription
    {
        return
            !is_null($carpoolItem->getCreditorUser())
            && !is_null($carpoolItem->getCreditorUser()->getLongDistanceSubscription())
                ? $carpoolItem->getCreditorUser()->getLongDistanceSubscription()
                : null;
    }

    /**
     * @param LongDistanceSubscription[]|ShortDistanceSubscription[] $subscriptions
     */
    public static function getSubscriptionsCanBeReset($subscriptions, bool $resetOnly = false): array
    {
        return array_values(array_filter($subscriptions, function ($subscription) use ($resetOnly) {
            if ($resetOnly) {
                return count($subscription->getJourneys()) <= 1;    // There is only the commitment journey
            }

            return count($subscription->getJourneys()) > 1;         // It's not just the commitment journey
        }));
    }
}
