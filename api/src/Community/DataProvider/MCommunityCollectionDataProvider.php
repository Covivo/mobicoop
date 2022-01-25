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

namespace App\Community\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Entity\Community;
use App\Community\Resource\MCommunity;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for MCommunity resource.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class MCommunityCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $communityManager;
    private $security;
    private $collectionFilters;

    public function __construct(RequestStack $requestStack, CommunityManager $communityManager, Security $security, iterable $collectionFilters)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
        $this->security = $security;
        $this->collectionFilters = $collectionFilters;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MCommunity::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): ?array
    {
        $queryBuilder = $this->communityManager->getMCommunitiesRequest($this->security->getUser(), $this->request->get('userEmail'));

        // We're browsing every available filters
        foreach ($this->collectionFilters as $collectionFilter) {
            $collectionFilter->applyToCollection($queryBuilder, new QueryNameGenerator(), Community::class, $operationName, $context);

            if ($collectionFilter instanceof QueryResultCollectionExtensionInterface && $collectionFilter->supportsResult(Community::class, $operationName)) {
                $communities = $collectionFilter->getResult($queryBuilder, Community::class, $operationName);
            }
        }

        return $this->communityManager->getMCommunities($this->request->get('userEmail'), $communities);
    }
}
