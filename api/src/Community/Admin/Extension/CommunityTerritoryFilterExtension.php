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

namespace App\Community\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Auth\Service\AuthManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Extension used to limit the list of communities to the territories allowed for the requester (admin).
 */
final class CommunityTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $authManager;
    private $security;

    public function __construct(Security $security, AuthManager $authManager, RequestStack $request)
    {
        $this->authManager = $authManager;
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // concerns only admin get collection
        if (Community::class == $resourceClass && 'ADMIN_get' == $operationName) {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        $territories = [];
        $user = $this->security->getUser();

        // we check if the user has limited territories
        $territories = $this->authManager->getTerritoriesForItem('community_list');

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin($rootAlias.'.address', 'actfe')
                ->leftJoin('actfe.territories', 'tctfe')
                ->leftjoin($rootAlias.'.communityUsers', 'c')
                ->andWhere('tctfe.id in (:territories) OR (c.user = :user AND c.status = :status)')
                ->setParameter('territories', $territories)
                ->setParameter('user', $user)
                ->setParameter('status', CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)
            ;
        }
    }
}
