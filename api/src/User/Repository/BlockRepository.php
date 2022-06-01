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

namespace App\User\Repository;

use App\User\Entity\Block;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BlockRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Block::class);
    }

    /**
     * Find one Blocks by its id.
     */
    public function find(int $id): ?Block
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the Blocks.
     *
     * @return Block[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Find All the Blocks by criteria.
     *
     * @param null|mixed $limit
     * @param null|mixed $offset
     *
     * @return Block[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find one Blocks by criteria.
     */
    public function findOneBy(array $criteria): ?Block
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find all the blocks involving $user1 and $user2.
     */
    public function findAllByUsersInvolved(User $user1, User $user2): array
    {
        $query = $this->repository->createQueryBuilder('b')
            ->where('(b.user = :user1 and b.blockedUser = :user2) or (b.user = :user2 and b.blockedUser = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
        ;

        return $query->getQuery()->getResult();
    }
}
