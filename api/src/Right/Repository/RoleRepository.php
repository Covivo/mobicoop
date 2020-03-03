<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Right\Repository;

use App\Right\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Role::class);
    }

    public function find(int $id): ?Role
    {
        return $this->repository->find($id);
    }

    /**
    * Find role by name.
    *
    * @param string $name
    * @return Role
    */
    public function findByName(string $name)
    {
        return $this->repository->findOneBy(['name'=>$name]);
    }

    /**
     * Find the children of a given Role.
     *
     * @param Role $role
     * @return void
     */
    public function findChildren(Role $role)
    {
        $query = $this->repository->createQueryBuilder('r')
        ->andWhere('r.parent = :parent')
        ->setParameter('parent', $role)
        ->getQuery();
        
        return $query->getResult()
        ;
    }
}
