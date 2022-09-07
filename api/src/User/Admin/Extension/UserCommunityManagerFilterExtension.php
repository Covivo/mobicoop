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

namespace App\User\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Auth\Service\AuthManager;
use App\Community\Entity\CommunityUser;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 *  Extension used to add an automatic filter to the admin get collection request for community managers
 *  Non-admin community managers can only manage and see the users that belong to their communities.
 */
final class UserCommunityManagerFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authManager;

    public function __construct(Security $security, AuthManager $authManager)
    {
        $this->security = $security;
        $this->authManager = $authManager;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // concerns only admin get and campaigns collections
        if (User::class == $resourceClass && in_array($operationName, ['ADMIN_get', 'ADMIN_associate_campaign', 'ADMIN_send_campaign'])) {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        if ($this->authManager->isAuthorized('ROLE_ADMIN')) {
            // user is admin => not concerned
            return;
        }

        if ($this->authManager->isAuthorized('ROLE_SOLIDARY_OPERATOR')) {
            // user is solidary operator => not concerned
            return;
        }

        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->join(sprintf('%s.communityUsers', $rootAlias), 'ucmfe_cu')
            ->join(sprintf('%s.communityUsers', $rootAlias), 'ucmfe_cu2')
            ->andWhere('ucmfe_cu.user = :user AND ucmfe_cu.status = :status AND ucmfe_cu2.community = ucmfe_cu.community')
        ;
        $queryBuilder->setParameter('user', $user);
        $queryBuilder->setParameter('status', CommunityUser::STATUS_ACCEPTED_AS_MODERATOR);
    }
}
