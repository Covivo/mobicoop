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

namespace App\Gamification\Repository;

use App\Gamification\Entity\Reward;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Gamification : Reward Repository
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RewardRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Reward::class);
    }

    public function find(int $id): ?Reward
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Reward
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get the Rewards that has not been notified yet
     *
     * @var User $user  The User we want to get the Rewards in waiting
     * @return array|null
     */
    public function findWaiting(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('r')
        ->where('r.notifiedDate is null')
        ->andWhere('r.user = :user')
        ->setParameter('user', $user)
        ;
                
        return $query->getQuery()->getResult();
    }
}
