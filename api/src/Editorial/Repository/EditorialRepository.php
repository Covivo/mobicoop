<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Editorial\Repository;

use App\Editorial\Entity\Editorial;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class EditorialRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Editorial::class);
        $this->entityManager = $entityManager;
    }
    
    /**
     * Find an editorial with an id
     *
     * @param integer $id
     * @return Editorial|null
     */
    public function find(int $id): ?Editorial
    {
        return $this->repository->find($id);
    }

    /**
     * Find All editorials
     *
     * @return Editorials|null
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Find One Editorial by criteria
     *
     * @param array $criteria
     * @return Editorial|null
     */
    public function findOneBy(array $criteria): ?Editorial
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Inactive all editorials except the one activated
     *
     * @param Editorial $editorial
     * @return void
     */
    public function setInactive(Editorial $editorial)
    {
        // we use raw sql as the request
        $conn = $this->entityManager->getConnection();

        // set editorial's status to 0 except the one updated
        $sql = "UPDATE `editorial` SET `status`= 0 WHERE `id`!=" . $editorial->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}
