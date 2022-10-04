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

namespace App\Geography\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\Geography\Entity\Territory;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/*
  We use this extension for add a filter on territory on the GET request and for the listing territories
  In admin : we can only get and set the territories (for ex when we create an user ), to which we belong
  If we dont belong to a territory : we can get all territories
*/

final class TerritoryTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
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
        $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        // concerns only Territory resource, and User users (not Apps)
        if (Territory::class !== $resourceClass || (null === $user = $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }

        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            switch ($operationName) {
                case 'get':
                    $territories = $this->authManager->getTerritoriesForItem('territory_list');
            }
        }

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $where = '(';
            /**
             * @var Territory $territory
             */
            foreach ($territories as $territory) {
                if ('(' != $where) {
                    $where .= ' OR ';
                }
                $territoryFrom = 'territory'.$territory;
                $where .= sprintf('%s.id = %s', $rootAlias, $territory);
            }
            $where .= ')';
            $queryBuilder->andWhere($where);
        }
    }
}
