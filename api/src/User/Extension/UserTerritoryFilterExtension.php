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

namespace App\User\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\User\Entity\User;
use App\Geography\Entity\Territory;
use App\Auth\Service\AuthManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
  *  We use this extension for add a filter on the user on the GET request and for the listing user
  *  In admin : we can only manage and see the user that belong to our territory
  *  For that we check the HOME ADRESSE of the users
  *  If we dont belong to a territory : we can get all users
  *
  * @author Julien Deschampt <julien.deschampt@mobicoop.org>
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
        // concerns only User resource, and User users (not Apps)
        if (User::class !== $resourceClass || (null === $user = $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }

        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            switch ($operationName) {
                case "get":
                    $territories = $this->authManager->getTerritoriesForItem("user_list");
            }
        }

        if (count($territories)>0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->leftJoin(sprintf("%s.addresses", $rootAlias), 'a')
                          ->leftJoin(sprintf("%s.proposals", $rootAlias), 'p')
                          ->leftJoin("p.waypoints", 'w')
                            //Check the HOME address (home =1) OR Waypoint is Origin Or destination
                          ->andWhere("a.home = 1 OR (w.position = 0 OR w.destination = 1)")
                          ->leftJoin("w.address", 'a2');

            $where = "(";
            /**
             * @var Territory $territory
             */
            foreach ($territories as $territory) {

                if ($where != '(') {
                    $where .= " OR ";
                }
                //Check if the home address is in territory
                $territoryFrom = 'territory'.$territory;
                $queryBuilder->leftJoin('a.territories', $territoryFrom);
                $where .= sprintf("%s.id = %s", $territoryFrom, $territory);

                //Check if the proposal address is in territory
                $territoryFromOD = 'territoryod'.$territory;
                $queryBuilder->leftJoin('a2.territories', $territoryFromOD);
                $where .= sprintf(" OR %s.id = %s",  $territoryFromOD, $territory);


            }
            $where .= ")";
            $queryBuilder
            ->andWhere($where);
        }
    }
}
