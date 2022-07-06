<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Community\Admin\Repository;

use App\Community\Entity\CommunitySecurity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CommunitySecurityRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(CommunitySecurity::class);
    }

    /**
     * Find community by id.
     */
    public function find(int $id): ?CommunitySecurity
    {
        return $this->repository->find($id);
    }

    /**
     * Find All communities.
     *
     * @return null|CommunitySecurity
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Find communities by criteria.
     *
     * @return null|CommunitySecurity[]
     */
    public function findBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }
}
