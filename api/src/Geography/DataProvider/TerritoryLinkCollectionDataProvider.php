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
use App\Geography\Service\TerritoryManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Territory link calculation (addresses and directions link).
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class TerritoryLinkCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $territoryManager;

    public function __construct(RequestStack $requestStack, TerritoryManager $territoryManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->territoryManager = $territoryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Territory::class === $resourceClass && 'link' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        $this->territoryManager->initAddressesAndDirections();

        return [];
    }
}
