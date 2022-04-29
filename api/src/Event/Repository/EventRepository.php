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

namespace App\Event\Repository;

use App\Event\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EventRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Event::class);
        $this->entityManager = $entityManager;
    }

    public function find(int $id): ?Event
    {
        return $this->repository->find($id);
    }

    /**
     * Find event by criteria.
     */
    public function findBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Return all events with the given name and status.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The event found
     */
    public function findByNameAndStatus(string $name, int $status)
    {
        $queryString = "
            SELECT e from App\\Event\\Entity\\Event e
            where (MATCH(e.name) AGAINST('".$name."') > 0) and e.status = ".$status;

        $query = $this->entityManager->createQuery($queryString);

        return $query->getResult();
    }

    /**
     *Get events created by the user.
     */
    public function getCreatedEvents(int $userId)
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getResult();
    }

    public function getEvents(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.status = 1')
            ->andWhere('e.private = false')
        ;
    }

    /**
     * Find One event by criteria.
     */
    public function findOneBy(array $criteria): ?Event
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get all internal events (exclude external events).
     */
    public function getInternalEvents()
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.externalSource is NULL')
            ->andWhere('e.externalId is NULL')
            ->getQuery()->getResult();
    }

    /**
     * Get all internal events QueryBuilder (exclude external events)
     * It's used to get only the querybuilder to apply filters on it on custom DataProvider.
     */
    public function getInternalEventsQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e')
            ->where('e.externalSource is NULL')
            ->andWhere('e.externalId is NULL')
        ;
    }
}
