<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use App\Geography\Entity\Address;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockAddress;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockUser;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class AccountTokenTest extends TestCase
{
    public function setUp(): void {}

    /**
     * @test
     *
     * @dataProvider getUser
     */
    public function testBuildBodyReturnsAnArray(User $user, ?Address $address)
    {
        $accountToken = new AccountToken($user, $address);
        $this->assertIsArray($accountToken->buildBody());
    }

    public function getUser(): array
    {
        /**
         * User with not international telephone number and a street.
         */
        $user = MockUser::getSimpleUser();

        /**
         * User with international telephone number and a street address + house number.
         */
        $user2 = MockUser::getSimpleUser();
        $user2->setTelephone('+33606060606');

        $homeAddress2 = MockAddress::getSimpleAddress();
        $homeAddress2->setStreetAddress(null);
        $homeAddress2->setStreet('rue de la paix');
        $homeAddress2->setHouseNumber(1);

        $user2->setHomeAddress($homeAddress2);

        $result = '{"account":{"business_type":"individual","individual":{"first_name":"test","last_name":"test","email":"test@test.com","address":{"line1":"1 rue de la paix","city":"Paris","postal_code":"75000","country":"FR"},"dob":{"day":"01","month":"01","year":"1980"},"phone":"+33606060606"},"tos_shown_and_accepted":true}}';

        /** Check when using other address than home address */
        $otherAddress = MockAddress::getSimpleAddress();
        $otherAddress->setStreetAddress('2 rue de vaugirard');

        $resultWithOtherHomeAddress = '{"account":{"business_type":"individual","individual":{"first_name":"test","last_name":"test","email":"test@test.com","address":{"line1":"2 rue de vaugirard","city":"Paris","postal_code":"75000","country":"FR"},"dob":{"day":"01","month":"01","year":"1980"},"phone":"+33606060606"},"tos_shown_and_accepted":true}}';

        return [
            [$user, null, $result], [$user2, null, $result], [$user, $otherAddress, $resultWithOtherHomeAddress],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getUser
     */
    public function testBuildBodyReturnsTheRightArray(User $user, ?Address $address, string $result)
    {
        $accountToken = new AccountToken($user, $address);
        $this->assertEquals($result, json_encode($accountToken->buildBody()));
    }
}
