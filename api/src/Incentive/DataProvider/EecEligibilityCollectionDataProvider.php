<?php

namespace App\Incentive\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Incentive\Resource\EecEligibility;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

class EecEligibilityCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var SubscriptionManager
     */
    private $_subscriptionManager;

    /**
     * @var User
     */
    private $_user;

    public function __construct(Security $security, SubscriptionManager $subscriptionManager)
    {
        $this->_user = $security->getUser();
        $this->_subscriptionManager = $subscriptionManager;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return EecEligibility::class === $resourceClass && 'get' == $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null)
    {
        return $this->_subscriptionManager->getUserEECEligibility($this->_user);
    }
}
