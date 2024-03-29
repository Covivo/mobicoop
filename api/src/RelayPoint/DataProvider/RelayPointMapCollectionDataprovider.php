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

namespace App\RelayPoint\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\RelayPoint\Resource\RelayPointMap;
use App\RelayPoint\Service\RelayPointMapManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

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
    private $security;
    
    public function __construct(RequestStack $requestStack, RelayPointMapManager $relayPointMapManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->relayPointMapManager = $relayPointMapManager;
        $this->security = $security;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RelayPointMap::class === $resourceClass && $operationName === "get";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): ?array
    {
        if ($this->request->get("communityId")!== null) {
            return $this->relayPointMapManager->getRelayPointsMapCommunity($this->request->get("communityId"));
        }

        $user = ($this->security->getUser() instanceof User) ? $this->security->getUser() : null;
        return $this->relayPointMapManager->getRelayPointsMap($user, $context);
    }
}
