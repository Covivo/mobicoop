<?php

namespace App\Incentive\DataProvider;

use App\Tests\Incentive\IncentiveWebClient;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CeeSubscriptionsCollectionDataProviderTest extends IncentiveWebClient
{
    public const ENDPOINT = '/my_cee_subscriptions';
    private const PROVIDER_METHOD = self::METHOD_GET;

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

        $this->assertIsArray($this->_response);
        $this->assertInstanceOf('stdClass', $this->_response[0]);
        $this->assertObjectHasAttribute('longDistanceSubscriptions', $this->_response[0]);
        $this->assertObjectHasAttribute('shortDistanceSubscriptions', $this->_response[0]);
        $this->assertObjectHasAttribute('longDistanceExpirationDate', $this->_response[0]);
        $this->assertObjectHasAttribute('shortDistanceExpirationDate', $this->_response[0]);
        $this->assertObjectHasAttribute('nbPendingProofs', $this->_response[0]);
        $this->assertObjectHasAttribute('nbValidatedProofs', $this->_response[0]);
        $this->assertObjectHasAttribute('nbRejectedProofs', $this->_response[0]);
    }
}
