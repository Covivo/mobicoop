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

namespace App\Payment\Repository;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Ressource\PaymentItem;
use App\User\Entity\User;
use DateTime;

class CarpoolItemRepository
{
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(CarpoolItem::class);
    }

    public function find(int $id): ?CarpoolItem
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find a carpool payment item by ask and date
     *
     * @param Ask $ask          The ask
     * @param DateTime $date    The date
     * @return CarpoolItem|null    The carpool item found or null if not found
     */
    public function findByAskAndDate(Ask $ask, DateTime $date)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0);
        $endDate = clone $date;
        $endDate->setTime(23, 59, 59, 999);

        $query = $this->repository->createQueryBuilder('ci')
        ->where('ci.ask = :ask')
        ->andWhere('ci.itemDate BETWEEN :startDate and :endDate')
        ->setParameter('ask', $ask)
        ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
        ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find carpool payment items for payments
     *
     * @param integer $frequency    The frequency for the items
     * @param integer $type         The type of items (1 = to pay, 2 = to collect)
     * @param User $user            The user concerned
     * @param DateTime $fromDate    The start of the period for which we want to get the items
     * @param DateTime $toDate      The end of the period  for which we want to get the items
     * @return array                The carpool items found
     */
    public function findForPayments(int $frequency, int $type, User $user, DateTime $fromDate, DateTime $toDate)
    {
        $query = $this->repository->createQueryBuilder('ci')
        ->join('ci.ask', 'a')
        ->join('a.criteria', 'c')
        ->where('ci.itemDate BETWEEN :fromDate and :toDate')
        ->andWhere('c.frequency = :frequency')
        ->orderBy('a.type')
        ->setParameter('fromDate', $fromDate->format('Y-m-d H:i:s'))
        ->setParameter('toDate', $toDate->format('Y-m-d H:i:s'))
        ->setParameter('frequency', $frequency);

        if ($type == PaymentItem::TYPE_PAY) {
            $query->andWhere('ci.debtorUser = :user')
            ->andWhere('ci.debtorStatus = :debtorStatusWaiting')
            ->setParameter('user', $user)
            ->setParameter('debtorStatusWaiting', 0);
        } else {
            $query->andWhere('ci.creditorUser = :user')
            ->andWhere('ci.creditorStatus = :creditorStatusWaiting')
            ->setParameter('user', $user)
            ->setParameter('creditorStatusWaiting', 0);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpoolItems for a user as creditor or deptor
     *
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        $query = $this->repository->createQueryBuilder('ci')
        ->where('ci.creditorUser = :user OR ci.debtorUser = :user')
        ->setParameter('user', $user);
        
        return $query->getQuery()->getResult();
    }
}
