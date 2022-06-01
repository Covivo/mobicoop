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

namespace App\Community\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Auth\Service\AuthManager;
use App\Community\Entity\Community;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * Extension for get the owned community for a referent in admin
 * We use this extension to get only the owned communities for those who are community_restrict.
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 */
final class CommunityOwnedForReferentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $authManager;
    private $security;

    public function __construct(Security $security, AuthManager $authManager)
    {
        $this->security = $security;
        $this->authManager = $authManager;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        // check if we are coming from admin (display all if we are in front, no matter what roles) and if user is community_restrict
        if (Community::class !== $resourceClass
             || !$this->authManager->isAuthorized('community_restrict')
             || 'manage' != $operationName) {
            return;
        }
        $user = $this->security->getUser();

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->orWhere(sprintf('%s.user = :current_user', $rootAlias))
            ->leftJoin(sprintf('%s.communityUsers', $rootAlias), 'c')
            ->orWhere(' c.user = :current_user AND c.status = 2')
        ;
        $queryBuilder->setParameter('current_user', $user->getId());
    }
}
