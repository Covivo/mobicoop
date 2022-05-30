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

namespace App\User\Repository;

use App\User\Entity\Review;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ReviewRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Review::class);
    }

    /**
     * Find one Review by its id
     *
     * @return Review|null
     */
    public function find(int $id): ?Review
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the Review
     *
     * @return Review[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Find All the Review by criteria
     *
     * @return Review[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find one Review by criteria
     *
     * @return Review|null
     */
    public function findOneBy(array $criteria): ?Review
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find all reviews involving a User (as reviewer or reviewed)
     *
     * @param User $user
     * @return array|null
     */
    public function findReviewsInvolvingUser(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('r')
        ->where('r.reviewer = :user or r.reviewed = :user')
        ->setParameter('user', $user)
        ;
        return $query->getQuery()->getResult();
    }

    /**
     * Find all reviews with specific reviewer and/or specific reviewed
     *
     * @param User $reviewer The reviewer
     * @param User $reviewed The reviewed
     * @return array|null
     */
    public function findSpecificReviews(User $reviewer=null, User $reviewed=null): ?array
    {
        $query = $this->repository->createQueryBuilder('r');

        if (!is_null($reviewer)) {
            $query->andWhere('r.reviewer = :reviewer');
            $query->setParameter('reviewer', $reviewer);
        }
        if (!is_null($reviewed)) {
            $query->andWhere('r.reviewed = :reviewed');
            $query->setParameter('reviewed', $reviewed);
        }

        return $query->getQuery()->getResult();
    }
}
