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

namespace App\Community\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class TerritoryFilter extends AbstractContextAwareFilter
{
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["{$property}"] = [
                'property' => $property,
                'type' => 'array',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on communities that have its address in the given territory',
                    'name' => 'territory',
                    'type' => 'array',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('territory' != $property) {
            return;
        }

        $territories = [];

        // the territory filter can be a single value or an array
        if (is_array($value)) {
            // we sanitize the values to be sure it's an int and not an iri
            foreach ($value as $id) {
                if (strrpos($id, '/')) {
                    $territories[] = substr($id, strrpos($id, '/') + 1);
                } else {
                    $territories[] = $id;
                }
            }
        } else {
            // we sanitize the value to be sure it's an int and not an iri
            if (strrpos($value, '/')) {
                $territories[] = substr($value, strrpos($value, '/') + 1);
            }
        }

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->leftJoin(sprintf('%s.address', $rootAlias), 'a');
            $where = '(';

            /**
             * @var Territory $territory
             */
            foreach ($territories as $territory) {
                if ('(' != $where) {
                    $where .= ' OR ';
                }
                $territoryFrom = 'territory'.$territory;
                $queryBuilder->leftJoin('a.territories', $territoryFrom);
                $where .= sprintf('%s.id = %s', $territoryFrom, $territory);
            }
            $where .= ')';
            $queryBuilder
                ->andWhere($where)
            ;
        }
    }
}
