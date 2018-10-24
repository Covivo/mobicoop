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
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\PublicTransport\Entity\Journey;
use App\PublicTransport\Service\PTDataProvider;
use Symfony\Component\HttpFoundation\RequestStack;

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
        return Journey::class === $resourceClass;
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?Array
    {
        return $this->dataProvider->getJourneys(
                $this->request->get("provider"),
                $this->request->get("apikey"),
                $this->request->get("origin_latitude"),
                $this->request->get("origin_longitude"),
                $this->request->get("destination_latitude"),
                $this->request->get("destination_longitude"),
                $this->request->get("date")
                );        
    }
}