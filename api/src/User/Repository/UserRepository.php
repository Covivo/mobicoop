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

namespace App\User\Repository;

use App\Community\Entity\Community;
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Entity\Structure;
use App\Solidary\Exception\SolidaryException;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class UserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->repository = $entityManager->getRepository(User::class);
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the users.
     *
     * @return null|User
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Find All the users by criteria.
     *
     * @param null|mixed $limit
     * @param null|mixed $offset
     *
     * @return null|User
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?User
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get all the users in the communities given.
     *
     * @param null|mixed $acceptEmail
     *
     * @return null|User
     */
    public function getUserInCommunity(Community $community, $acceptEmail = null)
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->leftJoin('u.communityUsers', 'c')
            ->andWhere('c.community = :community')
            ->setParameter('community', $community)
        ;

        if (null != $acceptEmail) {
            $qb->andWhere('u.newsSubscription = 1');
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Users with a specific type of SolidaryUser.
     *
     * @param string $type    Type of SolidaryUser (Beneficiary or Volunteer)
     * @param array  $filters Optionnal filters
     */
    public function findUsersBySolidaryUserType(string $type = null, array $filters = null, Structure $structureAdmin = null): ?array
    {
        $this->logger->info('Start findUsersBySolidaryUserType');
        $query = $this->repository->createQueryBuilder('u')
            ->join('u.solidaryUser', 'su')
        ;

        // filter by structure
        if (!is_null($structureAdmin)) {
            $query->join('su.solidaryUserStructures', 'sus');
        }

        // Type
        if (SolidaryBeneficiary::TYPE == $type) {
            $query->where('su.beneficiary = true');
        } elseif (SolidaryVolunteer::TYPE == $type) {
            $query->where('su.volunteer = true');
        } else {
            throw new SolidaryException(SolidaryException::TYPE_SOLIDARY_USER_UNKNOWN);
        }

        // Filters
        if (!is_null($filters)) {
            foreach ($filters as $filter => $value) {
                $query->andWhere('u.'.$filter." like '%".$value."%'");
            }
        }

        // Structure filter
        if (!is_null($structureAdmin)) {
            $query->andWhere('sus.structure = :structure');
            $query->setParameter('structure', $structureAdmin);
        }

        // var_dump($structureAdmin->getId());die;
        return $query->getQuery()->getResult();
    }

    /**
     * Get users by their id if they accept emailing.
     *
     * @param array $ids The ids of the users
     *
     * @return null|array The users
     */
    public function findDeliveriesByIds(array $ids)
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.id IN(:ids) and u.newsSubscription=1')
            ->setParameter('ids', $ids)
            ->getQuery()->getResult();
    }

    /**
     * Count the active users (with a connection in the last 6 months).
     *
     * @return int
     */
    public function countActiveUsers(): ?int
    {
        $now = new \DateTime();
        $last6Months = $now->modify('-6 months');

        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.lastActivityDate >= :last6months')
            ->setParameter('last6months', $last6Months)
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Count users.
     *
     * @return int
     */
    public function countUsers(): ?int
    {
        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findUserWithNoAdSinceXDays(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('- '.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT u.id
            FROM user u
            LEFT JOIN proposal p on p.user_id = u.id and p.private=0
            WHERE DATE(u.created_date) = '".$createdDate."'
            GROUP BY u.id
            HAVING COUNT(p.id) = 0"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findNewlyRegisteredUsers(): ?array
    {
        $now = (new \DateTime('now'));
        $yesterday = $now->modify('-1 day')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.createdDate = :yesterday')
            ->setParameter('yesterday', $yesterday)
        ;

        return $query->getQuery()->getResult();
    }

    public function findUserWithOlderThanXDaysAd(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('-'.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT ponct.id
            FROM
                (SELECT id
                    FROM
                        (SELECT u.id , max(p.created_date) AS maxdate
                        FROM user u
                            INNER JOIN proposal p ON p.user_id = u.id
                            INNER JOIN criteria c ON c.id = p.criteria_id
                        WHERE p.private=0 AND c.frequency=1
                        GROUP BY u.id) AS maxpropdate
                    WHERE DATE(maxdate) = '".$createdDate."') AS ponct
                LEFT JOIN
                    (SELECT u.id
                    FROM user u
                        INNER JOIN proposal p ON p.user_id = u.id
                        INNER JOIN criteria c ON c.id = p.criteria_id
                    WHERE p.private = 0 AND c.frequency= 2 AND c.to_date >= NOW()
                    GROUP BY u.id) AS regul ON regul.id = ponct.id
            WHERE regul.id IS NULL"
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByLastActivityDate(\DateTime $lastActivityDate): ?array
    {
        $query = $this->repository->createQueryBuilder('u')
            ->where('u.lastActivityDate >= :lastActivityDateBottom')
            ->andwhere('u.lastActivityDate <= :lastActivityDateUp')
            ->andwhere('u.status <> :statusPseudonymized')
            ->setParameter('lastActivityDateBottom', $lastActivityDate->format('Y-m-d').' 00:00:00')
            ->setParameter('lastActivityDateUp', $lastActivityDate->format('Y-m-d').' 23:59:59')
            ->setParameter('statusPseudonymized', User::STATUS_PSEUDONYMIZED)
        ;

        return $query->getQuery()->getResult();
    }

    public function findBeforeLastActivityDate(\DateTime $lastActivityDate): ?array
    {
        $query = $this->repository->createQueryBuilder('u')
            ->andwhere('u.lastActivityDate <= :lastActivityDateUp')
            ->andwhere('u.status <> :statusPseudonymized')
            ->setParameter('lastActivityDateUp', $lastActivityDate->format('Y-m-d').' 23:59:59')
            ->setParameter('statusPseudonymized', User::STATUS_PSEUDONYMIZED)
        ;

        return $query->getQuery()->getResult();
    }
}
