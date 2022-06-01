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

namespace App\Carpool\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Item data provider for external based Ad retrieve.
 */
final class AdItemExternalDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    private $adManager;
    private $request;

    public function __construct(AdManager $adManager, RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->adManager = $adManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Ad::class === $resourceClass && 'get_external' === $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?object
    {
        return $this->adManager->getAdFromExternalId($this->request->get('id'), $this->request->query->get('filters'), $this->request->query->get('order'));
    }
}
