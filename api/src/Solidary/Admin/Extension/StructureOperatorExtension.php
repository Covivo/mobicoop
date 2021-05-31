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
 **************************/

namespace App\Solidary\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use App\Auth\Service\AuthManager;
use App\User\Entity\User;
use App\Solidary\Entity\Operate;
use App\Solidary\Entity\Structure;

/**
 * Extension used to limit the list of structures to the ones where the requester is operator.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class StructureOperatorExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
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
        if ($resourceClass == Structure::class && $operationName === 'ADMIN_get') {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        if ($resourceClass == Structure::class && $operationName === 'ADMIN_get') {
            $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        // exclude pure admins
        if ($this->authManager->isAuthorized('ROLE_ADMIN')) {
            return;
        }

        // get the list of structures id where the requester is operator
        $ids = [];
        foreach ($user->getOperates() as $operate) {
            /**
             * @var Operate $operate
             */
            $ids[] = $operate->getStructure()->getId();
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
        ->andWhere(sprintf("%s.id IN (:ids)", $rootAlias))
        ->setParameter('ids', $ids);
    }
}
