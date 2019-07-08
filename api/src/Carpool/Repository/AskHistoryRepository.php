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

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
     * @return void
     */
    public function findLastByAskAndStatus(Ask $ask, int $status)
    {
        $query = $this->repository->createQueryBuilder('ah')
        ->where('ah.ask = :ask and ah.status=:status')
        ->setParameter('ask', $ask)
        ->setParameter('status', $status)
        ->orderBy('ah.createdDate', 'DESC')
        ->setMaxResults(1);
        ;
        return $query->getQuery()->getSingleResult();
    }
}
