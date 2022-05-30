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
 */

namespace App\Geography\Repository;

use App\Geography\Entity\Address;
use App\Geography\Entity\Territory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|Address find($id, $lockMode = null, $lockVersion = null)
 * @method null|Address findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Address::class);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function find(int $id): ?Address
    {
        return $this->repository->find($id);
    }

    /**
     * Return all addresses with the given name for the given user id.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The addresses found
     */
    public function findByName(string $name, int $userId): mixed
    {
        $query = $this->entityManager->createQuery("
            SELECT a from App\\Geography\\Entity\\Address a
            where a.name like '%".$name."%' and a.user = {$userId}
        ");

        return $query->getResult()
        ;
    }

    /**
     * Return all addresses in the given territory.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The addresses found
     */
    public function findAllInTerritory(Territory $territory): mixed
    {
        $query = $this->entityManager->createQuery('
            SELECT a from App\\Geography\\Entity\\Address a, App\\Geography\\Entity\\Territory t
            where t.id = '.$territory->getId().'
            and ST_CONTAINS(t.geoJsonDetail,a.geoJson)=1
        ');

        return $query->getResult()
        ;
    }

    /**
     * Find territories for an Address.
     *
     * @param Address $address The address
     *
     * @return null|Territory[] The territories
     */
    public function findAddressTerritories(Address $address): ?array
    {
        $query = $this->repository->createQueryBuilder('a')
            ->join('\App\Geography\Entity\Territory', 'territory')
            ->where('a.id = :id')
            ->setParameter('id', $address->getId())
            ->andWhere('ST_INTERSECTS(territory.geoJsonDetail,a.geoJson)=1')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find all minimal addresses (only latitude and logitude filled).
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The addresses found
     */
    public function findMinimalAddresses(): mixed
    {
        $query = $this->repository->createQueryBuilder('a')
            ->andWhere('a.houseNumber IS NULL')
            ->andWhere('a.street IS NULL')
            ->andWhere('a.streetAddress IS NULL')
            ->andWhere('a.postalCode IS NULL')
            ->andWhere('a.addressLocality IS NULL')
            ->andWhere('a.name IS NULL')
            ->andWhere('a.addressCountry IS NULL')
            ->andWhere('a.countryCode IS NULL')
            ->andWhere('a.county IS NULL')
            ->andWhere('a.localAdmin IS NULL')
            ->andWhere('a.macroCounty IS NULL')
            ->andWhere('a.macroRegion IS NULL')
            ->andWhere('a.region IS NULL')
            ->andWhere('a.subLocality IS NULL')
            ->andWhere('a.latitude IS NOT NULL')
            ->andWhere('a.longitude IS NOT NULL')
            ;

        return $query->getQuery()->getResult();
    }
}
