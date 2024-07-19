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
    public function isValidatedBool()
    {
        $this->assertIsBool(SubscriptionValidator::isValidated(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isValidatedFalse()
    {
        $this->assertFalse(SubscriptionValidator::isValidated(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isValidatedTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setStatus(Subscription::STATUS_VALIDATED);

        $this->assertTrue(SubscriptionValidator::isValidated($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function hasExpiredBool()
    {
        $this->assertIsBool(SubscriptionValidator::hasExpired(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function hasExpiredFalse()
    {
        $this->assertFalse(SubscriptionValidator::hasExpired(SdSubscriptionMock::getNewSubscription()));
        $this->assertFalse(SubscriptionValidator::hasExpired(SdSubscriptionMock::getCommitedSubscription()));

        $now = new \DateTime();

        $subscription = SdSubscriptionMock::getCompleteValidatedSubscription();
        $subscription->setExpirationDate($now->sub(new \DateInterval('P'.($subscription->getValidityPeriodDuration() - 2).'M')));
    }

    /**
     * @test
     */
    public function hasExpiredTrue()
    {
        $now = new \DateTime();

        $subscription = SdSubscriptionMock::getCompleteValidatedSubscription();
        $subscription->setExpirationDate($now->sub(new \DateInterval('P'.($subscription->getValidityPeriodDuration() + 2).'M')));

        $this->assertTrue(SubscriptionValidator::hasExpired($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isStreetAddressValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isStreetAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isStreetAddressValidFalse()
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

        $this->assertFalse(SubscriptionValidator::isStreetAddressValid($subscription));
    }

    /**
     * @test
     */
    public function isStreetAddressValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isStreetAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isPostalCodeValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isPostalCodeValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isPostalCodeValidFalse()
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

        $this->assertFalse(SubscriptionValidator::isPostalCodeValid($subscription));
    }

    /**
     * @test
     */
    public function isPostalCodeValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isPostalCodeValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isAddressLocalityValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isAddressLocalityValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isAddressLocalityValidFalse()
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

        $this->assertFalse(SubscriptionValidator::isAddressLocalityValid($subscription));
    }

    /**
     * @test
     */
    public function isAddressLocalityValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isAddressLocalityValid(SdSubscriptionMock::getNewSubscription()));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isAddressValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isAddressValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isAddressValidFalse()
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

        $this->assertFalse(SubscriptionValidator::isAddressValid($subscription));
    }

    /**
     * @test
     */
    public function isAddressValidTrue()
    {
        $this->assertTrue(SubscriptionValidator::isAddressValid(SdSubscriptionMock::getNewSubscription()));
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
    public function isPaymentProfileAvailableBool()
    {
        $this->assertIsBool(SubscriptionValidator::isPaymentProfileAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isPaymentProfileAvailableFalse()
    {
        $this->assertFalse(SubscriptionValidator::isPaymentProfileAvailable(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isPaymentProfileAvailableTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();

        $paymentProfile = new PaymentProfile();
        $paymentProfile->setUser($subscription->getUser());
        $paymentProfile->setValidationStatus(PaymentProfile::VALIDATION_VALIDATED);

        $this->assertTrue(SubscriptionValidator::isPaymentProfileAvailable($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isDrivingLicenceNumberValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isDrivingLicenceNumberValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isDrivingLicenceNumberValidFalse()
    {
        $this->assertFalse(SubscriptionValidator::isDrivingLicenceNumberValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @dataProvider dataDrivingLicenceNumber
     *
     * @test
     */
    public function isDrivingLicenceNumberValidTrue(string $drivingLicenceNumber)
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setDrivingLicenceNumber($drivingLicenceNumber);

        $this->assertTrue(SubscriptionValidator::isDrivingLicenceNumberValid($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function isPhoneNumberValidBool()
    {
        $this->assertIsBool(SubscriptionValidator::isPhoneNumberValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function isPhoneNumberValidFalse()
    {
        $this->assertFalse(SubscriptionValidator::isPhoneNumberValid(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @dataProvider dataPhoneNumber
     *
     * @test
     */
    public function isPhoneNumberValidTrue(string $phoneNumber)
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setTelephone($phoneNumber);

        $this->assertTrue(SubscriptionValidator::isPhoneNumberValid($subscription));
    }

    // ---------------------------------------

    /**
     * @test
     */
    public function hasBeenVerifiedBool()
    {
        $this->assertIsBool(SubscriptionValidator::hasBeenVerified(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function hasBeenVerifiedFalse()
    {
        $this->assertFalse(SubscriptionValidator::hasBeenVerified(SdSubscriptionMock::getNewSubscription()));
    }

    /**
     * @test
     */
    public function hasBeenVerifiedTrue()
    {
        $subscription = SdSubscriptionMock::getNewSubscription();
        $subscription->setStatus(Subscription::STATUS_VALIDATED);

        $this->assertTrue(SubscriptionValidator::hasBeenVerified($subscription));
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

    public function dataDrivingLicenceNumber(): array
    {
        return [
            ['051227308989'],
            ['822146819'],
            ['123456A'],
            ['99-X23836'],
        ];
    }

    public function dataPhoneNumber(): array
    {
        return [
            ['+496912345678'],  // Germany
            ['05 82 16 00 10'], // France - Mobicoop
            ['+63-2-8123-4567'], // Philippines
            ['02-8123-4567'], // Philippines
            ['8123-4567'], // Philippines
        ];
    }
}
