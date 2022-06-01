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
 */

namespace App\User\Repository;

use App\User\Entity\UserNotification;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserNotificationRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(UserNotification::class);
    }

    public function find(int $id): ?UserNotification
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $criteria): ?UserNotification
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findActiveByAction(string $action, int $userId)
    {
        $userNotifications = [];
        $query = $this->repository->createQueryBuilder('un')
            ->join('un.notification', 'n')
            ->join('n.action', 'a')
            ->join('un.user', 'u')
            ->where('a.name = :action and n.active=1 and un.active=1 and u.id=:userId')
            ->setParameter('action', $action)
            ->setParameter('userId', $userId)
        ;

        // Safety : If there is several identical userNotifications (it shouldn't but... you know...), we only keep one.
        $alreadySeenUserNotifications = [];
        foreach ($query->getQuery()->getResult() as $currentUserNotification) {
            if (!in_array($currentUserNotification->getNotification()->getId(), $alreadySeenUserNotifications)) {
                $userNotifications[] = $currentUserNotification;
                $alreadySeenUserNotifications[] = $currentUserNotification->getNotification()->getId();
            }
        }

        return $userNotifications;
    }

    /**
     * Find user editable notifications.
     */
    public function findUserNotifications(int $id)
    {
        $query = $this->repository->createQueryBuilder('un')
            ->join('un.user', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }
}
