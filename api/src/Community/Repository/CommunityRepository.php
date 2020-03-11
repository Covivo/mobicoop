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

namespace App\Community\Repository;

use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\User\Entity\User;

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
     *
     * @param integer $id
     * @return Community|null
     */
    public function find(int $id): ?Community
    {
        return $this->repository->find($id);
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
     * Find available communities for a user
     * Available communities = communities free of registration or communities where the user is registered
     *
     * @return void
     */
    public function findAvailableCommunitiesForUser(?User $user)
    {
        if ($user) {
            return $this->repository->createQueryBuilder('c')
            ->leftJoin('c.communityUsers', 'cu')
            ->leftJoin('c.communitySecurities', 'cs')
            ->where("cs.id is null OR (cu.user = :user AND cu.status = :status)")
            ->setParameter('user', $user)
            ->setParameter('status', CommunityUser::STATUS_ACCEPTED_AS_MEMBER or CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)
            ->getQuery()->getResult();
        }
        return $this->repository->createQueryBuilder('c')
        ->leftJoin('c.communityUsers', 'cu')
        ->leftJoin('c.communitySecurities', 'cs')
        ->where("cs.id is null")
        ->getQuery()->getResult();
    }

    /**
     * Find communities where the given user is registered
     *
     * @param User $user
     * @param boolean|null $proposalsHidden
     * @param boolean|null $membersHidden
     * @param array|null $memberStatuses
     * @return void
     */
    public function findByUser(User $user, ?bool $proposalsHidden=null, ?bool $membersHidden=null, ?array $memberStatuses=null)
    {
        $query = $this->repository->createQueryBuilder('c')
        ->join('c.communityUsers', 'cu')
        ->where('cu.user = :user')
        ->setParameter('user', $user);
        if (!is_null($proposalsHidden)) {
            $query->andWhere('c.proposalsHidden = :proposalsHidden')
            ->setParameter('proposalsHidden', $proposalsHidden);
        }
        if (!is_null($membersHidden)) {
            $query->andWhere('c.membersHidden = :membersHidden')
            ->setParameter('membersHidden', $membersHidden);
        }
        if (!is_null($memberStatuses) && is_array($memberStatuses)) {
            $query->andWhere('cu.status in (' . implode(',', $memberStatuses) . ')');
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Find if a user is registered in a given community
     *
     * @param Community $community
     * @param User $user
     *
     * @return void
     */
    public function isRegistered(Community $community, User $user)
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
     * Find if a user is registered in a given community (using id's)
     *
     * @param int $communityId
     * @param int $userId
     *
     * @return void
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
     *Get communities owned by the user
     *
     * @param Int $userId
     * @return void
     */
    public function getOwnedCommunities(Int $userId)
    {
        $query = $this->repository->createQueryBuilder('c')
        ->where('c.user = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()->getResult();
        return $query;
    }
}
