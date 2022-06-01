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

namespace App\Carpool\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Criteria;

class CriteriaRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Criteria::class);
    }

    public function find(int $id): ?Criteria
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findByUserImportStatus(int $status, ?\DateTimeInterface $date = null)
    {

        // we search the matchings in the proposal entity
        $query = $this->repository->createQueryBuilder('c');

        $selection = [
            'c.id as cid',
            'c.driver',
            'c.passenger',
            'w.position',
            'w.destination',
            'a.longitude',
            'a.latitude'
        ];

        $query->select($selection);

        $query->join('c.proposal', 'p')
        ->join('p.waypoints', 'w')
        ->join('w.address', 'a')
        ->join('p.user', 'u')
        ->join('u.import', 'i')
        ->where('i.status = :status')
        ->setParameter('status', $status);
        if (!is_null($date)) {
            $query->andWhere('((c.frequency = 1 and c.fromDate >= :date) or (c.frequency=2 and c.toDate >= :date))')
            ->setParameter('date', $date->format('Y-m-d'));
        }

        return $query->getQuery()->getResult();
    }

    public function findAllForDirectionsAndDefault()
    {
        $query = $this->repository->createQueryBuilder('c');

        $selection = [
            'c.id as cid',
            'c.driver',
            'c.passenger',
            'w.position',
            'w.destination',
            'a.longitude',
            'a.latitude'
        ];

        $query->select($selection);

        $query->join('c.proposal', 'p')
        ->join('p.waypoints', 'w')
        ->join('w.address', 'a')
        ->where('c.driver = 1 and c.directionDriver IS NULL')
        ->orWhere('c.passenger = 1 and c.directionPassenger IS NULL');
        return $query->getQuery()->getResult();
    }

    public function findDrivers(): ?array
    {
        $query = $this->repository->createQueryBuilder('c')
        ->select('c')
        ->where('c.directionDriver IS NOT NULL');

        return $query->getQuery()->getResult();
    }
}
