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
 */

namespace App\Community\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\User\Entity\User as EntityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CommunityUserRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EntityRepository
     */
    private $repository;
    private $collectionExtensions;
    private $communityNbLastUser;

    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions, int $communityNbLastUser)
    {
        $this->_em = $entityManager;
        $this->repository = $entityManager->getRepository(CommunityUser::class);
        $this->collectionExtensions = $collectionExtensions;
        $this->communityNbLastUser = $communityNbLastUser;
    }

    /**
     * Find communities by criteria.
     *
     * @return array
     */
    public function findBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Find community users for a given community.
     *
     * @param Community $community The community
     *
     * @return array The members
     */
    public function findForCommunity(Community $community, array $context = [], string $operationName): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('cu');
        $query->where('cu.community = :community')
            ->andWhere('cu.status = :accepted_as_moderator or cu.status = :accepted_as_member')
            ->join('cu.user', 'u')
            ->setParameter('community', $community)
            ->setParameter('accepted_as_moderator', CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)
            ->setParameter('accepted_as_member', CommunityUser::STATUS_ACCEPTED_AS_MEMBER)
        ;

        // Sort and Filters
        if (isset($context['filters'])) {
            // Filters
            $excludedFilters = ['page', 'perPage', 'order'];
            foreach ($context['filters'] as $filter => $value) {
                if (!in_array($filter, $excludedFilters)) {
                    switch ($filter) {
                        case 'givenName':
                        case 'familyName':$query->andWhere('u.'.$filter." like '%".$value."%'");

                            break;

                        default: $query->andWhere('cu.'.$filter." like '%".$value."%'");
                    }
                }
            }

            // Sort
            if (isset($context['filters']['order'])) {
                foreach ($context['filters']['order'] as $sort => $order) {
                    switch ($sort) {
                        case 'givenName':
                        case 'familyName':$query->addOrderBy('u.'.$sort, $order);

                            break;

                        default: $query->addOrderBy('cu.'.$sort, $order);
                    }
                }
            }
        }

        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, CommunityUser::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(CommunityUser::class, $operationName)) {
                return $extension->getResult($query, CommunityUser::class, $operationName);
            }
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get accepted members by their id if they accept emailing.
     *
     * @param array $ids The ids of the users
     *
     * @return null|array The users
     */
    public function findAcceptedDeliveriesByIds(array $ids)
    {
        return $this->repository->createQueryBuilder('cu')
            ->join('cu.user', 'u')
            ->where('cu.id IN(:ids) and u.newsSubscription=1 and cu.status IN (:statuses)')
            ->setParameter('ids', $ids)
            ->setParameter('statuses', [CommunityUser::STATUS_ACCEPTED_AS_MEMBER, CommunityUser::STATUS_ACCEPTED_AS_MODERATOR])
            ->getQuery()->getResult();
    }

    /**
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

    public function findUserCommunities(EntityUser $user)
    {
        $query = 'SELECT tcu_extended.user_id, GROUP_CONCAT(tcu_extended.Communauté1) as Communauté1, GROUP_CONCAT(tcu_extended.Communauté2) as Communauté2, GROUP_CONCAT(tcu_extended.Communauté3) as Communauté3 FROM ( SELECT tcu.user_id, case when tcu.OrdreCommunaute = 1 then tcu.NomCommunaute end as Communauté1, case when tcu.OrdreCommunaute = 2 then tcu.NomCommunaute end as Communauté2, case when tcu.OrdreCommunaute = 3 then tcu.NomCommunaute end as Communauté3 FROM ( SELECT cu.user_id, ROW_NUMBER() OVER ( PARTITION BY cu.user_id ORDER BY cu.accepted_date ASC ) as OrdreCommunaute, c.name as NomCommunaute, cu.accepted_date as DateAcceptationCommunaute FROM community_user cu inner join community c on c.id = cu.community_id WHERE cu.accepted_date is not null AND cu.user_id = :user GROUP by cu.id ORDER BY cu.accepted_date ) as tcu ) as tcu_extended GROUP BY tcu_extended.user_id';

        $conn = $this->_em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute(['user' => $user->getId()]);

        return $stmt->fetch();
    }
}
