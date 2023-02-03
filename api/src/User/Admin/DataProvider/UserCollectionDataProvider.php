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
 */

namespace App\User\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Collection data provider for users in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class UserCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionExtensions;
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && 'ADMIN_get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);

        /**
         * @var EntityRepository $repository
         */
        $repository = $manager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('u');
        // We exclude pseudoynymized users
        $queryBuilder->where('u.status != :status');
        $queryBuilder->setParameter('status', User::STATUS_PSEUDONYMIZED);
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $users = $extension->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        return $users;
    }
}
