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

use App\Geography\Entity\Zone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Zone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zone[]    findAll()
 * @method Zone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZoneRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Zone::class);
    }
    
    /**
     * Return the zone for a given latitude and logitude.
     * @param int $latitude     The latitude
     * @param int $longitude    The longitude
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The zone found
     */
    public function findOneByLatitudeLongitude($latitude, $longitude)
    {
        $query = $this->repository->createQueryBuilder('z')
        ->andWhere('z.fromLat <= :lat')
        ->andWhere('z.toLat >= :lat')
        ->andWhere('z.fromLon <= :lon')
        ->andWhere('z.toLon >= :lon')
        ->setParameter('lat', $latitude)
        ->setParameter('lon', $longitude)
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }
}
