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

namespace App\Community\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\Community\Entity\Community;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use App\User\Service\UserManager;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Entity\AuthItem;


/**
 * Extension for get the owned community for a referent in admin
 * We use this extension to get only the owned communities for a referent and for the admin (accessAdmin), for those who have ROLE_ADMIN roles we get all
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 *
 */

final class CommunityOwnedForReferentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $userManager;
    private $authItemRepository;


    public function __construct(Security $security,UserManager $userManager, AuthItemRepository $authItemRepository)
    {
        $this->security = $security;
        $this->userManager = $userManager;
        $this->authItemRepository = $authItemRepository;

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
        $authItemAdmin = $this->authItemRepository->find(AuthItem::ROLE_ADMIN);
        $authItemSuperAdmin = $this->authItemRepository->find(AuthItem::ROLE_SUPER_ADMIN);

        // concerns only Community resource, and User users (not Apps),
        // check also if we are coming from admin (display all if we are in front, no matter what roles) and if we are not admin
        if (Community::class !== $resourceClass || (null === $user = $this->security->getUser()) ||
             $user instanceof App ||
             $this->userManager->checkUserHaveAuthItem($user, $authItemAdmin) ||
             $this->userManager->checkUserHaveAuthItem($user, $authItemSuperAdmin) ||
             $operationName !='accessAdmin') {
            return;
        }


        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.user = :current_user', $rootAlias));
        $queryBuilder->setParameter('current_user', $user->getId());
    }
}
