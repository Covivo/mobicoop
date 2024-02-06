<?php

namespace App\Tests\Mocks\Payment;

use App\Payment\Entity\CarpoolPayment;

class CarpoolPaymentMock
{
    public static function getCarpoolPaymentEec(): CarpoolPayment
    {
        $carpoolPayment = new CarpoolPayment();
        $carpoolPayment->setStatus(CarpoolPayment::STATUS_SUCCESS);
        $carpoolPayment->setTransactionId(rand());

        return $carpoolPayment;
    }
}
