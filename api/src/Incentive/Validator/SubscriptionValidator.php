<?php

namespace App\Incentive\Validator;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Service\DrivingLicenceService;
use App\Service\Phone\PhoneService;

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
    public static function isReadyToVerify($subscription): bool
    {
        return $subscription instanceof LongDistanceSubscription
            ? static::isLdReadyToVerify($subscription) : static::isSdReadyToVerify($subscription);
    }

    public static function isLdReadyToVerify(LongDistanceSubscription $subscription): bool
    {
        return
            !static::isValidated($subscription)
            && static::isPaymentProfileAvailable($subscription)
            && !$subscription->getJourneys()->isEmpty()
            && !is_null($subscription->getCommitmentProofJourney())
            && !is_null($subscription->getCommitmentProofJourney()->getCarpoolPayment())
            && CarpoolPaymentValidator::isEecCompliant($subscription->getCommitmentProofJourney()->getCarpoolPayment())
            && static::areTokensAvailable($subscription);
    }

    public static function isSdReadyToVerify(ShortDistanceSubscription $subscription): bool
    {
        return
            !static::isValidated($subscription)
            && static::isPaymentProfileAvailable($subscription)
            && !$subscription->getJourneys()->isEmpty()
            && !is_null($subscription->getCommitmentProofJourney())
            && !is_null($subscription->getCommitmentProofJourney()->getCarpoolProof())
            && CarpoolProofValidator::isEecCompliant($subscription->getCommitmentProofJourney()->getCarpoolProof())
            && static::areTokensAvailable($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isPaymentProfileAvailable($subscription): bool
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
    public static function hasExpired($subscription): bool
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
    public static function isAddressValid($subscription): bool
    {
        return
            static::isStreetAddressValid($subscription)
            && static::isPostalCodeValid($subscription)
            && static::isAddressLocalityValid($subscription);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isStreetAddressValid($subscription): bool
    {
        return
            !is_null($subscription->getStreetAddress())
            && '' !== trim($subscription->getStreetAddress());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isPostalCodeValid($subscription): bool
    {
        return preg_match('/^((1(A|B))|[0-9]{2})[0-9]{3}$/', $subscription->getPostalCode());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isAddressLocalityValid($subscription): bool
    {
        return
            !is_null($subscription->getAddressLocality())
            && '' !== trim($subscription->getAddressLocality());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isValidated($subscription): bool
    {
        return Subscription::STATUS_VALIDATED === $subscription->getStatus();
    }

    public static function canPropertyBePatched(string $property): bool
    {
        return in_array($property, static::ALLOWED_PROPERTIES_TO_PATCH);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isDrivingLicenceNumberValid($subscription): bool
    {
        $validator = new DrivingLicenceService($subscription->getDrivingLicenceNumber());

        return $validator->isDrivingLicenceNumberValid();
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function isPhoneNumberValid($subscription): bool
    {
        $converter = new PhoneService($subscription->getTelephone());

        return preg_match('/^\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*(\d{1,2})$/', $converter->getInternationalPhoneNumber());
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public static function hasBeenVerified($subscription): bool
    {
        return
            !is_null($subscription->getStatus())
            && (Subscription::STATUS_REJECTED === $subscription->getStatus() || Subscription::STATUS_VALIDATED === $subscription->getStatus());
    }
}
