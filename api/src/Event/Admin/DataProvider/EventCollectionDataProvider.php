<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Event\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Event\Admin\Service\EventManager;
use App\Event\Entity\Event;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection member data provider in admin context.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
final class EventCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $eventManager;
    private $collectionFilters;

    public function __construct(RequestStack $requestStack, EventManager $eventManager, iterable $collectionFilters)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
        $this->collectionFilters = $collectionFilters;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Event::class === $resourceClass && $operationName === "ADMIN_get";
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        // We get only the QueryBuilder object. That way, we can apply filter on it
        $queryBuilder = $this->eventManager->getInternalEventsQueryBuilder();

        // We're browsing every available filters
        foreach ($this->collectionFilters as $collectionFilter) {
            $collectionFilter->applyToCollection($queryBuilder, new QueryNameGenerator(), $resourceClass, $operationName, $context);

            if ($collectionFilter instanceof QueryResultCollectionExtensionInterface && $collectionFilter->supportsResult($resourceClass, $operationName)) {
                return $collectionFilter->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
