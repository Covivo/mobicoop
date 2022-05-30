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

namespace App\Carpool\Repository;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Waypoint;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method null|Waypoint find($id, $lockMode = null, $lockVersion = null)
 * @method null|Waypoint findOneBy(array $criteria, array $orderBy = null)
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
     * Find the first waypoint for an ask and a role.
     *
     * @param Ask $ask  The ask
     * @param int $role The role
     *
     * @return null|Waypoint The waypoint found or null
     */
    public function findMinPositionForAskAndRole(Ask $ask, int $role): ?Waypoint
    {
        $query = $this->repository->createQueryBuilder('w')
            ->select('MIN(w.position) AS min_position')
            ->where('w.ask = :ask')
            ->andwhere('w.role = :role')
            ->setParameter('ask', $ask)
            ->setParameter('role', $role)
        ;

        if ($result = $query->getQuery()->getOneOrNullResult()) {
            return $this->findOneby([
                'position' => $result['min_position'],
                'ask' => $ask,
                'role' => $role,
            ]);
        }

        return null;
    }

    /**
     * Find the last waypoint for an ask and a role.
     *
     * @param Ask $ask  The ask
     * @param int $role The role
     *
     * @return null|Waypoint The waypoint found or null
     */
    public function findMaxPositionForAskAndRole(Ask $ask, int $role): ?Waypoint
    {
        $query = $this->repository->createQueryBuilder('w')
            ->select('MAX(w.position) AS max_position')
            ->where('w.ask = :ask')
            ->andwhere('w.role = :role')
            ->setParameter('ask', $ask)
            ->setParameter('role', $role)
        ;

        if ($result = $query->getQuery()->getOneOrNullResult()) {
            return $this->findOneby([
                'position' => $result['max_position'],
                'ask' => $ask,
                'role' => $role,
            ]);
        }

        return null;
    }
}
