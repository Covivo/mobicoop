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
use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 *  Extension used to add an automatic filter to the admin get collection request
 *  In admin, one can only manage and see the users that belong to its territories
 *  We check the user's home address, and the origin / destination of its proposals.
 */
final class UserTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authManager;

    public function __construct(Security $security, AuthManager $authManager)
    {
        $this->security = $security;
        $this->authManager = $authManager;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null)
    {
        if (User::class == $resourceClass && in_array($operationName, ['ADMIN_get', 'ADMIN_associate_campaign', 'ADMIN_send_campaign'])) {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = [])
    {
        if (User::class == $resourceClass && 'ADMIN_get' == $operationName) {
            $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, ?string $operationName = null, array $identifiers = [], array $context = []): void
    {
        // concerns only User resource, and User users (not Apps)
        if ((null === $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }

        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            switch ($operationName) {
                case 'ADMIN_get':
                case 'ADMIN_associate_campaign':
                case 'ADMIN_send_campaign':
                    $territories = $this->authManager->getTerritoriesForItem('user_list');
            }
        }
        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin($rootAlias.'.addresses', 'autfe')
                ->leftJoin('autfe.territories', 'atutfe')
                ->andWhere('(autfe.home = 1 AND atutfe.id in (:territories))')
                ->setParameter('territories', $territories)
            ;
        }
    }
}
