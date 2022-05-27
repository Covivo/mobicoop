<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityMembersList;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Get the members of a community.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class CommunityMembersGetItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
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
        return Community::class === $resourceClass && 'members' === $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): CommunityMembersList
    {
        return $this->communityManager->getMembers($id, $operationName, $context);
    }
}
