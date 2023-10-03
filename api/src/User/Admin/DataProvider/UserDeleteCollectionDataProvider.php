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

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Collection data provider for users in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class UserDeleteCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public const OPERATION_NAME = 'ADMIN_delete_filtered';

    public const MAX_RESULTS = 999999;

    private $_collectionExtensions;
    private $_em;
    private $_managerRegistry;
    private $_userManager;

    public function __construct(
        EntityManagerInterface $em,
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions,
        UserManager $userManager
    ) {
        $this->_collectionExtensions = $collectionExtensions;
        $this->_em = $em;
        $this->_managerRegistry = $managerRegistry;
        $this->_userManager = $userManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && self::OPERATION_NAME === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $manager = $this->_managerRegistry->getManagerForClass($resourceClass);

        /**
         * @var EntityRepository $repository
         */
        $repository = $manager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('u');
        // We exclude pseudoynymized users
        $queryBuilder->where('u.status != :status');
        $queryBuilder->setParameter('status', User::STATUS_PSEUDONYMIZED);
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->_collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            if ($extension instanceof PaginationExtension) {
                $queryBuilder->setMaxResults(self::MAX_RESULTS);
            }

            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $users = $extension->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        foreach ($users as $user) {
            $this->_userManager->deleteUser($user);
        }

        return [];
    }
}
