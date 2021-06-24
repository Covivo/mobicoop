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

namespace App\Event\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\Event\Entity\Event;
use App\Geography\Entity\Territory;
use App\Auth\Service\AuthManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

final class EventTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authManager;
    private $request;

    public function __construct(Security $security, AuthManager $authManager, RequestStack $request)
    {
        $this->security = $security;
        $this->authManager = $authManager;
        $this->request = $request->getCurrentRequest();
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
        // concerns only Event resource, and User users (not Apps)
        if (Event::class !== $resourceClass || (null === $user = $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }

        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            if ($this->request->get("showAllEvents")=="" || !$this->request->get("showAllEvents")) {
            } else {
                switch ($operationName) {
                    case "get":
                        $territories = $this->authManager->getTerritoriesForItem("event_list");
                }
            }
        }
        

        if (count($territories)>0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->leftJoin(sprintf("%s.address", $rootAlias), 'a');
            $where = "(";
            foreach ($territories as $territory) {
                if ($where != '(') {
                    $where .= " OR ";
                }
                $territoryFrom = 'territory'.$territory;
                $queryBuilder->leftJoin('a.territories', $territoryFrom);
                $where .= sprintf("%s.id = %s", $territoryFrom, $territory);
            }
            $where .= ")";
            $queryBuilder
            ->andWhere($where);
        }
    }
}
