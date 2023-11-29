<?php

namespace App\Incentive\Controller;

use App\Tests\Incentive\IncentiveWebClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class EECTimestampsTest extends IncentiveWebClient
{
    public const ENDPOINT = '/users/{user_id}/eec-timestamps';
    private const CONTROLLER_METHOD = self::METHOD_GET;

    private const USER_ID = 12;

    protected function setUp(): void
    {
        parent::setUsers();
    }

    /**
     * test.
     */
    public function ControllerUnauthorized()
    {
        parent::requestUnauthorized(self::CONTROLLER_METHOD, $this->setEndpointParameters(self::ENDPOINT, ['user_id' => self::USER_ID]));
    }

    /**
     * @test
     */
    public function controllerUnallowedUser()
    {
        parent::requestToken(self::CONTROLLER_METHOD, $this->setEndpointParameters(self::ENDPOINT, ['user_id' => self::USER_ID]), $this->_user);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     */
    public function controllerSuccess()
    {
        parent::requestToken(self::CONTROLLER_METHOD, $this->setEndpointParameters(self::ENDPOINT, ['user_id' => self::USER_ID]), $this->_adminUser);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertInstanceOf('stdClass', $this->_response);
        $this->assertObjectHasAttribute('id', $this->_response);
        $this->assertSame(self::USER_ID, $this->_response->id);

        $this->assertObjectHasAttribute('longDistanceSubscription', $this->_response);
        $this->assertInstanceOf('stdClass', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('id', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('incentiveProofTimestampToken', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('incentiveProofTimestampSigningTime', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('commitmentProofTimestampToken', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('commitmentProofTimestampSigningTime', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('honorCertificateProofTimestampToken', $this->_response->longDistanceSubscription);
        $this->assertObjectHasAttribute('honorCertificateProofTimestampSigningTime', $this->_response->longDistanceSubscription);

        $this->assertObjectHasAttribute('shortDistanceSubscription', $this->_response);
        $this->assertInstanceOf('stdClass', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('id', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('incentiveProofTimestampToken', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('incentiveProofTimestampSigningTime', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('commitmentProofTimestampToken', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('commitmentProofTimestampSigningTime', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('honorCertificateProofTimestampToken', $this->_response->shortDistanceSubscription);
        $this->assertObjectHasAttribute('honorCertificateProofTimestampSigningTime', $this->_response->shortDistanceSubscription);
    }
}
