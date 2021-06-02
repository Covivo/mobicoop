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

final class HomeAddressODTerritoryFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "homeAddressODTerritory") {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(sprintf("%s.addresses", $rootAlias), 'homeAddress')
            ->leftJoin('homeAddress.territories', 'thaodtf')
            ->leftJoin(sprintf("%s.proposals", $rootAlias), 'phaodtf')
            ->leftJoin('phaodtf.waypoints', 'whaodtf')
            ->leftJoin('whaodtf.address', 'ahaodtf')
            ->leftJoin('ahaodtf.territories', 'tahaodtf')
        ;

        if (is_array($value)) {
            // Multiple territories
            $queryBuilder
            ->andWhere(sprintf('((tahaodtf.id in (%s) AND phaodtf.private <> 1 AND (whaodtf.position=0 OR whaodtf.destination=1)) OR (thaodtf.id in (%s) AND homeAddress.home=1))', implode(",", $value), implode(",", $value)));
        } else {
            // One territory
            $queryBuilder
            ->andWhere(sprintf('((tahaodtf.id = %s AND phaodtf.private <> 1 AND (whaodtf.position=0 OR whaodtf.destination=1)) OR (thaodtf.id = %s AND homeAddress.home=1))', $value, $value));
        }
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
                    'description' => 'Filter on users that have the origin or the destination of one of their Ad, or their home address, in the given territory',
                    'name' => 'territory',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }
}
