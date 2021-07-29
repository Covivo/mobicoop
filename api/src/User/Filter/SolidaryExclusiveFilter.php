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
 **************************/

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use DateTime;
use Doctrine\ORM\QueryBuilder;

final class SolidaryExclusiveFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "solidaryExclusive") {
            return;
        }

        if ($value === 1 || $value === 'true') {
            $now = new DateTime();
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin(sprintf("%s.proposals", $rootAlias), 'pSolidaryExclusive')
                ->leftJoin('pSolidaryExclusive.criteria', 'c')
                ->andWhere('c.solidaryExclusive=1 and (
                    (c.frequency = 1 and c.fromDate >= :now) or
                    (c.frequency > 1 and c.fromDate <= :now and c.toDate >= :now)
                )')
                ->setParameter('now', $now->format('Y-m-d'));
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
                'type' => 'boolean',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on users that have active solidary exclusive ads',
                    'name' => 'solidaryExclusive',
                    'type' => 'boolean',
                ],
            ];
        }

        return $description;
    }
}
