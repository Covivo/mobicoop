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

namespace App\Carpool\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Carpool\Entity\Ad;
use App\Carpool\Service\AdManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for user's ads.
 *
 */
final class MyAdCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    protected $adManager;
    protected $security;
    
    public function __construct(RequestStack $requestStack, AdManager $adManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->adManager = $adManager;
        $this->security = $security;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Ad::class === $resourceClass && $operationName === "getMyCarpools";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        /**
         * TO DO : We are not supposed to use userId from request. Only the one from security token.
         * Need to change the method in front and remove the one from the request
         * see : AdVoter
         */
        return $this->adManager->getMyAds($this->request->get("userId", $this->security->getUser()->getId()));
    }
}
