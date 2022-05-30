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

namespace App\Community\Repository;

use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class CommunityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Community::class);
    }

    /**
     * Find community by id.
     */
    public function find(int $id): ?Community
    {
        return $this->repository->find($id);
    }

    /**
     * Find All communities.
     *
     * @return null|Community
     */
    public function findAll(): ?Community
    {
        return $this->repository->findAll();
    }

    /**
     * Find communities by criteria.
     *
     * @return null|Community
     */
    public function findBy(array $criteria): ?Community
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Find One community by criteria.
     */
    public function findOneBy(array $criteria): ?Community
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find available communities for a user
     * Available communities = communities free of registration or communities where the user is registered.
     *
     * @param null|User  $user    The user
     * @param null|array $orderBy The order of the results
     *
     * @return QueryBuilder
     */
    public function findAvailableCommunitiesForUser(?User $user, ?array $orderBy = null): QueryBuilder
    {
        if ($user) {
            $query = $this->repository->createQueryBuilder('c')
                ->leftJoin('c.communityUsers', 'cu')
                ->leftJoin('c.communitySecurities', 'cs')
                ->where('cs.id is null OR (cu.user = :user AND cu.status = :status)')
                ->setParameter('user', $user)
                ->setParameter('status', CommunityUser::STATUS_ACCEPTED_AS_MEMBER or CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)
            ;
            if (is_array($orderBy)) {
                foreach ($orderBy as $sort => $order) {
                    $query->addOrderBy($sort, $order);
                }
            }

            return $query;
        }

        $query = $this->repository->createQueryBuilder('c')
            ->leftJoin('c.communityUsers', 'cu')
            ->leftJoin('c.communitySecurities', 'cs')
            ->where('cs.id is null')
        ;
        if (is_array($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $query->addOrderBy($sort, $order);
            }
        }

        return $query;
    }

    /**
     * Find communities where the given user is registered.
     */
    public function findByUser(User $user, ?bool $proposalsHidden = null, ?bool $membersHidden = null, ?array $memberStatuses = null)
    {
        $query = $this->repository->createQueryBuilder('c')
            ->join('c.communityUsers', 'cu')
            ->where('cu.user = :user')
            ->setParameter('user', $user)
        ;
        if (!is_null($proposalsHidden)) {
            $query->andWhere('c.proposalsHidden = :proposalsHidden')
                ->setParameter('proposalsHidden', $proposalsHidden)
            ;
        }
        if (!is_null($membersHidden)) {
            $query->andWhere('c.membersHidden = :membersHidden')
                ->setParameter('membersHidden', $membersHidden)
            ;
        }
        if (!is_null($memberStatuses) && is_array($memberStatuses)) {
            $query->andWhere('cu.status in ('.implode(',', $memberStatuses).')');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get communities owned by the user.
     *
     * @param int $userId The user id
     *
     * @return array
     */
    public function getOwnedCommunities(int $userId): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();
    }

    /**
     * Find if a user is registered in a given community.
     *
     * @return bool
     */
    public function isRegistered(Community $community, User $user): bool
    {
        $result = $this->repository->createQueryBuilder('c')
            ->join('c.communityUsers', 'cu')
            ->where('cu.user = :user and cu.community = :community')
            ->setParameter('user', $user)
            ->setParameter('community', $community)
            ->getQuery()->getResult();
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Find if a user is registered in a given community (using id's).
     */
    public function isRegisteredById(int $communityId, int $userId)
    {
        $result = $this->repository->createQueryBuilder('c')
            ->join('c.communityUsers', 'cu')
            ->where('cu.user = :user and cu.community = :community')
            ->setParameter('user', $userId)
            ->setParameter('community', $communityId)
            ->getQuery()->getResult();
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user is a referrer.
     *
     * @param User      $user      The user id
     * @param Community $community The community to exclude from the check
     *
     * @return bool True if the user is referrer, false otherwise
     */
    public function isReferrer(User $user, Community $community): bool
    {
        $query = $this->repository->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.id <> :id')
            ->setParameter('user', $user)
            ->setParameter('id', $community->getId())
        ;
        $communities = $query->getQuery()->getResult();

        return count($communities) > 0;
    }

    /**
     * Get the communities where the user has one of the given statuses.
     *
     * @param User  $user     The user
     * @param array $statuses The statuses
     *
     * @return null|array The communities found
     */
    public function getCommunitiesForUserAndStatuses(User $user, array $statuses): ?array
    {
        return $this->repository->createQueryBuilder('c')
            ->join('c.communityUsers', 'cu')
            ->where('cu.user = :user and cu.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', $statuses)
            ->getQuery()->getResult();
    }

    /**
     * Count communities.
     *
     * @return int
     */
    public function countCommunities(): int
    {
        $query = $this->repository->createQueryBuilder('c')
            ->select('count(c.id)')
        ;

        return $query->getQuery()->getSingleScalarResult();
    }
}
