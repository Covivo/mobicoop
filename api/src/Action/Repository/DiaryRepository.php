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

namespace App\Action\Repository;

use App\Action\Entity\Diary;
use App\Solidary\Entity\Solidary;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DiaryRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Diary::class);
    }

    public function find(int $id): ?Diary
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Diary
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find the last entry for a given Solidary
     *
     * @param Solidary $solidary    The solidary record
     * @return Diary|null           The progression
     */
    public function findLastEntryForSolidary(Solidary $solidary): ?Diary
    {
        return $this->repository->createQueryBuilder('d')
        ->where('d.solidary = :solidary')
        ->orderBy('d.updatedDate', 'DESC')
        ->setParameter('solidary', $solidary)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
}
