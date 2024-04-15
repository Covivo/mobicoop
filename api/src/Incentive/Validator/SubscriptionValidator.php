<?php

namespace App\Incentive\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;

abstract class SubscriptionValidator
{
    public const ALLOWED_PROPERTIES_TO_PATCH = [
        SpecificFields::DRIVING_LICENCE_NUMBER,
        SpecificFields::PHONE_NUMBER,
    ];

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function canSubscriptionBeRecommited($subscription): bool
    {
        return
            is_null($subscription->getStatus())
            && !static::isCommitmentJourneyEecCompliant($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isCommitmentJourneyEecCompliant($subscription): bool
    {
        return
            !is_null($subscription->getCommitmentProofJourney())
            && $subscription->getCommitmentProofJourney()->isEECCompliant();
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionReadyToVerify($subscription): bool
    {
        return $subscription instanceof LongDistanceSubscription
            ? static::isLdSubscriptionReadyToVerify($subscription) : static::isSdSubscriptionReadyToVerify($subscription);
    }

    public static function isLdSubscriptionReadyToVerify(LongDistanceSubscription $subscription): bool
    {
        return
            !static::isSubscriptionValidated($subscription)
            && !static::hasSubscriptionExpired($subscription)
            && static::isSubscriptionAddressValid($subscription)
            && static::isSubscriptionPaymentProfileAvailable($subscription)
            && !$subscription->getJourneys()->isEmpty()
            && !is_null($subscription->getCommitmentProofJourney())
            && !is_null($subscription->getCommitmentProofJourney()->getCarpoolPayment())
            && CarpoolPaymentValidator::isEecCompliant($subscription->getCommitmentProofJourney()->getCarpoolPayment())
            && static::areTokensAvailable($subscription);
    }

    public static function isSdSubscriptionReadyToVerify(ShortDistanceSubscription $subscription): bool
    {
        return
            static::isSubscriptionValidated($subscription)
            && !static::hasSubscriptionExpired($subscription)
            && static::isSubscriptionAddressValid($subscription)
            && static::isSubscriptionPaymentProfileAvailable($subscription)
            && !$subscription->getJourneys()->isEmpty()
            && !is_null($subscription->getCommitmentProofJourney())
            && !is_null($subscription->getCommitmentProofJourney()->getCarpoolProof())
            && CarpoolProofValidator::isEecCompliant($subscription->getCommitmentProofJourney()->getCarpoolProof())
            && static::areTokensAvailable($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionPaymentProfileAvailable($subscription): bool
    {
        return
            !is_null($subscription->getUser())
            && $subscription->getUser()->hasBankingIdentityValidated();
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function areTokensAvailable($subscription): bool
    {
        return
            !is_null($subscription->getIncentiveProofTimestampToken())
            && !is_null($subscription->getIncentiveProofTimestampSigningTime())
            && !is_null($subscription->getCommitmentProofTimestampToken())
            && !is_null($subscription->getCommitmentProofTimestampSigningTime())
            && !is_null($subscription->getHonorCertificateProofTimestampToken())
            && !is_null($subscription->getHonorCertificateProofTimestampSigningTime());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function hasSubscriptionExpired($subscription): bool
    {
        $now = new \DateTime('now');

        return
            !empty($subscription->getJourneys())
            && !is_null($subscription->getExpirationDate())
            && $subscription->getExpirationDate() < $now->sub(new \DateInterval('P'.$subscription->getValidityPeriodDuration().'M'));
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionAddressValid($subscription): bool
    {
        return
            static::isSubscriptionStreetAddressValid($subscription)
            && static::isSubscriptionPostalCodeValid($subscription)
            && static::isSubscriptionAddressLocalityValid($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionStreetAddressValid($subscription): bool
    {
        return
            !is_null($subscription->getStreetAddress())
            && '' !== trim($subscription->getStreetAddress());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionPostalCodeValid($subscription): bool
    {
        return preg_match('/^((1(A|B))|[0-9]{2})[0-9]{3}$/', $subscription->getPostalCode());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionAddressLocalityValid($subscription): bool
    {
        return
            !is_null($subscription->getAddressLocality())
            && '' !== trim($subscription->getAddressLocality());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isSubscriptionValidated($subscription): bool
    {
        return Subscription::STATUS_VALIDATED === $subscription->getStatus();
    }

    public static function canPropertyBePatched(string $property): bool
    {
        return in_array($property, static::ALLOWED_PROPERTIES_TO_PATCH);
    }
}
