<?php

namespace App\ExternalJourney\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\ExternalJourney\Entity\ExternalJourney;

final class ExternalJourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return "Journey::class === $resourceClass";
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        return ["Jojo","Jojo2"];
    }
}