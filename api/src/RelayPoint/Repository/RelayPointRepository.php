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

namespace App\RelayPoint\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Community\Entity\Community;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method null|RelayPoint find($id, $lockMode = null, $lockVersion = null)
 * @method null|RelayPoint findOneBy(array $criteria, array $orderBy = null)
 * @method RelayPoint[]    findAll()
 */
class RelayPointRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry, iterable $collectionExtensions)
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
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
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The relay points found
     */
    public function findByNameAndStatus(string $name, int $status)
    {
        $words = explode(' ', $name);
        $searchString = "rp.name like '%".implode("%' and rp.name like '%", $words)."%'";
        $queryString = '
            SELECT rp from App\\RelayPoint\\Entity\\RelayPoint rp
            where '.$searchString.' and rp.status = '.$status;

        $query = $this->entityManager->createQuery($queryString);

        return $query->getResult();
    }

    public function findByNameLocalityAndStatus(string $name, int $status)
    {
        $words = explode(' ', $name);
        $searchString = "(rp.name like '%".implode("%' and rp.name like '%", $words)."%')";
        $searchLocality = "(a.addressLocality like '%".implode("%' and a.addressLocality like '%", $words)."%')";

        $query = $this->repository->createQueryBuilder('rp')
            ->leftJoin('rp.address', 'a')
            ->where($searchString)
            ->orwhere($searchLocality)
            ->andWhere('rp.status = :status')
            ->setParameter('status', $status)
        ;

        return $query->getQuery()->getResult();
    }

    public function findByParams(string $name, int $status, array $params)
    {
        var_dump($params);

        exit;
        $words = explode(' ', $name);
        $searchString = "(rp.name like '%".implode("%' and rp.name like '%", $words)."%')";
        $searchLocality = "(a.addressLocality like '%".implode("%' and a.addressLocality like '%", $words)."%')";

        $query = $this->repository->createQueryBuilder('rp')
            ->leftJoin('rp.address', 'a')
            ->where($searchString)
            ->orwhere($searchLocality)
            ->andWhere('rp.status = :status')
            ->setParameter('status', $status)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Return all relay points in the given territory.
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed The relay points found
     */
    public function findAllInTerritory(Territory $territory)
    {
        $query = $this->entityManager->createQuery('
            SELECT rp from App\\RelayPoint\\Entity\\RelayPoint rp, a from App\\Geography\\Entity\\Address a, App\\Geography\\Entity\\Territory t
            where rp.address_id = a.id and t.id = '.$territory->getId().'
            and ST_CONTAINS(t.geoJsonDetail,a.geoJson)=1
        ');

        return $query->getResult()
        ;
    }

    /**
     * Find the public relaypoints and some private if the current user is entitled to (i.e community...).
     *
     * @param null|User $user          The User who make the request
     * @param array     $context       The operation context
     * @param string    $operationName The operation name
     *
     * @return null|array The relay points found
     */
    public function findRelayPoints(User $user = null, array $context = [], string $operationName): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('rp');

        if (!is_null($user) && 'public' == $operationName) {
            // for public list, we filter to get only publi relay points, or the ones related to a community where the user is a member
            $query->where('(rp.private is null or rp.private = 0) and rp.status = '.RelayPoint::STATUS_ACTIVE)
                ->leftJoin('rp.community', 'c')
                ->leftJoin('c.communityUsers', 'cu')
                ->orWhere('cu.user = :user')
                ->setParameter('user', $user)
            ;
        } else {
            $query->where('rp.status = '.RelayPoint::STATUS_ACTIVE);
        }
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, RelayPoint::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(RelayPoint::class, $operationName)) {
                return $extension->getResult($query, RelayPoint::class, $operationName);
            }
        }

        return $query->getQuery()->getResult();
    }
}
