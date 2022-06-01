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

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
  *  Filters for get users who have the origin of their travels in the given adresses
  *
  *  Here we have origin encoded in a json string (name, lgt, ltd)
  *  For the range go in RadiusRangeFilter, we declare here the parameters to fill (:range) and we set the value in this filter
  *
  * @author Julien Deschampt <julien.deschampt@mobicoop.org>
*/

final class ODRangeOriginFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "origin") {
            return;
        }

        // we decode the json we received for get Latitude and Longitude
        $value = json_decode($value);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(sprintf("%s.proposals", $rootAlias), 'pori')
            ->leftJoin('pori.waypoints', 'wori')
            ->leftJoin('wori.address', 'aori')
            ->andWhere('pori.private <> 1 AND wori.position = 0 AND acos(sin(aori.latitude * 0.0175) * sin('.$value->lat.' * 0.0175) 
                + cos(aori.latitude * 0.0175) * cos('.$value->lat.' * 0.0175) *    
                cos(('.$value->lgt.' * 0.0175) - (aori.longitude * 0.0175))
            ) * 6371 <= :range')// Origin of proposal;
            ->setParameter('range', 1);

        /* Uncomment for also set the home adresse in check
        ->leftJoin('u.addresses', 'homeAddress')
            ->orWhere('homeAddress.home=1 AND acos(sin(homeAddress.latitude * 0.0175) * sin(:latitude * 0.0175)
            + cos(homeAddress.latitude * 0.0175) * cos(:latitude * 0.0175) *
              cos((:longitude * 0.0175) - (homeAddress.longitude * 0.0175))
           ) * 6371 <= :range') */
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
                    'description' => 'Filter on users that have have the origin of their travels in the given adresses',
                    'name' => 'ODRangeOrigin',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }
}
