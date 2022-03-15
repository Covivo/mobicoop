<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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
use App\Geography\Ressource\Point;
use App\Geography\Service\PointSearcher;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for point search.
 */
final class PointCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $pointSearcher;

    public function __construct(RequestStack $requestStack, PointSearcher $pointSearcher)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->pointSearcher = $pointSearcher;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Point::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if (null !== $this->request->get('search')) {
            return $this->pointSearcher->geocode($this->request->get('search'));
        }

        return [];
    }
}
