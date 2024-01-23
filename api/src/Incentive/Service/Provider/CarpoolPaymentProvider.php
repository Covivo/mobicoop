<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceJourney;
use App\Payment\Entity\CarpoolPayment;

class CarpoolPaymentProvider
{
    public static function getCarpoolPaymentFromLdJourney(LongDistanceJourney $journey): ?CarpoolPayment
    {
        $carpoolPayment = $journey->getCarpoolPayment();

        if (!is_null($carpoolPayment)) {
            return $carpoolPayment;
        }

        $carpoolPayment =
            !is_null($journey->getCarpoolPayment())
            ? $journey->getCarpoolPayment()
            : (
                !is_null($journey->getCarpoolItem())
                ? $journey->getCarpoolItem()->getSuccessfullPayment()
                : null
            );
    }
}
