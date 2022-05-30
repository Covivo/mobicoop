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

namespace App\Carpool\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Ask;
use App\User\Entity\User;

class AskHistoryRepository
{
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(AskHistory::class);
    }
    
    /**
     * Find last ask history by ask and status
     *
     * @param string $action
     * @return AskHistory
     */
    public function findLastByAskAndStatus(Ask $ask, int $status): AskHistory
    {
        $query = $this->repository->createQueryBuilder('ah')
        ->where('ah.ask = :ask and ah.status=:status')
        ->setParameter('ask', $ask)
        ->setParameter('status', $status)
        ->orderBy('ah.createdDate', 'DESC')
        ->setMaxResults(1);
        ;
        return $query->getQuery()->getOneOrNullResult();
    }

    public function findLastAskHistory(Ask $ask)
    {
        $query = $this->repository->createQueryBuilder('ah')
        ->join('ah.ask', 'a')
        ->where('a = :ask')
        ->setParameter('ask', $ask)
        ->orderBy('ah.createdDate', 'DESC');
        
        return $query->getQuery()->getResult();
    }

    /**
     * Find the last AskHistory having a linked message
     *
     * @param Ask $ask  The ask
     * @return AskHistory|null  The ask history found
     */
    public function findLastAskHistoryWithMessage(Ask $ask): ?AskHistory
    {
        $query = $this->repository->createQueryBuilder('ah')
        ->join('ah.ask', 'a')
        ->join('ah.message', 'm')
        ->where('a = :ask')
        ->setParameter('ask', $ask)
        ->orderBy('ah.createdDate', 'DESC')
        ->setMaxResults(1);
        
        return $query->getQuery()->getOneOrNullResult();
    }
}
