<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Entity\Community;
use App\Community\Repository\CommunityRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Community search (by name).
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class CommunityExistsCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $communityRepository;

    public function __construct(RequestStack $requestStack, CommunityRepository $communityRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->communityRepository = $communityRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Community::class === $resourceClass && 'exists' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        if ($this->communityRepository->findBy(['name' => $this->request->get('name')])) {
            throw new \InvalidArgumentException('Community name already used');
        }

        return null;
    }
}
