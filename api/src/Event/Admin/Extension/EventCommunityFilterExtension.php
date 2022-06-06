<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Event\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Auth\Service\AuthManager;
use App\Community\Admin\Service\CommunityManager;
use App\Event\Entity\Event;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class EventCommunityFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authManager;
    private $request;
    private $communityManager;
    private $communityManagerCanSeeAllEvents;

    public function __construct(Security $security, AuthManager $authManager, RequestStack $request, CommunityManager $communityManager, bool $communityManagerCanSeeAllEvents)
    {
        $this->security = $security;
        $this->authManager = $authManager;
        $this->request = $request->getCurrentRequest();
        $this->communityManager = $communityManager;
        $this->communityManagerCanSeeAllEvents = $communityManagerCanSeeAllEvents;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (!$this->authManager->isAuthorized('event_manage')) {
            return;
        }
        // concerns only admin get collection
        if (Event::class == $resourceClass && 'ADMIN_get' == $operationName) {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        $communities = $this->communityManager->getModerated($this->security->getUser());

        if (count($communities) > 0 && false == $this->communityManagerCanSeeAllEvents) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin($rootAlias.'.community', 'c')
                ->andWhere('c.id in (:communities)')
                ->setParameter('communities', $communities)
        ;
        }
    }
}
