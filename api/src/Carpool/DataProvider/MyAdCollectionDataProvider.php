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

namespace App\Carpool\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Carpool\Ressource\MyAd;
use App\Carpool\Service\MyAdManager;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for MyAds.
 *
 */
final class MyAdCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $myAdManager;
    protected $security;

    public function __construct(MyAdManager $myAdManager, Security $security)
    {
        $this->myAdManager = $myAdManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MyAd::class === $resourceClass && $operationName === "get";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return $this->myAdManager->getMyAds($this->security->getUser());
    }
}
