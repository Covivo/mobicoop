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

namespace App\Rdex\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Rdex\Entity\RdexJourney;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Rdex\Service\RdexManager;

/**
 * Collection data provider for Rdex Journey entity.
 *
 * Automatically associated to Rdex Journey entity thanks to autowiring (see 'supports' method).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
final class JourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $rdexManager;
    protected $request;
    
    public function __construct(RequestStack $requestStack, RdexManager $rdexManager)
    {
        $this->rdexManager = $rdexManager;
        $this->request = $requestStack->getCurrentRequest();
    }
    
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RdexJourney::class === $resourceClass;
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if ($result = $this->rdexManager->validate($this->request)) return [];
        
        return $this->rdexManager->getJourneys($this->request->get("p"));
    }
}
