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

final class HomeAddressWaypointTerritoryFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "homeAddressWaypointTerritory") {
            return;
        }

        // we sanitize the value to be sure it's an int and not an iri
        if (strrpos($value, '/')) {
            $value = substr($value, strrpos($value, '/') + 1);
        }

        // $queryBuilder
        //     ->leftJoin('u.addresses', 'homeAddress')
        //     ->leftJoin('u.proposals', 'p')
        //     ->leftJoin('p.waypoints', 'w')
        //     ->leftJoin('w.address', 'a')
        //     ->join('\App\Geography\Entity\Territory', 'homeAddressWaypointTerritory')
        //     ->andWhere(sprintf('(homeAddressWaypointTerritory.id = %s AND (ST_INTERSECTS(homeAddressWaypointTerritory.geoJsonDetail,a.geoJson)=1) OR (ST_INTERSECTS(homeAddressWaypointTerritory.geoJsonDetail,homeAddress.geoJson)=1 AND homeAddress.home=1))', $value));

        $queryBuilder
            ->leftJoin('u.addresses', 'homeAddress')
            ->leftJoin('homeAddress.territories', 't')
            ->leftJoin('u.proposals', 'p')
            ->leftJoin('p.waypoints', 'w')
            ->leftJoin('w.address', 'a')
            ->leftJoin('a.territories', 'ta')
            ->andWhere(sprintf('((ta.id = %s AND p.private <> 1) OR (t.id = %s AND homeAddress.home=1))', $value, $value));
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
                    'description' => 'Filter on users that have a proposal or homeAddress in the given territory',
                    'name' => 'territory',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }
}
