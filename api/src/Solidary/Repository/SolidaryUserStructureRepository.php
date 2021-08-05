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

use App\Solidary\Entity\SolidaryUserStructure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
*/
class SolidaryUserStructureRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(SolidaryUserStructure::class);
    }


    public function find(int $id): ?SolidaryUserStructure
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

    /**
     * Get a SolidaryUserStructure by its StructureId and its SolidaryUserId
     *
     * @param integer $structureId      The id of the Structure
     * @param integer $solidaryUserId   The id of the SolidaryUser
     * @return SolidaryUserStructure|null     The SolidaryUserStructure found, or null if not found
     */
    public function findByStructureAndSolidaryUser(int $structureId, int $solidaryUserId)
    {
        $query = $this->repository->createQueryBuilder('sus')
        ->join('sus.solidaryUser', 'su')
        ->join('sus.structure', 's')
        ->where('su.id = :solidaryUserId')
        ->andWhere('s.id = :structureId')
        ->setParameter('solidaryUserId', $solidaryUserId)
        ->setParameter('structureId', $structureId);

        return $query->getQuery()->getOneOrNullResult();
    }
}
