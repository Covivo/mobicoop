<?php

namespace App\Tests\Mocks\Payment;

use App\Payment\Entity\CarpoolPayment;

class EecCarpoolPayment
{
    public static function getCarpoolPayment(): CarpoolPayment
    {
        $carpoolPayment = new CarpoolPayment();
        $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $carpoolPayment->setTransactionId(rand());

        return $carpoolPayment;
    }
}
