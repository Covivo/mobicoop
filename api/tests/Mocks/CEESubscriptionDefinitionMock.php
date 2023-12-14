<?php

namespace App\Tests\Mocks;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Service\Definition\LdStandard;
use App\Incentive\Service\Definition\SdStandard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CEESubscriptionDefinitionMock
{
    public static function getSubscriptionDefinition(string $type): SubscriptionDefinitionInterface
    {
        if (LongDistanceSubscription::SUBSCRIPTION_TYPE === $type) {
            return new LdStandard();
        }

        if (ShortDistanceSubscription::SUBSCRIPTION_TYPE === $type) {
            return new SdStandard();
        }

        throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The request type is not allowed');
    }
}
