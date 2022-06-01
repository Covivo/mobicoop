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
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Service\PTDataProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Public Transport Journey entity.
 *
 * Automatically associated to Public Transport Journey entity thanks to autowiring (see 'supports' method).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
final class JourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
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
        return PTJourney::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if (
            is_null($this->request->get("provider")) &&
            is_null($this->request->get("origin_latitude")) &&
            is_null($this->request->get("origin_longitude")) &&
            is_null($this->request->get("destination_latitude")) &&
            is_null($this->request->get("destination_longitude")) &&
            is_null($this->request->get("date"))
        ) {
            return null;
        }

        return $this->dataProvider->getJourneys(
            $this->request->get("provider"),
            $this->request->get("origin_latitude"),
            $this->request->get("origin_longitude"),
            $this->request->get("destination_latitude"),
            $this->request->get("destination_longitude"),
            \DateTime::createFromFormat(PTDataProvider::DATETIME_FORMAT, $this->request->get("date")),
            !is_null($this->request->get("dateType")) ? $this->request->get("dateType") : null,
            !is_null($this->request->get("modes")) ? $this->request->get("modes") : null
        );
    }
}
