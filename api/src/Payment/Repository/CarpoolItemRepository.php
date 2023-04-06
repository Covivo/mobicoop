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

use App\Carpool\Entity\Ask;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Ressource\PaymentItem;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CarpoolItemRepository
{
    /**
     * @var EntityRepository
     */
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
     * Find a carpool item by ask and date.
     *
     * @param Ask       $ask  The ask
     * @param \DateTime $date The date
     *
     * @return null|CarpoolItem The carpool item found or null if not found
     */
    public function findByAskAndDate(Ask $ask, \DateTime $date)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.ask = :ask')
            ->andWhere('ci.itemDate = :date')
            ->setParameter('ask', $ask)
            ->setParameter('date', $date->format('Y-m-d'))
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find all carpool items for a given ask in a given period.
     * Results are ordered by item date asc.
     *
     * @param Ask       $ask      The ask
     * @param \DateTime $fromDate The start of the period
     * @param \DateTime $toDate   The end of the period
     *
     * @return CarpoolItem[] The carpool items found
     */
    public function findByAskAndPeriod(Ask $ask, \DateTime $fromDate, \DateTime $toDate)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.ask = :ask')
            ->andWhere('ci.itemDate BETWEEN :startDate and :endDate')
            ->orderBy('ci.itemDate', 'ASC')
            ->setParameter('ask', $ask)
            ->setParameter('startDate', $fromDate->format('Y-m-d'))
            ->setParameter('endDate', $toDate->format('Y-m-d'))
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpool items for payments.
     *
     * @param int       $frequency The frequency for the items
     * @param int       $type      The type of items (1 = to pay, 2 = to collect)
     * @param User      $user      The user concerned
     * @param \DateTime $fromDate  The start of the period for which we want to get the items
     * @param \DateTime $toDate    The end of the period  for which we want to get the items
     *
     * @return array The carpool items found
     */
    public function findForPayments(int $frequency, int $type, User $user, \DateTime $fromDate, \DateTime $toDate)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->join('ci.ask', 'a')
            ->join('a.criteria', 'c')
            ->where('ci.itemDate BETWEEN :fromDate and :toDate')
            ->andWhere('c.frequency = :frequency')
            ->orderBy('a.type')
            ->setParameter('fromDate', $fromDate->format('Y-m-d'))
            ->setParameter('toDate', $toDate->format('Y-m-d'))
            ->setParameter('frequency', $frequency)
        ;

        if (PaymentItem::TYPE_PAY == $type) {
            $query->andWhere('ci.debtorUser = :user')
                ->andWhere('ci.debtorStatus = :debtorStatusWaiting or ci.debtorStatus = :debtorStatusPendingOnline or ci.debtorStatus = :debtorStatusPendingDirect')
                ->setParameter('user', $user)
                ->setParameter('debtorStatusWaiting', CarpoolItem::DEBTOR_STATUS_PENDING)
                ->setParameter('debtorStatusPendingOnline', CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE)
                ->setParameter('debtorStatusPendingDirect', CarpoolItem::DEBTOR_STATUS_PENDING_DIRECT)
            ;
        } else {
            $query->andWhere('ci.creditorUser = :user')
                ->andWhere('ci.creditorStatus = :creditorStatusWaiting')
                ->setParameter('user', $user)
                ->setParameter('creditorStatusWaiting', CarpoolItem::CREDITOR_STATUS_PENDING)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpoolItems for a user as creditor or debtor.
     *
     * @return array
     */
    public function findByUser(User $user)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.creditorUser = :user OR ci.debtorUser = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpoolItems for a user as creditor or debtor.
     *
     * @param mixed $toDate
     * @param mixed $fromDate
     *
     * @return array
     */
    public function findByUserAndDate(User $user, $fromDate, $toDate)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.creditorUser = :user OR ci.debtorUser = :user')
            ->setParameter('user', $user)
        ;
        if (null != $fromDate && null != $toDate) {
            $query->andWhere('ci.itemDate BETWEEN :fromDate and :toDate')
                ->setParameter('fromDate', $fromDate)
                ->setParameter('toDate', $toDate)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpoolItems where the consumption feedback is in error.
     *
     * @return array
     */
    public function findConsumptionFeedbackInError()
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.debtorConsumptionFeedbackDate is not null OR ci.creditorConsumptionFeedbackDate is not null')
            ->andWhere('(ci.debtorConsumptionFeedbackReturnCode is not null and ci.debtorConsumptionFeedbackReturnCode <> 200) OR (ci.creditorConsumptionFeedbackReturnCode is not null and ci.creditorConsumptionFeedbackReturnCode <> 200)')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find carpoolItems using electronic payment for a user as creditor.
     *
     * @return CarpoolItem[]
     */
    public function findByCreditorElectronically(User $user)
    {
        $query = $this->repository->createQueryBuilder('ci')
            ->where('ci.creditorUser = :user')
            ->andWhere('ci.creditorStatus = :paid or ci.creditorStatus = :pending')
            ->setParameter('user', $user)
            ->setParameter('paid', CarpoolItem::CREDITOR_STATUS_ONLINE)
            ->setParameter('pending', CarpoolItem::CREDITOR_STATUS_PENDING_ONLINE)
        ;

        return $query->getQuery()->getResult();
    }

    public function findUnpaidForDelay(int $delay)
    {
        $date = new \DateTime('now');
        $date = $date->sub(new \DateInterval("P{$delay}D"));

        $startDate = $date->format('Y-m-d').' 00:00:00';
        $endDate = $date->format('Y-m-d').' 23:59:59';

        $qb = $this->repository->createQueryBuilder('c');

        $qb
            ->where('c.unpaidDate IS NOT NULL')
            ->andWhere('c.unpaidDate BETWEEN :startDate AND :endDate')
            ->andWhere('c.creditorStatus NOT IN (:creditorStatus)')
            ->setParameters([
                'startDate' => $startDate,
                'endDate' => $endDate,
                'creditorStatus' => CarpoolItem::CREDITOR_STATUS_DIRECT.','.CarpoolItem::CREDITOR_STATUS_ONLINE,
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUnpaydForRelaunch(int $frequency, $date)
    {
        $qb = $this->repository->createQueryBuilder('ci');

        $parameters = [
            'creditorStatus' => CarpoolItem::CREDITOR_STATUS_DIRECT.','.CarpoolItem::CREDITOR_STATUS_ONLINE,
            'frequency' => $frequency,
            'pseudonymizedStatus' => User::STATUS_PSEUDONYMIZED,
        ];

        $qb
            ->innerJoin('ci.debtorUser', 'debU', 'WITH', 'debU.status != :pseudonymizedStatus')
            ->innerJoin('ci.creditorUser', 'creU', 'WITH', 'creU.status != :pseudonymizedStatus')
            ->innerJoin('ci.ask', 'a')
            ->innerJoin('a.criteria', 'c')
            ->where('ci.unpaidDate IS NULL')
            ->andWhere('ci.creditorStatus NOT IN (:creditorStatus)')
            ->andWhere('c.frequency = :frequency')
        ;

        switch (true) {
            case $date instanceof string:
                $qb->andWhere("DATE_FORMAT(c.fromDate, '%a') = :day");
                $parameters['day'] = $date;

                break;

            case $date instanceof \DateTime:
                $qb->andWhere('ci.createdDate LIKE :date');
                $parameters['date'] = $date->format('Y-m-d').'%';

                break;
        }

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }
}
