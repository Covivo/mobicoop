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

use App\Solidary\Entity\Structure;
use App\Solidary\Entity\StructureProof;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
*/
class StructureProofRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(StructureProof::class);
    }


    public function find(int $id): ?StructureProof
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findStructureProofs(Structure $structure)
    {
        $query = $this->repository->createQueryBuilder('sp')
        ->where('sp.structure = :structure')
        ->setParameter('structure', $structure);
        return $query->getQuery()->getResult();
    }

    public function findNotMandatoryBeneficiaryStructureProofs(Structure $structure)
    {
        $query = $this->repository->createQueryBuilder('sp')
        ->where('sp.structure = :structure')
        ->andwhere('sp.mandatory = 0')
        ->andwhere('sp.type = 1')
        ->setParameter('structure', $structure);
        return $query->getQuery()->getResult();
    }
}
