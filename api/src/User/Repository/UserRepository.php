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
            $qb->andWhere(('u.newsSubscription = 1'));
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

    public function getUsersInContactWithCurrentUser(User $user)
    {
        $stmt = $this->entityManager->getConnection()->prepare('
        select * from user u join ask a on a.user_id = u.id where a.user_id != 19 and a.user_related_id ='.$user->getId().';
        ;');
        $stmt->execute();
        $scammerVictims = $stmt->fetchAll();

        $stmt = $this->entityManager->getConnection()->prepare('
        select * from user u join ask a on a.user_related_id = u.id where a.user_id = 19 and a.user_related_id !='.$user->getId().'
        ;');
        $stmt->execute();
        $scammerVictimsRelated = $stmt->fetchAll();

        return array_merge($scammerVictims, $scammerVictimsRelated);
    }
}
