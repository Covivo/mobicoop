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
 */

namespace App\Geography\Repository;

use App\Geography\Entity\Direction;
use App\Geography\Entity\Territory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|Direction find($id, $lockMode = null, $lockVersion = null)
 * @method null|Direction findOneBy(array $criteria, array $orderBy = null)
 * @method Direction[]    findAll()
 * @method Direction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectionRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Direction::class);
    }

    /**
     * Return all directions without zones.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The directions found
     */
    public function findAllWithoutZones(): mixed
    {
        $query = $this->repository->createQueryBuilder('d')
            ->leftJoin('d.zones', 'z')
            ->andWhere('z.direction IS NULL')
            ->getQuery()
        ;

        return $query->getResult()
        ;
    }

    /**
     * Return all directions that have their bounding box in the given territory.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The directions found
     */
    public function findAllWithBoundingBoxInTerritory(Territory $territory): mixed
    {
        $query = $this->entityManager->createQuery('
            SELECT d from App\\Geography\\Entity\\Direction d, App\\Geography\\Entity\\Territory t
            where t.id = '.$territory->getId().'
            and ST_INTERSECTS(t.geoJsonDetail,d.geoJsonBbox)=1
        ');

        return $query->getResult()
        ;
    }

    /**
     * Search if a direction intersects a given territory.
     */
    public function directionIsInTerritory(Direction $direction, Territory $territory)
    {
        $sql = 'SELECT ST_INTERSECTS(t.geoJsonDetail,d.geoJsonDetail) as inTerritory
            from App\\Geography\\Entity\\Territory t, App\\Geography\\Entity\\Direction d
            where t.id = '.$territory->getId().' and d.id = '.$direction->getId();
        $query = $this->entityManager->createQuery($sql);

        return 1 == $query->getResult()[0]['inTerritory'];
    }
}
