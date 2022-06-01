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

namespace App\RelayPoint\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\Geography\Entity\Territory;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 *  Extension used to add an automatic filter to the "get relaypoints" request
 *  In admin, one can only manage and see the relaypoints that belong to its territories
 *  We check the relay point address.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class RelayPointTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
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
        // this filter only applies to collection

        // $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, string $operationName = null, array $identifiers = [], array $context = []): void
    {
        // concerns only RelayPoint resource, and User users (not Apps)
        if (RelayPoint::class !== $resourceClass || (null === $user = $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }
        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            switch ($operationName) {
                case 'get':
                    $territories = $this->authManager->getTerritoriesForItem('relay_point_list');
            }
        }

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin(sprintf('%s.address', $rootAlias), 'arptfe')
            ;

            $where = '(';

            /**
             * @var Territory $territory
             */
            foreach ($territories as $territory) {
                if ('(' != $where) {
                    $where .= ' OR ';
                }
                // check if the address is in territory
                $territoryFrom = 'territory'.$territory;
                $queryBuilder->leftJoin('arptfe.territories', $territoryFrom);
                $where .= sprintf('%s.id = %s', $territoryFrom, $territory);
            }
            $where .= ')';
            $queryBuilder
                ->andWhere($where)
            ;
        }
    }
}
