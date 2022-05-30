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
 */

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
     * Find the structures where a User is beneficiary.
     *
     * @param User $user The user
     */
    public function findByUser(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('s')
            ->join('s.solidaryUserStructures', 'sus')
            ->join('sus.solidaryUser', 'su')
            ->join('su.user', 'u')
            ->where('u.id = :user')
            ->setParameter('user', $user->getId())
        ;

        return $query->getQuery()->getResult();
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Find structures by GPS points.
     *
     * @return Structure[]
     */
    public function findByPoint(float $longitude, float $latitude)
    {
        $conn = $this->entityManager->getConnection();

        // we get only structure's ids
        $sql = "SELECT s.id FROM structure s
        INNER JOIN structure_territory st ON st.structure_id = s.id
        INNER JOIN territory t ON t.id = st.territory_id
        WHERE ST_INTERSECTS(t.geo_json_detail,ST_GEOMFROMTEXT('POINT({$longitude} {$latitude})'))=1
        ";
        $stmt = $conn->prepare($sql);
        $structureIds = $stmt->executeQuery()->fetchAllAssociative();

        $structures = [];
        // we retrieve structures with ids. We do that in two step because with the sql call we do not get linked entities infos like subjects
        foreach ($structureIds as $structureId) {
            $structures[] = $this->find($structureId['id']);
        }

        return $structures;
    }
}
