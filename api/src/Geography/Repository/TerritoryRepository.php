<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use App\Geography\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Geography\Entity\Territory;
use App\Geography\Entity\Direction;

/**
 * @method Territory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Territory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Territory[]    findAll()
 * @method Territory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TerritoryRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Territory::class);
    }
    
    public function find(int $id): ?Territory
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Search a territory by its geoJson.
     *
     * @param array $geoJson
     * @return void
     */
    public function findByGeoJson(array $geoJson)
    {
    }

    /**
     * Find territories for a direction
     *
     * @param Direction $direction  The direction
     * @return Territory[]|null       The territories
     */
    public function findDirectionTerritories(Direction $direction)
    {
        $query = $this->repository->createQueryBuilder('t')
            ->join('\App\Geography\Entity\Direction', 'd')
            ->where('d.id = :id')
            ->setParameter('id', $direction->getId())
            ->andWhere('ST_INTERSECTS(t.geoJsonDetail,d.geoJsonDetail)=1');
        return $query->getQuery()->getResult();
    }

    /**
     * Find territories for an Address
     *
     * @param Address $address  The address
     * @return Territory[]|null       The territories
     */
    public function findAddressTerritories(Address $address)
    {
        $query = $this->repository->createQueryBuilder('t')
            ->join('\App\Geography\Entity\Address', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $address->getId())
            ->andWhere('ST_INTERSECTS(t.geoJsonDetail,a.geoJson)=1');
        return $query->getQuery()->getResult();
    }

    /**
     * Find territories of a point defined by its latitude and longitude
     *
     * @param float $latitude   Latitude of the point
     * @param float $longitude  Longitude of the point
     * @return Territory[]|null       The territories
     */
    public function findPointTerritories(float $latitude, float $longitude)
    {
        $conn = $this->entityManager->getConnection();

        // we get only structure's ids
        $sql = "SELECT t.id FROM territory t
        WHERE ST_INTERSECTS(t.geo_json_detail,ST_GEOMFROMTEXT('POINT($longitude $latitude)'))=1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
