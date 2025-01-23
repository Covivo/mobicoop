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

namespace App\Solidary\Admin\Extension;

use App\Solidary\Service\TerritoryOperatorManager;
use Doctrine\ORM\QueryBuilder;

abstract class SolidaryTerritory
{
    /**
     * @var TerritoryOperatorManager
     */
    private $_territoryOperatorManager;

    public function __construct(TerritoryOperatorManager $territoryOperatorManager)
    {
        $this->_territoryOperatorManager = $territoryOperatorManager;
    }

    public function addWhere(QueryBuilder $queryBuilder)
    {
        $territories = $this->_territoryOperatorManager->getOperatorTerritories([]);

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin($rootAlias.'.user', 'su')
                ->leftJoin('su.addresses', 'autfe')
                ->leftJoin('autfe.territories', 'atutfe')
                ->andWhere('(autfe.home = 1 AND atutfe.id in (:territories))')
                ->setParameter('territories', $territories)
            ;
        }
    }
}
