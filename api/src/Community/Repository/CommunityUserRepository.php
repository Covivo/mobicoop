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
    private $communityNbLastUser;
    
    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions, int $communityNbLastUser)
    {
        $this->repository = $entityManager->getRepository(CommunityUser::class);
        $this->collectionExtensions = $collectionExtensions;
        $this->communityNbLastUser = $communityNbLastUser;
    }

    /**
     * Find communities by criteria
     *
     * @param array $criteria
     * @return array
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
        ->join("cu.user", "u")
        ->setParameter('community', $community);
        
        // Sort and Filters
        if (isset($context["filters"])) {

            // Filters
            $excludedFilters = ["page","perPage","order"];
            foreach ($context["filters"] as $filter => $value) {
                if (!in_array($filter, $excludedFilters)) {
                    switch ($filter) {
                        case "givenName":
                        case "familyName":$query->andWhere("u.".$filter." like '%".$value."%'");
                            break;
                        default: $query->andWhere("cu.".$filter." like '%".$value."%'");
                    }
                }
            }

            // Sort
            if (isset($context["filters"]['order'])) {
                foreach ($context["filters"]['order'] as $sort => $order) {
                    switch ($sort) {
                        case "givenName":
                        case "familyName":$query->addOrderBy("u.".$sort, $order);
                            break;
                        default: $query->addOrderBy("cu.".$sort, $order);
                    }
                }
            }
        }


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

    /**
     * Get accepted members by their id if they accept emailing
     *
     * @param array $ids    The ids of the users
     * @return array|null   The users
     */
    public function findAcceptedDeliveriesByIds(array $ids)
    {
        return $this->repository->createQueryBuilder('cu')
        ->join('cu.user', 'u')
        ->where("cu.id IN(:ids) and u.newsSubscription=1 and cu.status IN (:statuses)")
        ->setParameter('ids', $ids)
        ->setParameter('statuses', [CommunityUser::STATUS_ACCEPTED_AS_MEMBER,CommunityUser::STATUS_ACCEPTED_AS_MODERATOR])
        ->getQuery()->getResult();
    }

    /**
     * @param Community $community
     * @return CommunityUser[]
     */
    public function findNLastUsersOfACommunity(Community $community): array
    {
        return $this->repository->createQueryBuilder('cu')
        ->where('cu.community = :community')
        ->andWhere('cu.status != :pending')
        ->andWhere('cu.status != :refused')
        ->orderBy('cu.createdDate', 'DESC')
        ->setMaxResults($this->communityNbLastUser)
        ->setParameter('community', $community)
        ->setParameter('pending', CommunityUser::STATUS_PENDING)
        ->setParameter('refused', CommunityUser::STATUS_REFUSED)
        ->getQuery()->getResult();
    }
}
