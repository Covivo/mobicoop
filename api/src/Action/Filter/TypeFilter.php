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

namespace App\Action\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use App\Action\Entity\Action;
use LogicException;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class TypeFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "type") {
            return;
        }

        if (!array_key_exists($value, Action::TYPE_FILTER)) {
            throw new LogicException("Unknown type. Should be in ['".implode("','", array_keys(Action::TYPE_FILTER))."']");
        }

        // we will create a new querybuilder for retrieving the solidary users, to avoid modifying the one used for the original query
        $em = $queryBuilder->getEntityManager();

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
        ->where($alias.'.type in ('."'".implode("','", Action::TYPE_FILTER[$value])."'".')');
        return;
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
                'type' => 'string',
                'required' => false,
                'enum' => "{'".implode("','", array_keys(Action::TYPE_FILTER))."'}",
                'swagger' => [
                    'description' => 'Filter on Action to get only a specific type ['.implode("','", array_keys(Action::TYPE_FILTER)).']',
                    'name' => 'type',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }
}
