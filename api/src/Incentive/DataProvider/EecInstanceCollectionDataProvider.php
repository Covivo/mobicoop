<?php

namespace App\Incentive\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\Manager\InstanceManager;

class EecInstanceCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var InstanceManager
     */
    private $_instanceManager;

    public function __construct(InstanceManager $instanceManager)
    {
        $this->_instanceManager = $instanceManager;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return EecInstance::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null)
    {
        return $this->_instanceManager->getEecInstance();
    }
}
