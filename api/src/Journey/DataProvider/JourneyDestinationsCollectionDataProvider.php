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

namespace App\Journey\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Journey\Entity\Journey;
use App\Journey\Service\JourneyManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for destinations from a given origin.
 *
 */
final class JourneyDestinationsCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $journeyManager;
    protected $security;
    protected $request;
    
    public function __construct(JourneyManager $journeyManager, RequestStack $requestStack, Security $security)
    {
        $this->journeyManager = $journeyManager;
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Journey::class === $resourceClass && $operationName === "destinations";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        return $this->journeyManager->getDestinationsForOrigin($this->request->get('origin'), $operationName, $context);
    }
}
