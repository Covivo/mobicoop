<?php

namespace App\Incentive\Service\Definition;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Determines for a subscription type, the definition that must be used for its creation.
 */
abstract class DefinitionSelector
{
    public static function getDefinition(string $type): SubscriptionDefinitionInterface
    {
        if (!Subscription::isTypeAllowed($type)) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The required type is not available');
        }

        switch ($type) {
            case LongDistanceSubscription::SUBSCRIPTION_TYPE:
                $class = LongDistanceSubscription::class;

                break;

            case ShortDistanceSubscription::SUBSCRIPTION_TYPE:
                $class = ShortDistanceSubscription::class;

                break;

            default:
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The use case was not planned');
        }

        foreach ($class::getAvailableDefinitions() as $definition) {
            if ($definition::isReady()) {
                return new $definition();
            }
        }
    }
}
