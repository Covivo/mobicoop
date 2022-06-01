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

namespace App\PublicTransport\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\PublicTransport\Entity\PTTripPoint;
use App\PublicTransport\Service\PTDataProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Public Transport TripPoint entity.
 *
 * Automatically associated to Public Transport TripPoint entity thanks to autowiring (see 'supports' method).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
final class TripPointCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $dataProvider;
    protected $request;

    public function __construct(RequestStack $requestStack, PTDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->request = $requestStack->getCurrentRequest();
    }


    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return PTTripPoint::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {

        // First, I check the Lat/Lon. If they are given, we ignore keywords
        if (!is_null($this->request->get("latitude")) &&
            !is_null($this->request->get("longitude")) &&
            $this->request->get("latitude") != 0 &&
            $this->request->get("longitude") != 0
        ) {
            if (
                is_null($this->request->get("provider")) &&
                is_null($this->request->get("perimeter")) &&
                is_null($this->request->get("transportModes"))
            ) {
                return null;
            }

            return $this->dataProvider->getTripPoints(
                $this->request->get("provider"),
                $this->request->get("latitude"),
                $this->request->get("longitude"),
                $this->request->get("perimeter"),
                $this->request->get('transportModes'),
                ""
            );
        } else {
            // We assume that we have to use keywords for the search
            if (
                is_null($this->request->get("provider")) &&
                is_null($this->request->get("perimeter")) &&
                is_null($this->request->get("keywords")) &&
                is_null($this->request->get("transportModes"))
            ) {
                return null;
            }

            return $this->dataProvider->getTripPoints(
                $this->request->get("provider"),
                0,
                0,
                $this->request->get("perimeter"),
                $this->request->get('transportModes'),
                $this->request->get("keywords")
            );
        }
    }
}
