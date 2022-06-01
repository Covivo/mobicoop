<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class LocalityFilter extends AbstractContextAwareFilter
{
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            switch ($property) {
                case 'originLocality':
                    $description['originLocality'] = [
                        'property' => $property,
                        'type' => 'string',
                        'required' => false,
                        'swagger' => [
                            'description' => 'originLocality',
                            'name' => 'originLocality',
                            'type' => 'string',
                        ],
                    ];

                    break;

                case 'destinationLocality':
                    $description['destinationLocality'] = [
                        'property' => $property,
                        'type' => 'string',
                        'required' => false,
                        'swagger' => [
                            'description' => 'destinationLocality',
                            'name' => 'destinationLocality',
                            'type' => 'string',
                        ],
                    ];

                    break;
            }
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        switch ($property) {
            case 'originLocality':
                $queryBuilder
                    ->join('o.points', 'startPoint')
                    ->join('startPoint.address', 'startAddress')
                    ->andWhere('startPoint.position = 0')
                    ->andWhere('startAddress.addressLocality = :originLocality')
                    ->setParameter('originLocality', $value)
                ;

                break;

            case 'destinationLocality':
                $queryBuilder
                    ->join('o.points', 'destinationPoint')
                    ->join('destinationPoint.address', 'destinationAddress')
                    ->andWhere('destinationPoint.lastPoint = 1')
                    ->andWhere('destinationAddress.addressLocality = :destinationLocality')
                    ->setParameter('destinationLocality', $value)
                ;

                break;
        }
    }
}
