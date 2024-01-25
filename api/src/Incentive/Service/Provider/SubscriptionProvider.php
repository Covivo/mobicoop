<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Payment\Entity\CarpoolItem;

class SubscriptionProvider
{
    public static function getLDSubscriptionFromCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceSubscription
    {
        return
            !is_null($carpoolItem->getCreditorUser())
            && !is_null($carpoolItem->getCreditorUser()->getLongDistanceSubscription())
                ? $carpoolItem->getCreditorUser()->getLongDistanceSubscription()
                : null;
    }
}
