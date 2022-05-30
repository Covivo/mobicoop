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

use App\Match\Entity\MassPTJourney;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassPTJourneyRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MassPTJourney::class);
    }

    public function find(int $id): ?MassPTJourney
    {
        return $this->repository->find($id);
    }

    public function deleteMassPTJourneysOfAMass($massId)
    {
        $conn = $this->entityManager->getConnection();

        $sql = "DELETE mass_ptjourney FROM `mass_ptjourney` INNER JOIN mass_person on mass_person.id = mass_ptjourney.mass_person_id WHERE mass_person.mass_id = ".$massId;

        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }
}
