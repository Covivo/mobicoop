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

namespace App\Community\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Entity\Community;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Collection data provider for Community search (by name).
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
final class CommunityIsOwnedByUserCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $communityManager;
    private $security;

    public function __construct(RequestStack $requestStack, CommunityManager $communityManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Community::class === $resourceClass && $operationName === "owned";
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        (!is_null($this->request->get("userId"))) ? $userId = $this->request->get("userId") : $userId = $this->security->getUser()->getId();
        return $this->communityManager->getOwnedCommunities($userId);
    }
}
