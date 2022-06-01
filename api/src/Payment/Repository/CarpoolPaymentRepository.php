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

namespace App\Payment\Repository;

use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CarpoolPaymentRepository
{
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(CarpoolPayment::class);
    }

    public function find(int $id): ?CarpoolPayment
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?CarpoolPayment
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find successful electronic payment and related items for the given period.
     *
     * @param DateTime $fromDate The start date and time
     * @param DateTime $toDate   The end date and time
     *
     * @return null|CarpoolPayment[] The carpool payments if found
     */
    public function findSuccessfulElectronicPaymentsForPeriod(DateTime $fromDate, DateTime $toDate): ?array
    {
        $query = $this->repository->createQueryBuilder('cp')
            ->join('cp.carpoolItems', 'ci')
            ->where('ci.debtorStatus = :debtorStatus')
            ->andWhere('cp.status = :success and cp.transactionId IS NOT NULL')
            ->andWhere('cp.transactionDate between :fromDate and :toDate')
            ->setParameter('debtorStatus', CarpoolItem::DEBTOR_STATUS_ONLINE)
            ->setParameter('success', CarpoolPayment::STATUS_SUCCESS)
            ->setParameter('fromDate', $fromDate->format('Y-m-d H:i:s'))
            ->setParameter('toDate', $toDate->format('Y-m-d H:i:s'))
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find a carpoolpayment made by $debtor about a $carpoolItem.
     *
     * @return null|CarpoolPayment[]
     */
    public function findCarpoolPaymentByDebtorAndCarpoolItem(User $debtor, CarpoolItem $carpoolItem): ?array
    {
        $query = $this->repository->createQueryBuilder('cp')
            ->join('cp.carpoolItems', 'ci')
            ->where('cp.user = :debtor')
            ->andWhere('ci.id = :carpoolItemId')
            ->setParameter('debtor', $debtor)
            ->setParameter('carpoolItemId', $carpoolItem->getId())
        ;

        return $query->getQuery()->getResult();
    }
}
