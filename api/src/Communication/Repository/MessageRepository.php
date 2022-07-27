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

namespace App\Communication\Repository;

use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Solidary\Entity\SolidaryAsk;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class MessageRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Message::class);
    }

    public function find(int $id): ?Message
    {
        return $this->repository->find($id);
    }

    public function findThreads(User $user)
    {
        $this->repository = $this->entityManager->getRepository(Message::class);
        $query = $this->repository->createQueryBuilder('m')
            ->join('m.recipients', 'r')
            ->where('m.message is null and (m.user = :user or r.user = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    public function findThreadsDirectMessages(User $user)
    {
        $this->repository = $this->entityManager->getRepository(Message::class);
        $query = $this->repository->createQueryBuilder('m')
            ->join('m.recipients', 'r')
            ->leftJoin('m.askHistory', 'ah')
            ->leftJoin('m.solidaryAskHistory', 'sah')
            ->leftJoin('m.messages', 'ms')
            ->where('m.message is null and ah.id is null and sah is null and (m.user = :user or r.user = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    public function findThreadsCarpoolMessages(User $user)
    {
        $this->repository = $this->entityManager->getRepository(Message::class);
        $query = $this->repository->createQueryBuilder('m')
            ->leftJoin('m.askHistory', 'ah')
            ->leftJoin('m.messages', 'ms')
            ->leftJoin('m.recipients', 'r')
            ->where('m.message is null and ah.id is not null and (m.user = :user or r.user = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find the unread messages of a User.
     *
     * @return null|Recipient[]
     */
    public function findUnreadMessages(User $user): ?array
    {
        $this->repository = $this->entityManager->getRepository(Recipient::class);
        $query = $this->repository->createQueryBuilder('r')
            ->join('r.message', 'm')
            ->where('r.user = :user')
            ->andWhere('r.readDate is null')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Return the first message related with a SolidaryAsk, or null if not found.
     *
     * @param SolidaryAsk $solidaryAsk The solidaryAsk
     *
     * @return null|Message The message found, or null if not found
     */
    public function findFirstForSolidaryAsk(SolidaryAsk $solidaryAsk)
    {
        $query = $this->repository->createQueryBuilder('m')
            ->innerJoin('m.solidaryAskHistory', 'sah')
            ->where('sah.solidaryAsk = :solidaryAsk')
            ->orderBy('sah.createdDate', 'asc')
            ->setMaxResults(1)
            ->setParameter('solidaryAsk', $solidaryAsk)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find all answered messages of a user.
     *
     * @return null|Message[]
     */
    public function findAnswers(User $user)
    {
        $this->repository = $this->entityManager->getRepository(Message::class);
        $query = $this->repository->createQueryBuilder('m')
            ->where('m.message is not null and (m.user = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    public function findNotAnsweredMessagesSinceXDays(int $nbOfDays)
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('-'.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT mi.id
            FROM message mi
                LEFT JOIN
                    (SELECT m1.id
                    FROM message m1
                        INNER JOIN message m2 on m2.message_id = m1.id AND m2.user_id != m1.user_id) replied_messages ON replied_messages.id = mi.id
            WHERE mi.message_id IS NULL AND replied_messages.id IS NULL AND DATE(mi.created_date) = '.$createdDate
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
