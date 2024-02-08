<?php

namespace App\Tests\Mocks\Payment;

use App\Carpool\Entity\Ask;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;

class CarpoolItemMock
{
    public static function getCarpoolItemEec(CarpoolPayment $carpoolPayment, Ask $ask): CarpoolItem
    {
        $carpoolItem = new CarpoolItem();
        $carpoolPayment->addCarpoolItem($carpoolItem);
        $carpoolItem->addCarpoolPayment($carpoolPayment);
        $carpoolItem->setAsk($ask);
        $carpoolItem->setItemDate(new \DateTime());

        return $carpoolItem;
    }
}
