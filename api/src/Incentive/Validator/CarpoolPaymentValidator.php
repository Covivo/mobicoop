<?php

namespace App\Incentive\Validator;

use App\Payment\Entity\CarpoolPayment;

class CarpoolPaymentValidator
{
    public const VALID_EEC_STATUS = CarpoolPayment::STATUS_SUCCESS;

    public static function isEecCompliant(CarpoolPayment $carpoolPayment): bool
    {
        return
            static::isStatusEecCompliant($carpoolPayment)
            && static::isTransactionIdEecCompliant($carpoolPayment)
            && static::hasAtLeastAProofEECCompliant($carpoolPayment);
    }

    public static function isStatusEecCompliant(CarpoolPayment $carpoolPayment): bool
    {
        return static::VALID_EEC_STATUS === $carpoolPayment->getStatus();
    }

    public static function isTransactionIdEecCompliant(CarpoolPayment $carpoolPayment): bool
    {
        return !is_null($carpoolPayment->getTransactionId());
    }

    public static function hasAtLeastAProofEECCompliant(CarpoolPayment $carpoolPayment): bool
    {
        return $carpoolPayment->hasAtLeastAProofEECCompliant();
    }
}
