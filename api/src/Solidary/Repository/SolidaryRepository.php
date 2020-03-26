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

namespace App\Solidary\Repository;

use App\Action\Entity\Diary;
use App\Solidary\Entity\Solidary;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SolidaryRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Solidary::class);
    }


    public function find(int $id): ?Solidary
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

    public function findOneBy(array $criteria): ?Solidary
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get the Diaries entries of a Solidary
     *
     * @param Solidary $solidary   The Solidary
     * @return float
     */
    public function getDiaries(Solidary $solidary)
    {
        $diaryRepository = $this->entityManager->getRepository(Diary::class);

        $query = $diaryRepository->createQueryBuilder('d')
        ->where('d.solidary = :solidary')
        ->setParameter('solidary', $solidary)
        ->orderBy('d.createdDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
