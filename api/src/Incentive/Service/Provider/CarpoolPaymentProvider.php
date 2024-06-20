<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Validator\CarpoolPaymentValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;

class CarpoolPaymentProvider
{
    public static function getCarpoolPaymentFromLdJourney(LongDistanceJourney $journey): ?CarpoolPayment
    {
        return !is_null($journey->getCarpoolPayment())
            ? $journey->getCarpoolPayment()
            : (
                !is_null($journey->getCarpoolItem())
                ? $journey->getCarpoolItem()->getSuccessfullPayment()
                : null
            );
    }

    public static function getCarpoolPaymentFromCarpoolItem(CarpoolItem $carpoolItem): ?CarpoolPayment
    {
        $carpoolPayments = array_values(array_filter($carpoolItem->getCarpoolPayments(), function (CarpoolPayment $carpoolPayment) {
            return CarpoolPaymentValidator::isEecCompliant($carpoolPayment);
        }));

        return !(empty($carpoolPayments)) ? $carpoolPayments[0] : null;
    }
}
