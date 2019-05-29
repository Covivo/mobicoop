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

    /**
     * Search if a direction intersects a given territory.
     *
     * @param Direction $direction
     * @param Territory $territory
     * @return void
     */
    public function directionIsInTerritory(Direction $direction, Territory $territory)
    {
        $result = false;
        // we use a batch of 100 points to avoid sql failure
        $batch = 100;
        $stop = false;
        $points = $direction->getPoints();
        $nbLoops = 0;
        while (!$stop) {
            $sql = "SELECT ST_INTERSECTS(t.detail,ST_GeomFromText('Multipoint(";
            $start = $nbLoops*$batch;
            $end = min($start+$batch, count($points)); // count + 1 ???
            for ($i=$start;$i<$end;$i++) {
                $address = $points[$i];
                $sql .= $address->getLongitude() . " " . $address->getLatitude() . ",";
            }
            $sql = rtrim($sql, ',');
            $sql .= ")')) as inTerritory from App\Geography\Entity\Territory t where t.id = " . $territory->getId();
            $query = $this->entityManager->createQuery($sql);
            if ($query->getResult()[0]['inTerritory'] == 1) {
                $result = true;
                $stop = true;
            }
            if ($end == count($points)) {
                $stop = true;
            }
            $nbLoops++;
        }
        return $result;
    }
}
