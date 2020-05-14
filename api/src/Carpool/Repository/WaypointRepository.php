<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Waypoint;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Waypoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Waypoint|null findOneBy(array $criteria, array $orderBy = null)
 */
class WaypointRepository
{
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Waypoint::class);
    }

    public function find(int $id): ?Waypoint
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * Find the first waypoint for an ask and a role
     *
     * @param Ask $ask          The ask
     * @param integer $role     The role
     * @return Waypoint|null    The waypoint found or null
     */
    public function findMinPositionForAskAndRole(Ask $ask, int $role)
    {
        $query = $this->repository->createQueryBuilder('w')
        ->select('w, MIN(w.position) AS min_position')
        ->where('w.ask = :ask')
        ->andwhere('w.role = :role')
        ->setParameter('ask', $ask)
        ->setParameter('role', $role);

        return $query->getQuery()->getResult();
    }

    /**
     * Find the last waypoint for an ask and a role
     *
     * @param Ask $ask          The ask
     * @param integer $role     The role
     * @return Waypoint|null    The waypoint found or null
     */
    public function findMaxPositionForAskAndRole(Ask $ask, int $role)
    {
        $query = $this->repository->createQueryBuilder('w')
        ->select('w, MAX(w.position) AS max_position')
        ->where('w.ask = :ask')
        ->andwhere('w.role = :role')
        ->setParameter('ask', $ask)
        ->setParameter('role', $role);

        return $query->getQuery()->getResult();
    }
}
