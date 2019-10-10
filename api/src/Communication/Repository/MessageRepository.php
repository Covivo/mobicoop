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
use App\Communication\Entity\Message;
use App\User\Entity\User;

class MessageRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Message::class);
    }
    
    public function find(int $id): ?Message
    {
        return $this->repository->find($id);
    }

    public function findThreads(User $user)
    {
        $query = $this->repository->createQueryBuilder('m')
        ->join('m.recipients', 'r')
        ->where('m.message is null and (m.user = :user or r.user = :user)')
        ->setParameter('user', $user);

        return $query->getQuery()->getResult();
    }

    public function findThreadsDirectMessages(User $user)
    {
        $query = $this->repository->createQueryBuilder('m')
        ->join('m.recipients', 'r')
        ->leftJoin('m.askHistory', 'ah')
        ->leftJoin('m.messages', 'ms')
        ->where('m.message is null and ah.id is null and (m.user = :user or r.user = :user)')
        ->setParameter('user', $user);

        return $query->getQuery()->getResult();
    }

    public function findThreadsCarpoolMessages(User $user)
    {
        $query = $this->repository->createQueryBuilder('m')
        ->join('m.recipients', 'r')
        ->leftJoin('m.askHistory', 'ah')
        ->leftJoin('m.messages', 'ms')
        ->where('m.message is null and ah.id is not null and (m.user = :user or r.user = :user)')
        ->setParameter('user', $user);

        return $query->getQuery()->getResult();
    }
}
