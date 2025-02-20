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
 */

namespace App\Journey\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Carpool\Ressource\Ad;
use App\Journey\Entity\Journey;
use App\Rdex\Entity\RdexJourney;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class JourneyRepository
{
    public const HAVERSINE_DISTANCE = 6371000;

    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;
    private $collectionExtensions;
    private $rdexAlternativeMathingCircleRadius;

    public function __construct(
        EntityManagerInterface $entityManager,
        iterable $collectionExtensions,
        int $rdexAlternativeMathingCircleRadius
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Journey::class);
        $this->collectionExtensions = $collectionExtensions;
        $this->rdexAlternativeMathingCircleRadius = $rdexAlternativeMathingCircleRadius;
    }

    public function find(int $id): ?Journey
    {
        return $this->repository->find($id);
    }

    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Journey
    {
        return $this->repository->findOneBy($criteria);
    }

    public function getAllFrom(array $origin, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')
            ->where("j.origin in (:origins')")
            ->setParameter('origins', $origin)
        ;
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                return $extension->getResult($query, Journey::class, $operationName);
            }
        }

        return $query->getQuery()->getResult();
    }

    public function getDestinationsForOrigin(array $origin)
    {
        $query = $this->repository->createQueryBuilder('j')
            ->select('j.origin,j.destination')
            ->distinct()
            ->where('j.origin in (:origins)')
            ->setParameter('origins', $origin)
            ->orderBy('j.destination')
        ;

        return $query->getQuery()->getResult();
    }

    public function getAllTo(array $destination, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')->where("j.destination in ('".implode("','", $destination)."')");
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                return $extension->getResult($query, Journey::class, $operationName);
            }
        }

        return $query->getQuery()->getResult();
    }

    public function getOriginsForDestination(array $destination)
    {
        $query = $this->repository->createQueryBuilder('j')
            ->select('j.origin,j.destination')
            ->distinct()
            ->where('j.destination IN (:destinations)')
            ->setParameter('destinations', $destination)
            ->orderBy('j.origin')
        ;

        return $query->getQuery()->getResult();
    }

    public function getAllFromTo(array $origin, array $destination, string $operationName, array $context = []): PaginatorInterface
    {
        $query = $this->repository->createQueryBuilder('j')
            ->where('j.origin in (:origins) AND j.destination in (:destinations)')
            ->orderBy('j.fromDate', 'asc')
            ->setParameter('origins', $origin)
            ->setParameter('destinations', $destination)
        ;
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $queryNameGenerator, Journey::class, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(Journey::class, $operationName)) {
                return $extension->getResult($query, Journey::class, $operationName);
            }
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Get the popular journeys
     * (see .env for the max number and criteria).
     *
     * @param int $popularJourneyMinOccurences Minimum occurences of the journey to be considered popular
     * @param int $popularJourneyHomeMaxNumber Maximum number of returned journeys
     *
     * @return Journey[]
     */
    public function getPopularJourneys(int $popularJourneyMinOccurences, int $popularJourneyHomeMaxNumber): array
    {
        $conn = $this->entityManager->getConnection();
        $sql = 'SELECT origin, destination, latitude_origin, longitude_origin, latitude_destination, longitude_destination, count(id) as occurences
                FROM `journey`
                GROUP BY origin, destination
                HAVING occurences >= '.$popularJourneyMinOccurences.'
                ORDER BY occurences desc
                LIMIT 0,'.$popularJourneyHomeMaxNumber;

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getJourneysByHaversineDistance(array $parameters): array
    {
        $conn = $this->entityManager->getConnection();

        $sql = 'SELECT *,
                ('.self::HAVERSINE_DISTANCE.' * acos(
                    cos(radians('.$parameters['from']['latitude'].')) *
                    cos(radians(latitude_origin)) *
                    cos(radians(longitude_origin) - radians('.$parameters['from']['longitude'].')) +
                    sin(radians('.$parameters['from']['latitude'].')) *
                    sin(radians(latitude_origin))
                )) AS distance_origin,
                ('.self::HAVERSINE_DISTANCE.' * acos(
                    cos(radians('.$parameters['to']['latitude'].')) *
                    cos(radians(latitude_destination)) *
                    cos(radians(longitude_destination) - radians('.$parameters['to']['longitude'].')) +
                    sin(radians('.$parameters['to']['latitude'].')) *
                    sin(radians(latitude_destination))
                )) AS distance_destination
            FROM journey ';

        $where = 'WHERE to_date >= CURDATE() ';

        $where = $this->_addRolesParameter($where, $parameters);
        $where = $this->_addFrequencyParameter($where, $parameters);
        $where = $this->_addDatesParameters($where, $parameters, 'outward');
        $where = $this->_addDatesParameters($where, $parameters, 'return');

        $having = 'HAVING distance_origin <= '.$this->rdexAlternativeMathingCircleRadius.' AND distance_destination <= '.$this->rdexAlternativeMathingCircleRadius.' ';

        $order = 'ORDER BY distance_origin, distance_destination';

        $sql .= $where.$having.$order;

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function _addRolesParameter(string $currentWhereClause, array $parameters): string
    {
        $lookingForDriver = isset($parameters['driver']['state']) && 1 == $parameters['driver']['state'];
        $lookingForPassenger = isset($parameters['passenger']['state']) && 1 == $parameters['passenger']['state'];

        if ($lookingForDriver && !$lookingForPassenger) {
            return $currentWhereClause.'AND role in ('.Ad::ROLE_DRIVER.', '.Ad::ROLE_DRIVER_OR_PASSENGER.') ';
        }

        if (!$lookingForDriver && $lookingForPassenger) {
            return $currentWhereClause.'AND role in ('.Ad::ROLE_PASSENGER.', '.Ad::ROLE_DRIVER_OR_PASSENGER.') ';
        }

        return $currentWhereClause;
    }

    private function _addFrequencyParameter(string $currentWhereClause, array $parameters): string
    {
        $frequency = null;
        if (isset($parameters['frequency']) && RdexJourney::FREQUENCY_REGULAR == $parameters['frequency']) {
            $frequency = 2;
        } elseif (isset($parameters['frequency']) && RdexJourney::FREQUENCY_PUNCTUAL == $parameters['frequency']) {
            $frequency = 1;
        }

        if (!is_null($frequency)) {
            $currentWhereClause .= 'AND frequency = '.$frequency.' ';
        }

        return $currentWhereClause;
    }

    private function _addDatesParameters(string $currentWhereClause, array $parameters, string $way): string
    {
        /*
         *For a trip valid from_date:02/12/2024 to_date:02/12/2025
         *
         *I want to go at least minDate:01/03/2025
         *
         *It means my minDate>=from_date
         *
         *I want to go at most maxDate:01/03/2025
         *
         *It means my maxDate >= from_date
         *
         *
         *I want to go at least  minDate:01/03/2025 and at most maxDate:01/03/2025
         *
         *minDate>=from_date && maxDate >= from_date
         */

        if (isset($parameters[$way]['now'], $parameters[$way]['maxdate']) && $this->isValidDate($parameters[$way]['mindate']) && $this->isValidDate($parameters[$way]['maxdate'])) {
            $currentWhereClause .= sprintf(
                'AND "%s" >= from_date AND "%s" >= from_date ',
                $parameters[$way]['mindate'],
                $parameters[$way]['maxdate']
            );
        } elseif (isset($parameters[$way]['mindate']) && $this->isValidDate($parameters[$way]['mindate'])) {
            $currentWhereClause .= sprintf(
                'AND "%s" >= from_date ',
                $parameters[$way]['mindate']
            );
        } elseif (isset($parameters[$way]['maxdate']) && $this->isValidDate($parameters[$way]['maxdate'])) {
            $currentWhereClause .= sprintf(
                'AND "%s" >= from_date ',
                $parameters[$way]['maxdate']
            );
        }

        return $currentWhereClause;
    }

    private function isValidDate($dateString)
    {
        try {
            $date = new \DateTime($dateString);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
