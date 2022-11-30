<?php

namespace App\Incentive\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\MobConnectSubscriptionManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

class CeeSubscriptionsCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_security;
    private $_subscriptionManager;

    public function __construct(Security $security, MobConnectSubscriptionManager $subscriptionManager)
    {
        $this->_security = $security;
        $this->_subscriptionManager = $subscriptionManager;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return CeeSubscriptions::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null)
    {
        $user = $this->_security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('Only a User can make this');
        }

        return $this->_subscriptionManager->getUserSubscriptions($user);
    }
}
