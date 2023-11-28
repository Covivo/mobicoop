<?php

namespace App\Incentive\Controller\Subscription;

use App\Tests\Incentive\IncentiveWebClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class SdSubscriptionCommitTest extends IncentiveWebClient
{
    public const DEFAULT_TAG = 'subscription_id';
    public const ENDPOINT = '/eec/sd-subscriptions/{subscription_id}/commit';

    private const EXISTANT_SUBSCRIPTION_ID = 6;
    private const NON_EXISTANT_SUBSCRIPTION_ID = 12;

    /**
     * @test
     */
    public function controllerUnauthorized()
    {
        parent::requestUnauthorized(self::METHOD_PUT, $this->setEndpointParameters(self::ENDPOINT, [self::DEFAULT_TAG => self::EXISTANT_SUBSCRIPTION_ID]));
    }

    /**
     * @test
     */
    public function controllerUnallowedUser()
    {
        parent::requestToken(self::METHOD_PUT, $this->setEndpointParameters(self::ENDPOINT, [self::DEFAULT_TAG => self::EXISTANT_SUBSCRIPTION_ID]), self::USER);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     */
    public function controllerUnallowedMethod()
    {
        parent::requestToken(self::METHOD_GET, $this->setEndpointParameters(self::ENDPOINT, [self::DEFAULT_TAG => self::EXISTANT_SUBSCRIPTION_ID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * @test
     */
    public function controllerSubscriptionNotFound()
    {
        parent::requestToken(self::METHOD_PUT, $this->setEndpointParameters(self::ENDPOINT, [self::DEFAULT_TAG => self::NON_EXISTANT_SUBSCRIPTION_ID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    // TODO
    public function controllerSuccess() {}
}
