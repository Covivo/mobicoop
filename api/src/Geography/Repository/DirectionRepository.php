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

namespace App\Geography\Repository;

use App\Geography\Entity\Direction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Geography\Entity\Territory;

/**
 * @method Direction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Direction|null findOneBy(array $criteria, array $orderBy = null)
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
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The directions found
     */
    public function findAllWithoutZones()
    {
        $query = $this->repository->createQueryBuilder('d')
        ->leftJoin('d.zones', 'z')
        ->andWhere('z.direction IS NULL')
        ->getQuery();
        
        return $query->getResult()
        ;
    }

    /**
     * Return all directions that have thier bounding box in the given territory.
     *
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The directions found
     */
    public function findAllWithBoundingBoxInTerritory(Territory $territory)
    {
        $query = $this->entityManager->createQuery("
            SELECT d from App\Geography\Entity\Direction d, App\Geography\Entity\Territory t
            where t.id = " . $territory->getId() . "
            and ST_INTERSECTS(t.detail,ST_GeomFromText(CONCAT('POLYGON((',
            d.bboxMinLon,' ',d.bboxMinLat,',',
            d.bboxMinLon,' ',d.bboxMaxLat,',',
            d.bboxMaxLon,' ',d.bboxMinLat,',',
            d.bboxMaxLon,' ',d.bboxMaxLat,',',
            d.bboxMinLon,' ',d.bboxMinLat,
            '))')))=1
        ");
        
        return $query->getResult()
        ;
    }
}
