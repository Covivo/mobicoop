<?php
/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class DirectionTerritoryFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "directionTerritory") {
            return;
        }

        // we sanitize the value to be sure it's an int and not an iri
        if (strrpos($value, '/')) {
            $value = substr($value, strrpos($value, '/') + 1);
        }
        
        // $queryBuilder
        //     ->leftJoin('u.proposals', 'p')
        //     ->leftJoin('p.criteria', 'c')
        //     ->leftJoin('c.directionDriver', 'dd')
        //     ->leftJoin('c.directionPassenger', 'dp')
        //     ->join('\App\Geography\Entity\Territory', 'directionTerritory')
        //     ->andWhere(sprintf('directionTerritory.id = %s AND (ST_INTERSECTS(directionTerritory.geoJsonDetail,dd.geoJsonDetail)=1 OR ST_INTERSECTS(directionTerritory.geoJsonDetail,dp.geoJsonDetail)=1)', $value));

        $queryBuilder
            ->leftJoin('u.proposals', 'p')
            ->leftJoin('p.criteria', 'c')
            ->leftJoin('c.directionDriver', 'dd')
            ->leftJoin('c.directionPassenger', 'dp')
            ->leftJoin('dd.territories', 'td')
            ->leftJoin('dp.territories', 'tp')
            ->andWhere(sprintf('(td.id = %s OR tp.id = %s)', $value, $value));
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                'property' => $property,
                'type' => 'number',
                'format' => 'integer',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on users that have a point of one of their Ad in the given territoryy',
                    'name' => 'proposalTerritory',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }
}
