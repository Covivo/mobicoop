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

namespace App\RelayPoint\Repository;

use App\RelayPoint\Entity\RelayPoint;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method RelayPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelayPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelayPoint[]    findAll()
 * @method RelayPoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelayPointRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(RelayPoint::class);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Return all relaypoint with the given name and status.
     *
     * @param string $name
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The relay points found
     */
    public function findByNameAndStatus(string $name, int $status)
    {
        $query = $this->entityManager->createQuery("
            SELECT rp from App\RelayPoint\Entity\RelayPoint rp
            where rp.name like '%" . $name . "%' and rp.status = $status
        ");
        
        return $query->getResult()
        ;
    }
    
    /**
     * Return all relay points in the given territory.
     *
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The relay points found
     */
    public function findAllInTerritory(Territory $territory)
    {
        $query = $this->entityManager->createQuery("
            SELECT rp from App\RelayPoint\Entity\RelayPoint rp, a from App\Geography\Entity\Address a, App\Geography\Entity\Territory t
            where rp.address_id = a.id and t.id = " . $territory->getId() . "
            and ST_CONTAINS(t.geoJsonDetail,a.geoJson)=1
        ");
        
        return $query->getResult()
        ;
    }
}
