<?php

namespace App\Incentive\Service\Provider;

use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;

class CarpoolItemProvider
{
    /**
     * @return CarpoolItem[]
     */
    public static function getCarpoolItemFromCarpoolPayment(CarpoolPayment $carpoolPayment)
    {
        return array_filter($carpoolPayment->getCarpoolItems(), function (CarpoolItem $carpoolItem) {
            $driver = $carpoolItem->getCreditorUser();

            return
                !is_null($driver)
                && !is_null($driver->getMobConnectAuth())
                && $carpoolItem->isEECompliant();
        });
    }
}
