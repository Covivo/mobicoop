<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Geography\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Geography\Entity\Territory;
use App\Geography\Repository\TerritoryRepository;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for getting the territories of a point by its latitude and longitude.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class TerritoryPointCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $territoryRepository;
    private $context;

    public function __construct(RequestStack $requestStack, TerritoryRepository $territoryRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->territoryRepository = $territoryRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;

        return Territory::class === $resourceClass && 'territoriesPoint' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if (!isset($this->context['filters'])) {
            throw new LogicException('This route should always has latitude and longitude params');
        }

        if (!isset($this->context['filters']['latitude']) || !is_numeric($this->context['filters']['latitude'])) {
            throw new LogicException('Missing or invalid latitude');
        }

        if (!isset($this->context['filters']['longitude']) || !is_numeric($this->context['filters']['longitude'])) {
            throw new LogicException('Missing or invalid longitude');
        }

        return $this->territoryRepository->findPointTerritories($this->context['filters']['latitude'], $this->context['filters']['longitude']);
    }
}
