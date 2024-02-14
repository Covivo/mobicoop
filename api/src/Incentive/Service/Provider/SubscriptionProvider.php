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
}
