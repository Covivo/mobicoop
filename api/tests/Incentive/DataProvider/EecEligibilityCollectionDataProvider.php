<?php

use App\Tests\Incentive\IncentiveWebClient;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class EecEligibilityCollectionDataProviderTest extends IncentiveWebClient
{
    public const ENDPOINT = '/my_eec_eligibility';
    private const PROVIDER_METHOD = self::METHOD_GET;

    protected function setUp(): void
    {
        parent::setUsers();
    }

    /**
     * @test
     */
    public function dataProviderUnauthorized()
    {
        parent::requestUnauthorized(self::PROVIDER_METHOD, self::ENDPOINT);
    }

    /**
     * @test
     */
    public function dataProviderAuthorized()
    {
        parent::requestToken(self::PROVIDER_METHOD, self::ENDPOINT);

        $this->assertInstanceOf('stdClass', $this->_response);

        $this->assertObjectHasAttribute('longDistanceEligibility', $this->_response);
        $this->assertIsBool($this->_response->longDistanceEligibility);
        $this->assertObjectHasAttribute('longDistanceDrivingLicenceNumberDoublon', $this->_response);
        $this->assertIsInt($this->_response->longDistanceDrivingLicenceNumberDoublon);
        $this->assertObjectHasAttribute('longDistancePhoneDoublon', $this->_response);
        $this->assertIsInt($this->_response->longDistancePhoneDoublon);

        $this->assertObjectHasAttribute('shortDistanceEligibility', $this->_response);
        $this->assertIsBool($this->_response->shortDistanceEligibility);
        $this->assertObjectHasAttribute('shortDistanceDrivingLicenceNumberDoublon', $this->_response);
        $this->assertIsInt($this->_response->shortDistanceDrivingLicenceNumberDoublon);
        $this->assertObjectHasAttribute('shortDistancePhoneDoublon', $this->_response);
        $this->assertIsInt($this->_response->shortDistancePhoneDoublon);
    }
}