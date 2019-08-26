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

final class ProposalTerritoryFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "proposalTerritory") {
            return;
        }

        // we sanitize the value to be sure it's an int and not an iri
        if (strrpos($value, '/')) {
            $value = substr($value, strrpos($value, '/') + 1);
        }
        
        $queryBuilder
            ->select('u')
            ->from('\App\User\Entity\User', 'u')
            ->join('\App\Carpool\Entity\Proposal', 'p', 'WITH', 'p.user_id = u.id')
            ->join('\App\Carpool\Entity\Criteria', 'c', 'WITH', 'p.criteria_id = c.id')
            ->join('\App\Geography\Entity\Direction', 'd', 'WITH', 'c.direction_driver_id = d.id OR c.direction_passenger_id = d.id')
            ->join('\App\Geography\Entity\Territory', 'proposalTerritory')
            ->andWhere(sprintf('proposalTerritory.id = %s AND ST_INTERSECTS(proposalTerritory.geoJsonDetail,d.geoJsonDetail)=1', $value));
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
                    'description' => 'Filter on users that have a proposal in the given territory',
                    'name' => 'proposalTerritory',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }
}
