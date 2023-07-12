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
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;

final class HitchHikerFilter extends AbstractContextAwareFilter
{
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => 'boolean',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on users that are hitch hikers',
                    'name' => 'isHitchHiker',
                    'type' => 'boolean',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('isHitchHiker' != $property) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere('u.status != :status')
            ->setParameter('status', User::STATUS_PSEUDONYMIZED)
        ;

        if (1 === $value || 'true' === $value) {
            $queryBuilder->andWhere($rootAlias.'.hitchHikeDriver is not null or '.$rootAlias.'.hitchHikePassenger is not null');
        } elseif (0 === $value || 'false' === $value) {
            $queryBuilder->andWhere($rootAlias.'hitchHikeDriver is null and '.$rootAlias.'hitchHikePassenger is null');
        }
    }
}
