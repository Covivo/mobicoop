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
 **************************/

namespace App\Carpool\Repository;

use App\Carpool\Entity\Journey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\DataProvider\PaginatorInterface;

class JourneyRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;
    private $collectionExtensions;
    
    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Journey::class);
        $this->collectionExtensions = $collectionExtensions;
    }

    public function find(int $id): ?Journey
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Journey
    {
        return $this->repository->findOneBy($criteria);
    }

    public function getAllFrom(array $origin, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')->where("j.origin in ('" . implode("','", $origin) . "')");
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                $result = $extension->getResult($query, Journey::class, $operationName);
                return $result;
            }
        }

        return $query->getQuery()->getResult();
    }

    public function getAllTo(array $destination, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')->where("j.destination in ('" . implode("','", $destination) . "')");
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                $result = $extension->getResult($query, Journey::class, $operationName);
                return $result;
            }
        }

        return $query->getQuery()->getResult();
    }

    public function getAllFromTo(array $origin, array $destination, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')->where("j.origin in ('" . implode("','", $origin) . "') AND j.destination in ('" . implode("','", $destination) . "')");
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                $result = $extension->getResult($query, Journey::class, $operationName);
                return $result;
            }
        }

        return $query->getQuery()->getResult();
    }
}
