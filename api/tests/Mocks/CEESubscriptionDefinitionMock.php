<?php

namespace App\Tests\Mocks;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Service\Definition\LdImproved;
use App\Incentive\Service\Definition\SdImproved;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CEESubscriptionDefinitionMock
{
    public static function getSubscriptionDefinition(string $type): SubscriptionDefinitionInterface
    {
        if (LongDistanceSubscription::SUBSCRIPTION_TYPE === $type) {
            return new LdImproved();
        }

        if (ShortDistanceSubscription::SUBSCRIPTION_TYPE === $type) {
            return new SdImproved();
        }

        throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The request type is not allowed');
    }
}
