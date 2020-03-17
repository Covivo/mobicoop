<?php
// api/src/DataProvider/BlogPostItemDataProvider.php

namespace App\Solidary\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Solidary\Entity\Exposed\Volunteer;
use App\Solidary\Service\VolunteerManager;

final class VolunteerItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $volunteerManager;

    public function __construct(VolunteerManager $volunteerManager)
    {
        $this->volunteerManager = $volunteerManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Volunteer::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Volunteer
    {
        return $this->volunteerManager->getVolunteer($id);
    }
}
