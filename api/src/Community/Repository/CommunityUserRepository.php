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

namespace App\Community\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;

class CommunityUserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $collectionExtensions;
    
    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions)
    {
        $this->repository = $entityManager->getRepository(CommunityUser::class);
        $this->collectionExtensions = $collectionExtensions;
    }

    /**
     * Find communities by criteria
     *
     * @param array $criteria
     * @return void
     */
    public function findBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Find community users for a given community
     *
     * @param Community $community  The community
     * @return array The members
     */
    public function findForCommunity(Community $community, array $context = [], string $operationName): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('cu');
        $query->where("cu.community = :community")
        ->setParameter('community', $community);
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, CommunityUser::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(CommunityUser::class, $operationName)) {
                $result = $extension->getResult($query, CommunityUser::class, $operationName);
                return $result;
            }
        }

        return $query->getQuery()->getResult();
    }
}
