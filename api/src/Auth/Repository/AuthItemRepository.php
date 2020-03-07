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

namespace App\Auth\Repository;

use App\Auth\Entity\AuthItem;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Geography\Entity\Territory;

/**
 * @method AuthItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthItem[]    findAll()
 * @method AuthItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthItemRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(AuthItem::class);
    }

    /**
     * Find Auth Item by name.
     *
     * @param string $name
     * @return AuthItem
     */
    public function findByName(string $name)
    {
        return $this->repository->findOneBy(['name'=>$name]);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }
}
