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
 **************************/

namespace App\Community\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Resource\RelayPointMap;
use App\Community\Service\RelayPointMapManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for RelayPointMap resource.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 *
 */
final class RelayPointMapCollectionDataprovider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $relayPointMapManager;
    
    public function __construct(RequestStack $requestStack, RelayPointMapManager $relayPointMapManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointMapManager = $relayPointMapManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RelayPointMap::class === $resourceClass && $operationName === "get";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if ($this->request->get("communityId")!== null) {
            return $this->relayPointMapManager->getRelayPointsMapCommunity($this->request->get("communityId"));
        }
        return [];
    }
}
