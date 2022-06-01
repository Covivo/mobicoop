<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Geography\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Geography\Entity\Address;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Geography\Service\GeoRouter;
use App\Geography\Entity\Direction;

/**
 * Collection data provider for Direction search (route calculation).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
final class DirectionSearchCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    protected $geoRouter;

    public function __construct(RequestStack $requestStack, GeoRouter $geoRouter)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->geoRouter = $geoRouter;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Direction::class === $resourceClass && $operationName === "search";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        $addresses = [];
        foreach ($this->request->get('points') as $point) {
            $waypoint = new Address();
            $waypoint->setLatitude($point['latitude']);
            $waypoint->setLongitude($point['longitude']);
            $addresses[] = $waypoint;
        }
        return $this->geoRouter->getRoutes($addresses, false, true, $this->request->get('toll'));
    }
}
