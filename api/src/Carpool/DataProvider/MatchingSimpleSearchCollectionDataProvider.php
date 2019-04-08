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
use App\Carpool\Entity\Matching;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Carpool\Service\ProposalManager;

/**
 * Collection data provider for Matching simple search.
 * Only for punctual and one way trip.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
final class MatchingSimpleSearchCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    
    
    public function __construct(RequestStack $requestStack, ProposalManager $proposalManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->proposalManager = $proposalManager;
    }
    
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Matching::class === $resourceClass && $operationName === "simple_search";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        return [$this->proposalManager->searchMatchings(
            $this->request->get("origin_latitude"),
            $this->request->get("origin_longitude"),
            $this->request->get("destination_latitude"),
            $this->request->get("destination_longitude"),
            \DateTime::createFromFormat(\DateTime::RFC3339, $this->request->get("date"))
        )];
    }
}
