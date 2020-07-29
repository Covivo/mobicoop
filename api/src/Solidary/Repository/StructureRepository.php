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
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
*/
class StructureRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Structure::class);
    }


    public function find(int $id): ?Structure
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find the structures of a User
     *
     * @param User $user    The user
     * @return array|null
     */
    public function findByUser(User $user): ?array
    {

        // @TODO: Remove this ugly hack
        // I've added it to be able to work on the admin during API fix
        // return $this->repository->findById(1);

        $query = $this->repository->createQueryBuilder('s')
        ->join('s.solidaryUserStructures', 'sus')
        ->join('sus.solidaryUser', 'su')
        ->join('su.user', 'u')
        ->where('u.id = :user')
        ->setParameter('user', $user->getId());

        return $query->getQuery()->getResult();
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findByTerritories(array $territories)
    {
        // Get all the territory id
        $territoriesId = [];
        foreach ($territories as $territory) {
            $territoriesId[] = $territory->getId();
        }
        
        $query = $this->entityManager->createQuery("
            SELECT s from App\Solidary\Entity\Structure s
            inner join App\Geography\Entity\Territory t
            where t.id in ('".implode("','", $territoriesId)."')
        ");
        
        return $query->getResult();
    }
}
