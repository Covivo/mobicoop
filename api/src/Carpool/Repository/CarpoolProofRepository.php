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
use App\Carpool\Entity\CarpoolProof;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CarpoolProofRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(CarpoolProof::class);
    }

    public function find(int $id): ?CarpoolProof
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find a proof by ask and date.
     *
     * @param Ask       $ask  The ask
     * @param \DateTime $date The date
     *
     * @return null|CarpoolProof The carpool proof found or null if not found
     */
    public function findByAskAndDate(Ask $ask, \DateTime $date)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0);
        $endDate = clone $date;
        $endDate->setTime(23, 59, 59, 999);

        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.ask = :ask')
            ->andWhere('(cp.pickUpPassengerDate BETWEEN :startDate and :endDate) or (cp.pickUpDriverDate BETWEEN :startDate and :endDate)')
            ->setParameter('ask', $ask)
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->setMaxResults(1)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find the remaining proofs for a user (driver or passenger) : used to find proofs related to a deleted ask.
     *
     * @param User $user The user
     *
     * @return null|CarpoolProof[] The carpool proofs found or null if not found
     */
    public function findRemainingByUser(User $user)
    {
        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.ask is null')
            ->andWhere('(cp.driver = :user or cp.passenger = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find proofs with given types and given period.
     *
     * @param array     $types     The possible types
     * @param \DateTime $startDate The start date of the period
     * @param \DateTime $endDate   The end date of the period
     * @param array     $status    The possible status
     *
     * @return CarpoolProof[] The carpool proofs found
     */
    public function findByTypesAndPeriod(array $types, \DateTime $startDate, \DateTime $endDate, array $status = null)
    {
        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59, 59, 999);

        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.type in (:types)')
            ->andWhere('(cp.pickUpPassengerDate BETWEEN :startDate and :endDate) or (cp.pickUpDriverDate BETWEEN :startDate and :endDate)')
        ;

        if (!is_null($status)) {
            $query->andWhere('cp.status in (:status)')
                ->setParameter('status', $status)
            ;
        }

        $query
            ->setParameter('types', $types)
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
        ;

        return $query->getQuery()->getResult();
    }

    public function findCarpoolProofToChack(array $status): ?array
    {
        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.status in (:status)')
            ->setParameter('status', $status)
        ;

        return $query->getQuery()->getResult();
    }
}
