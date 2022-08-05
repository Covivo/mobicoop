<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Communication\Repository;

use App\Communication\Entity\Notified;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class NotifiedRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Notified::class);
        $this->entityManager = $entityManager;
    }

    public function find(int $id): ?Notified
    {
        return $this->repository->find($id);
    }

    public function findNotifiedByUserAndNotificationDuringLastMonth(int $userId, int $notificationId)
    {
        $now = (new \DateTime('now'));
        $today = $now->format('Y-m-d');
        $aMonthAgo = $now->modify('-1 month')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('n')
            ->select('n')
            ->where('n.user = :userId')
            ->andWhere('n.notification = :notificationId')
            ->andWhere('n.sentDate >= :aMonthAgo')
            ->andWhere('n.sentDate <= :today')
            ->setParameter('userId', $userId)
            ->setParameter('notificationId', $notificationId)
            ->setParameter('today', $today)
            ->setParameter('aMonthAgo', $aMonthAgo)
        ;

        return $query->getQuery()->getResult();
    }

    public function findNotifiedByUserAndNotification(int $userId, int $notificationId)
    {
        $query = $this->repository->createQueryBuilder('n')
            ->select('n')
            ->where('n.user = :userId')
            ->andWhere('n.notification = :notificationId')
            ->setParameter('userId', $userId)
            ->setParameter('notificationId', $notificationId)
        ;

        return $query->getQuery()->getResult();
    }
}
