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
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method RelayPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelayPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelayPoint[]    findAll()
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


    public function find(int $id): ?RelayPoint
    {
        return $this->repository->find($id);
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
        $words = explode(" ", $name);
        $searchString = "rp.name like '%".implode("%' and rp.name like '%", $words)."%'";
        $queryString = "
            SELECT rp from App\RelayPoint\Entity\RelayPoint rp
            where ".$searchString." and rp.status = ".$status;

        $query = $this->entityManager->createQuery($queryString);
        
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

    /**
     * Find the public relaypoints and some private if the current user is entitled to (i.e community...)
     *
     * @param User|null $user The User who make the request
     * @return array|null     The relay points found
     */
    public function findRelayPoints(User $user=null)
    {
        $query = $this->repository->createQueryBuilder('rp');
        $query->where("rp.private is null or rp.private = 0");
        
        if (!is_null($user)) {
            $query->leftJoin('rp.community', 'c')
            ->leftJoin('c.communityUsers', 'cu')
            ->orWhere("cu.user = :user")
            ->orderBy('rp.id', 'ASC')
            ->setParameter('user', $user);
        }



        return $query->getQuery()->getResult();
    }
}
