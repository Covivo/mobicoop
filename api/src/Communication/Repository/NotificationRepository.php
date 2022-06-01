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

namespace App\Communication\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Communication\Entity\Notification;

class NotificationRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Notification::class);
    }

    public function find(int $id): ?Notification
    {
        return $this->repository->find($id);
    }

    /**
     * Find active notifications for a given action
     *
     * @param string $action
     */
    public function findActiveByAction(string $action)
    {
        $query = $this->repository->createQueryBuilder('n')
        ->join('n.action', 'a')
        ->where('a.name = :action and n.active=1 and n.userEditable=0')
        ->setParameter('action', $action)
        ;
        return $query->getQuery()->getResult();
    }

    /**
     * Find user editable notifications
     */
    public function findUserEditable()
    {
        $query = $this->repository->createQueryBuilder('n')
        ->where('n.userEditable=1')
        ;
        return $query->getQuery()->getResult();
    }
}
