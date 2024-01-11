<?php

namespace App\DataProvider\Entity\MobConnect;

use App\Incentive\Service\MobConnectMessages;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RouteProvider
{
    public const ROUTE_SUBSCRIPTIONS = '/v1/subscriptions';
    public const ROUTE_GET_SUBSCRIPTION = self::ROUTE_SUBSCRIPTIONS.'/'.self::SUBSCRIPTION_ID_TAG;
    public const ROUTE_PATCH_SUBSCRIPTIONS = self::ROUTE_GET_SUBSCRIPTION;
    public const ROUTE_SUBSCRIPTIONS_VERIFY = self::ROUTE_GET_SUBSCRIPTION.'/verify';
    public const ROUTE_SUBSCRIPTIONS_TIMESTAMPS = self::ROUTE_SUBSCRIPTIONS.'/timestamps';
    public const ROUTE_INCENTIVES = '/v1/incentives';
    public const ROUTE_INCENTIVE = self::ROUTE_INCENTIVES.'/'.self::INCENTIVE_ID_TAG;

    public const INCENTIVE_ID_TAG = '{INCENTIVE_ID}';
    public const SUBSCRIPTION_ID_TAG = '{SUBSCRIPTION_ID}';

    public const ALLOWED_ID_TAGS = [self::INCENTIVE_ID_TAG, self::SUBSCRIPTION_ID_TAG];

    /**
     * Replaces tags with the transmitted resource identifier.
     */
    public static function buildResource(string $resource, string $resource_id = null, ?string $tag = null): string
    {
        if (strpos($resource, self::SUBSCRIPTION_ID_TAG) || strpos($resource, self::INCENTIVE_ID_TAG)) {
            if (is_null($resource_id)) {
                throw new BadRequestHttpException(MobConnectMessages::SUBSCRIPTION_PARAMETER_MISSING);
            }

            $resource = str_replace(
                !is_null($tag) ? $tag : self::SUBSCRIPTION_ID_TAG,
                $resource_id,
                $resource
            );
        }

        return $resource;
    }
}
