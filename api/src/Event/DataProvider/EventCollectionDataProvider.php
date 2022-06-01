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
 */

namespace App\Event\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Event\Entity\Event;
use App\Event\Service\EventManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Event.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
final class EventCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $eventManager;
    private $context;

    public function __construct(RequestStack $requestStack, eventManager $eventManager, iterable $collectionFilters)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
        $this->collectionFilters = $collectionFilters;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;

        return Event::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        $queryBuilder = $this->eventManager->getEvents();

        // We're browsing every available filters
        foreach ($this->collectionFilters as $collectionFilter) {
            $collectionFilter->applyToCollection($queryBuilder, new QueryNameGenerator(), $resourceClass, $operationName, $this->context);

            if ($collectionFilter instanceof QueryResultCollectionExtensionInterface && $collectionFilter->supportsResult($resourceClass, $operationName)) {
                return $collectionFilter->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
