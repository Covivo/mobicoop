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

use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\User\Entity\Review;
use App\User\Entity\User;
use DateTime;

class AskRepository
{
    private $repository;
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Ask::class);
    }

    public function find(int $id): ?Ask
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }
    
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }
    
    /**
     * Get the Asks of a user
     *
     * @param User $user        The User
    * @param int $filter       0 : All the Asks, 1 : Only Ask without SolidaryAsk, 2 Only Ask with SolidaryAsk
     * @return Ask[]|null
     */
    public function findAskByUser(User $user, $filter=Ask::ALL_ASKS)
    {
        $query = $this->repository->createQueryBuilder('a');

        // If it's a Solidary request and the user is a beneficiary, we return no messages. Beneficiaries can't see solidary exchanges
        if ($filter==Ask::ASKS_WITH_SOLIDARY && !is_null($user->getSolidaryUser()) && $user->getSolidaryUser()->isBeneficiary()) {
            return [];
        }
        
        if ($filter==Ask::ASKS_WITHOUT_SOLIDARY) {
            $query->leftJoin('a.solidaryAsk', 'sa');
        } elseif ($filter==Ask::ASKS_WITH_SOLIDARY) {
            $query->join('a.solidaryAsk', 'sa');
        }
        
        $query->where('(a.user = :user or a.userRelated = :user)');
        
        if ($filter==Ask::ASKS_WITHOUT_SOLIDARY) {
            $query->andWhere('sa is null');
        }

        $query->setParameter('user', $user)
        ->orderBy('a.updatedDate', 'DESC');
        
        return $query->getQuery()->getResult();
    }

    public function findAskByAsker(User $user)
    {
        $query = $this->repository->createQueryBuilder('a')
            ->where('(a.user = :user)')
            ->setParameter('user', $user)
            ->orderBy('a.updatedDate', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function findAskForAd(Proposal $proposal, User $user, array $statuses)
    {
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.matching', 'm')
        ->join('m.proposalOffer', 'o')
        ->join('m.proposalRequest', 'r')
        ->where('a.status IN (:statuses)')
        ->andWhere('(m.proposalOffer = :proposal or m.proposalRequest= :proposal) and (o.user = :user or r.user= :user)')
        ->setParameter('statuses', $statuses)
        ->setParameter('proposal', $proposal)
        ->setParameter('user', $user)
        ;
        return $query->getQuery()->getResult();
    }

    /**
     * Find accepted asks between the given dates, for an optional given user
     *
     * @param DateTime $fromDate    The start date
     * @param DateTime $toDate      The end date
     * @param User|null $user       The user
     * @return Ask[]|null           The asks if found
     */
    public function findAcceptedAsksForPeriod(DateTime $fromDate, DateTime $toDate, ?User $user = null)
    {
        // we will need the different week number days between fromDate and toDate
        $days = [];
        $curDate = clone $fromDate;
        $continue = true;
        while ($continue) {
            if (!in_array($curDate->format('w'), $days)) {
                $days[] = $curDate->format('w');
            }
            if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d') || count($days) == 7) {
                $continue = false;
            } else {
                $curDate->modify('+1 day');
            }
        }
        // we create the regular where clause
        $regularWhereArray = [];
        foreach ($days as $day) {
            switch ($day) {
                case 0:
                    $regularWhereArray[$day] = "(c.sunCheck = 1)";
                    break;
                case 1:
                    $regularWhereArray[$day] = "(c.monCheck = 1)";
                    break;
                case 2:
                    $regularWhereArray[$day] = "(c.tueCheck = 1)";
                    break;
                case 3:
                    $regularWhereArray[$day] = "(c.wedCheck = 1)";
                    break;
                case 4:
                    $regularWhereArray[$day] = "(c.thuCheck = 1)";
                    break;
                case 5:
                    $regularWhereArray[$day] = "(c.friCheck = 1)";
                    break;
                case 6:
                    $regularWhereArray[$day] = "(c.satCheck = 1)";
                    break;
            }
        }
        $regularWhere = implode(' or ', $regularWhereArray);
        // note : for regular proposals, we need to get the accepted asks that can have days in the given range
        // => accepted asks that have their fromDate <= max date of the range and their toDate >= min date of the range (trust me, that's it :))
        // we will eventually check later for each day in the range if it's really carpooled
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.criteria', 'c')
        ->where('(a.status = :accepted_driver or a.status = :accepted_passenger)')
        ->andWhere('(
            (
                c.frequency = :punctual and c.fromDate between :fromDate and :toDate
            ) 
            or 
            (
                c.frequency = :regular and c.fromDate <= :toDate and c.toDate >= :fromDate and
                (' . $regularWhere . ')
            )
        )')
        ->setParameter('accepted_driver', Ask::STATUS_ACCEPTED_AS_DRIVER)
        ->setParameter('accepted_passenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ->setParameter('punctual', Criteria::FREQUENCY_PUNCTUAL)
        ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
        ->setParameter('fromDate', $fromDate->format('Y-m-d'))
        ->setParameter('toDate', $toDate->format('Y-m-d'))
        ;

        if (!is_null($user)) {
            $query->andWhere('(a.user = :user or a.userRelated = :user)')
            ->setParameter('user', $user);
        }
                
        return $query->getQuery()->getResult();
    }

    /**
     * Find pending asks between the given dates, for an optional given user
     *
     * @param DateTime $fromDate    The start date
     * @param DateTime $toDate      The end date
     * @param User|null $user       The user
     * @return Ask[]|null           The asks if found
     */
    public function findPendingAsksForPeriod(DateTime $fromDate, DateTime $toDate, ?User $user = null)
    {
        // we will need the different week number days between fromDate and toDate
        $days = [];
        $curDate = clone $fromDate;
        $continue = true;
        while ($continue) {
            if (!in_array($curDate->format('w'), $days)) {
                $days[] = $curDate->format('w');
            }
            if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d') || count($days) == 7) {
                $continue = false;
            } else {
                $curDate->modify('+1 day');
            }
        }
        // we create the regular where clause
        $regularWhereArray = [];
        foreach ($days as $day) {
            switch ($day) {
                case 0:
                    $regularWhereArray[$day] = "(c.sunCheck = 1)";
                    break;
                case 1:
                    $regularWhereArray[$day] = "(c.monCheck = 1)";
                    break;
                case 2:
                    $regularWhereArray[$day] = "(c.tueCheck = 1)";
                    break;
                case 3:
                    $regularWhereArray[$day] = "(c.wedCheck = 1)";
                    break;
                case 4:
                    $regularWhereArray[$day] = "(c.thuCheck = 1)";
                    break;
                case 5:
                    $regularWhereArray[$day] = "(c.friCheck = 1)";
                    break;
                case 6:
                    $regularWhereArray[$day] = "(c.satCheck = 1)";
                    break;
            }
        }
        $regularWhere = implode(' or ', $regularWhereArray);
        // note : for regular proposals, we need to get the pending asks that can have days in the given range
        // => pending asks that have their fromDate <= max date of the range and their toDate >= min date of the range (trust me, that's it :))
        // we will eventually check later for each day in the range if it's really carpooled
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.criteria', 'c')
        ->where('(a.status = :pending_driver or a.status = :pending_passenger)')
        ->andWhere('(
            (
                c.frequency = :punctual and c.fromDate between :fromDate and :toDate
            ) 
            or 
            (
                c.frequency = :regular and c.fromDate <= :toDate and c.toDate >= :fromDate and
                (' . $regularWhere . ')
            )
        )')
        ->setParameter('pending_driver', Ask::STATUS_PENDING_AS_DRIVER)
        ->setParameter('pending_passenger', Ask::STATUS_PENDING_AS_PASSENGER)
        ->setParameter('punctual', Criteria::FREQUENCY_PUNCTUAL)
        ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
        ->setParameter('fromDate', $fromDate->format('Y-m-d'))
        ->setParameter('toDate', $toDate->format('Y-m-d'))
        ;

        if (!is_null($user)) {
            $query->andWhere('(a.user = :user or a.userRelated = :user)')
            ->setParameter('user', $user);
        }
                
        return $query->getQuery()->getResult();
    }


    /**
     * Find accepted asks for a given user (or between two specific users)
     *
     * @param User|null $user   The user
     * @param User|null $user2   The second user if we want to check between two specific users
     * @return Ask[]|null       The asks if found
     */
    public function findAcceptedAsksForUser(User $user, User $user2=null)
    {
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.criteria', 'c')
        ->where('a.status = :accepted_driver or a.status = :accepted_passenger');

        if (!is_null($user2)) {
            $query
            ->andWhere('(a.user = :user and a.userRelated = :user2) or (a.user = :user2 and a.userRelated = :user)')
            ->setParameter('user', $user)
            ->setParameter('user2', $user2);
        } else {
            $query
            ->andWhere('a.user = :user or a.userRelated = :user')
            ->setParameter('user', $user);
        }

        $query
        ->setParameter('accepted_driver', Ask::STATUS_ACCEPTED_AS_DRIVER)
        ->setParameter('accepted_passenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ;
                
        return $query->getQuery()->getResult();
    }

    /**
     * Find accepted regular asks for a given user as a driver
     *
     * @param User|null $user   The user
     * @return Ask[]|null       The asks if found
     */
    public function findAcceptedRegularAsksForUserAsDriver(User $user)
    {
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.criteria', 'c')
        ->where('c.frequency = :regular')
        ->andWhere('(a.status = :accepted_driver and a.userRelated = :user) or (a.status = :accepted_passenger and a.user = :user)')
        ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
        ->setParameter('accepted_driver', Ask::STATUS_ACCEPTED_AS_DRIVER)
        ->setParameter('accepted_passenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ->setParameter('user', $user)
        ;
                
        return $query->getQuery()->getResult();
    }

    /**
     * Find accepted regular asks for a given user as a passenger
     *
     * @param User|null $user   The user
     * @return Ask[]|null       The asks if found
     */
    public function findAcceptedRegularAsksForUserAsPassenger(User $user)
    {
        $query = $this->repository->createQueryBuilder('a')
        ->join('a.criteria', 'c')
        ->where('c.frequency = :regular')
        ->andWhere('(a.status = :accepted_driver and a.user = :user) or (a.status = :accepted_passenger and a.userRelated = :user)')
        ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
        ->setParameter('accepted_driver', Ask::STATUS_ACCEPTED_AS_DRIVER)
        ->setParameter('accepted_passenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ->setParameter('user', $user)
        ;
                
        return $query->getQuery()->getResult();
    }

    public function countCarpoolersConnected()
    {
        $query = $this->repository->createQueryBuilder('a')
        ->select('count(a.id)');
        return $query->getQuery()->getSingleScalarResult();
    }
}
