<?php

namespace App\Incentive\Validator;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;

class CarpoolProofValidator
{
    public const VALID_EEC_STATUS = CarpoolProof::STATUS_VALIDATED;
    public const VALID_EEC_TYPE = CarpoolProof::TYPE_HIGH;

    public const REFERENCE_COUNTRY = 'France';

    public const CARPOOL_PROOF_ERROR_STATUS = [
        CarpoolProof::STATUS_ERROR,
        CarpoolProof::STATUS_CANCELED,
        CarpoolProof::STATUS_ACQUISITION_ERROR,
        CarpoolProof::STATUS_NORMALIZATION_ERROR,
        CarpoolProof::STATUS_FRAUD_ERROR,
        CarpoolProof::STATUS_EXPIRED,
        CarpoolProof::STATUS_CANCELED_BY_OPERATOR,
    ];

    public static function isEecCompliant(CarpoolProof $carpoolProof): bool
    {
        return
            self::isCarpoolProofStatusEecCompliant($carpoolProof)
            && self::isCarpoolProofTypeEecCompliant($carpoolProof);
    }

    public static function isCarpoolProofStatusEecCompliant(CarpoolProof $carpoolProof): bool
    {
        return static::VALID_EEC_STATUS === $carpoolProof->getStatus();
    }

    public static function isCarpoolProofTypeEecCompliant(CarpoolProof $carpoolProof): bool
    {
        return static::VALID_EEC_TYPE === $carpoolProof->getType();
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceubscription $subscription
     */
    public static function isCarpoolProofSubscriptionCommitmentProof($subscription, CarpoolProof $carpoolProof): bool
    {
        $commitmentJourney = $subscription->getCommitmentProofJourney();

        if (is_null($commitmentJourney)) {
            return false;
        }

        return $commitmentJourney->getCarpoolProof()->getId() === $carpoolProof->getId();
    }

    public static function isStatusError(CarpoolProof $carpoolProof): bool
    {
        return in_array($carpoolProof->getStatus(), static::CARPOOL_PROOF_ERROR_STATUS);
    }

    public static function isDowngradedType(CarpoolProof $carpoolProof): bool
    {
        return $carpoolProof->getType() != static::VALID_EEC_TYPE;
    }

    public static function isCarpoolProofOriginOrDestinationFromFrance(CarpoolProof $carpoolProof): bool
    {
        if (
            !is_null($carpoolProof->getAsk())
            && !is_null($carpoolProof->getAsk()->getMatching())
            && !is_null($carpoolProof->getAsk()->getMatching()->getWaypoints())
            && !empty($carpoolProof->getAsk()->getMatching()->getWaypoints())
        ) {
            $waypoints = $carpoolProof->getAsk()->getMatching()->getWaypoints();

            foreach ($waypoints as $waypoint) {
                if (
                    !is_null($waypoint->getAddress())
                    && !is_null($waypoint->getAddress()->getAddressCountry())
                    && self::REFERENCE_COUNTRY === $waypoint->getAddress()->getAddressCountry()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
