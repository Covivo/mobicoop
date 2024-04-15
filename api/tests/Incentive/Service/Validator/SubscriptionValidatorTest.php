<?php

namespace App\Incentive\Service\Validator;

use App\Geography\Entity\Address;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Service\Definition\SdImproved;
use App\Incentive\Validator\SubscriptionValidator;
use App\Payment\Entity\PaymentProfile;
use App\Tests\Mocks\Incentive\LdSubscriptionMock;
use App\Tests\Mocks\Incentive\SdSubscriptionMock;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class SubscriptionValidatorTest extends TestCase
{
    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantBool($subscription)
    {
        $this->assertIsBool(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantFalse($subscription)
    {
        $this->assertFalse(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function isCommitmentJourneyEecCompliantTrue($subscription)
    {
        $this->assertTrue(SubscriptionValidator::isCommitmentJourneyEecCompliant($subscription));
    }

    // ---------------------------------------

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedBool($subscription)
    {
        $this->assertIsBool(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedFalse($subscription)
    {
        $this->assertFalse(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    /**
     * @dataProvider dataSusbcriptionsNotEecCompliant
     *
     * @test
     *
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function canSubscriptionBeRecommitedTrue($subscription)
    {
        $this->assertTrue(SubscriptionValidator::canSubscriptionBeRecommited($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function canPropertyBePatchedBool()
    {
        $this->assertIsBool(SubscriptionValidator::canPropertyBePatched(SpecificFields::DRIVING_LICENCE_NUMBER));
    }

    /**
     * @dataProvider dataFieldsCannotBePatched
     *
     * @test
     *
     * @param mixed $property
     */
    public function canPropertyBePatchedFalse(string $property)
    {
        $this->assertFalse(SubscriptionValidator::canPropertyBePatched($property));
    }

    /**
     * @dataProvider dataFieldsCanBePatched
     *
     * @test
     */
    public function canPropertyBePatchedTrue(string $property)
    {
        $this->assertTrue(SubscriptionValidator::canPropertyBePatched($property));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionValidatedBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionValidated(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionValidatedFalse()
    {
        $this->assertFalse(SubscriptionValidator::isSubscriptionValidated(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionValidatedTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setStatus(Subscription::STATUS_VALIDATED);

        $this->assertTrue(SubscriptionValidator::isSubscriptionValidated($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function hasSubscriptionExpiredBool()
    {
        $this->assertIsBool(SubscriptionValidator::hasSubscriptionExpired(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function hasSubscriptionExpiredFalse()
    {
        $this->assertFalse(SubscriptionValidator::hasSubscriptionExpired(SdSubscriptionMock::getNewSubscription()));
        $this->assertFalse(SubscriptionValidator::hasSubscriptionExpired(SdSubscriptionMock::getCommitedSubscription()));

        $now = new \DateTime();

        $subscription = SdSubscriptionMock::getCompleteValidatedSubscription();
        $subscription->setExpirationDate($now->sub(new \DateInterval('P'.($subscription->getValidityPeriodDuration() - 2).'M')));
    }

    /**
     * @test
     */
    public function hasSubscriptionExpiredTrue()
    {
        $now = new \DateTime();

        $subscription = SdSubscriptionMock::getCompleteValidatedSubscription();
        $subscription->setExpirationDate($now->sub(new \DateInterval('P'.($subscription->getValidityPeriodDuration() + 2).'M')));

        $this->assertTrue(SubscriptionValidator::hasSubscriptionExpired($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionStreetAddressValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionStreetAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionStreetAddressValidFalse()
    {
        $homeAddress = new Address();
        $homeAddress->setAddressLocality('Nancy');
        $homeAddress->setPostalCode('54000');
        $homeAddress->setCounty('France');

        $user = new User();
        $user->setGivenName(md5(rand()));
        $user->setFamilyName(md5(rand()));
        $user->setDrivingLicenceNumber(md5(rand()));
        $user->setTelephone(md5(rand()));
        $user->setEmail(md5(rand()));
        $user->setHomeAddress($homeAddress);

        $subscription = new ShortDistanceSubscription(
            $user,
            md5(rand()),
            new SdImproved()
        );

        $this->assertFalse(SubscriptionValidator::isSubscriptionStreetAddressValid($subscription));
    }

    /**
     * @test
     */
    public function isSubscriptionStreetAddressValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isSubscriptionStreetAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionPostalCodeValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionPostalCodeValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionPostalCodeValidFalse()
    {
        $homeAddress = new Address();
        $homeAddress->setHouseNumber('5');
        $homeAddress->setStreetAddress('rue de la monnaie');
        $homeAddress->setAddressLocality('Nancy');
        $homeAddress->setCounty('France');

        $user = new User();
        $user->setGivenName(md5(rand()));
        $user->setFamilyName(md5(rand()));
        $user->setDrivingLicenceNumber(md5(rand()));
        $user->setTelephone(md5(rand()));
        $user->setEmail(md5(rand()));
        $user->setHomeAddress($homeAddress);

        $subscription = new ShortDistanceSubscription(
            $user,
            md5(rand()),
            new SdImproved()
        );

        $this->assertFalse(SubscriptionValidator::isSubscriptionPostalCodeValid($subscription));
    }

    /**
     * @test
     */
    public function isSubscriptionPostalCodeValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isSubscriptionPostalCodeValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionAddressLocalityValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionAddressLocalityValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionAddressLocalityValidFalse()
    {
        $homeAddress = new Address();
        $homeAddress->setHouseNumber('5');
        $homeAddress->setStreetAddress('rue de la monnaie');
        $homeAddress->setPostalCode('54000');
        $homeAddress->setCounty('France');

        $user = new User();
        $user->setGivenName(md5(rand()));
        $user->setFamilyName(md5(rand()));
        $user->setDrivingLicenceNumber(md5(rand()));
        $user->setTelephone(md5(rand()));
        $user->setEmail(md5(rand()));
        $user->setHomeAddress($homeAddress);

        $subscription = new ShortDistanceSubscription(
            $user,
            md5(rand()),
            new SdImproved()
        );

        $this->assertFalse(SubscriptionValidator::isSubscriptionAddressLocalityValid($subscription));
    }

    /**
     * @test
     */
    public function isSubscriptionAddressLocalityValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isSubscriptionAddressLocalityValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionAddressValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionAddressValidFalse()
    {
        $homeAddress = new Address();
        $homeAddress->setHouseNumber('5');
        $homeAddress->setStreetAddress('rue de la monnaie');
        $homeAddress->setAddressLocality('Nancy');
        $homeAddress->setCounty('France');

        $user = new User();
        $user->setGivenName(md5(rand()));
        $user->setFamilyName(md5(rand()));
        $user->setDrivingLicenceNumber(md5(rand()));
        $user->setTelephone(md5(rand()));
        $user->setEmail(md5(rand()));
        $user->setHomeAddress($homeAddress);

        $subscription = new ShortDistanceSubscription(
            $user,
            md5(rand()),
            new SdImproved()
        );

        $this->assertFalse(SubscriptionValidator::isSubscriptionAddressValid($subscription));
    }

    /**
     * @test
     */
    public function isSubscriptionAddressValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isSubscriptionAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function areTokensAvailableBool()
    {
        $this->assertIsBool(SubscriptionValidator::areTokensAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function areTokensAvailableFalse()
    {
        $this->assertFalse(SubscriptionValidator::areTokensAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function areTokensAvailableTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setIncentiveProofTimestampToken(md5(rand()));
        $subscription->setIncentiveProofTimestampSigningTime(new \DateTime());
        $subscription->setCommitmentProofTimestampToken(md5(rand()));
        $subscription->setCommitmentProofTimestampSigningTime(new \DateTime());
        $subscription->setHonorCertificateProofTimestampToken(md5(rand()));
        $subscription->setHonorCertificateProofTimestampSigningTime(new \DateTime());

        $this->assertTrue(SubscriptionValidator::areTokensAvailable($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isSubscriptionPaymentProfileAvailableBool()
    {
        $this->assertIsBool(SubscriptionValidator::isSubscriptionPaymentProfileAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionPaymentProfileAvailableFalse()
    {
        $this->assertFalse(SubscriptionValidator::isSubscriptionPaymentProfileAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isSubscriptionPaymentProfileAvailableTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();

        $paymentProfile = new PaymentProfile();
        $paymentProfile->setUser($subscription->getUser());
        $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_VALIDATED);

        $this->assertTrue(SubscriptionValidator::isSubscriptionPaymentProfileAvailable($subscription));
    }

    // ---------------------------------------

    public function dataSusbcriptionsNotEecCompliant()
    {
        return [
            [SdSubscriptionMock::getCommitedSubscription()],
            [LdSubscriptionMock::getCommitedSubscription()],
        ];
    }

    public function dataSusbcriptionsEecCompliant()
    {
        $sdSubscription = SdSubscriptionMock::getValidatedSubscription();

        $ldSubscription = LdSubscriptionMock::getValidatedSubscription();

        return [
            [$sdSubscription],
            [$ldSubscription],
        ];
    }

    public function dataFieldsCannotBePatched(): array
    {
        return [
            [SpecificFields::HONOR_CERTIFICATE],
            [SpecificFields::JOURNEY_COST_SHARING_DATE],
            [SpecificFields::JOURNEY_ID],
            [SpecificFields::JOURNEY_PUBLISH_DATE],
            [SpecificFields::JOURNEY_START_DATE],
        ];
    }

    public function dataFieldsCanBePatched(): array
    {
        return [
            [SpecificFields::DRIVING_LICENCE_NUMBER],
            [SpecificFields::PHONE_NUMBER],
        ];
    }
}
