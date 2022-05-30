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

namespace App\Match\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Match\Entity\MassMatching;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @method MassMatching|null find($id)
 */
class MassMatchingRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MassMatching::class);
    }

    public function find(int $id): ?MassMatching
    {
        return $this->repository->find($id);
    }

    public function deleteMatchingsOfAMass($massId)
    {
        $conn = $this->entityManager->getConnection();

        $sql = "DELETE mass_matching FROM `mass_matching` INNER JOIN mass_person on mass_person.id = mass_matching.mass_person1_id WHERE mass_person.mass_id = ".$massId;

        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }
}
