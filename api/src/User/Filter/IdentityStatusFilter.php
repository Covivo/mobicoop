<?php
/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\User\Entity\IdentityProof;
use Doctrine\ORM\QueryBuilder;

final class IdentityStatusFilter extends AbstractContextAwareFilter
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
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter for identity status',
                    'name' => 'identityStatus',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('identityStatus' != $property) {
            return;
        }

        $where = '';

        if (is_array($value)) {
            $where = '(u.identityStatus IN (';
            $statuses = [];
            $hasNone = '';
            foreach ($value as $val) {
                if (IdentityProof::STATUS_NONE == $val) {
                    $hasNone = ' or u.identityStatus IS NULL';
                }
                $statuses[] = $val;
            }
            $where .= implode(',', $statuses).')'.$hasNone.')';
        } else {
            $where = "(u.identityStatus = {$value})";
        }

        $queryBuilder->andWhere($where);
    }
}
