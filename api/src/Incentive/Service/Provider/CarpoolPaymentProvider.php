<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceJourney;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Repository\CarpoolPaymentRepository;

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

    public static function getCarpoolPaymentFromCarpoolItem(CarpoolPaymentRepository $repository, CarpoolItem $carpoolItem): ?CarpoolPayment
    {
        return $repository->findOneByCarpoolItem($carpoolItem);
    }
}
