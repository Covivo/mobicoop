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

namespace App\Solidary\Repository;

use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Entity\SolidaryUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * SolidaryAsk Repository
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAskRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(SolidaryAsk::class);
    }


    public function find(int $id): ?SolidaryAsk
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

    public function findOneBy(array $criteria): ?SolidaryAsk
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Return the SolidaryAsk of a SolidarySolution if it exists
     *
     * @param SolidarySolution $solidarySolution
     * @return array
     */
    public function findBySolidarySolution(SolidarySolution $solidarySolution): array
    {
        $query = $this->repository->createQueryBuilder('sa')
        ->join('sa.solidarySolution', 'ss')
        ->where('sa.solidarySolution = :solidarySolution')
        ->setParameter('solidarySolution', $solidarySolution);

        return $query->getQuery()->getResult();
    }

    /**
     * Find the SolidaryAsk between two dates
     *
     * @param \DateTimeInterface $startDate Search startDate
     * @param \DateTimeInterface $endDate   Search endDate
     * @param bool $onlySolidaryTransport   True if we search only for Solidary transport
     * @return array
     */
    public function findBetweenTwoDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate, SolidaryUser $solidaryVolunteer = null, $onlySolidaryTransport = true): array
    {
        $query = $this->repository->createQueryBuilder('sa')
        ->join('sa.criteria', 'c')
        ->join('sa.solidarySolution', 'ss')
        ->join('ss.solidaryMatching', 'sm')
        ->where('c.fromDate >= :startDate and (c.toDate <= :endDate or c.toDate is null)');
        
        if (!is_null($solidaryVolunteer)) {
            $query->andWhere('sm.solidaryUser = :solidaryVolunteer');
        }
        
        if ($onlySolidaryTransport) {
            $query->andWhere('sm.matching is null');
        }
        
        $query->setParameter('startDate', $startDate->format("Y-m-d"))
        ->setParameter('endDate', $endDate->format("Y-m-d"));

        if (!is_null($solidaryVolunteer)) {
            $query->setParameter('solidaryVolunteer', $solidaryVolunteer);
        }


        return $query->getQuery()->getResult();
    }

    /**
     * Find the solidaryAsks of a solidary
     *
     * @param int $solidaryId Id of the Solidary
     * @return array|null
     */
    public function findSolidaryAsks(int $solidaryId): ?array
    {
        $query = $this->repository->createQueryBuilder('sa')
        ->join('sa.solidarySolution', 'ss')
        ->join('ss.solidary', 's')
        ->where('s.id = :solidaryId')
        ->setParameter('solidaryId', $solidaryId);

        return $query->getQuery()->getResult();
    }

    /**
     * Find the solidaryAsks of a given user as driver
     *
     * @param User $user        The User
     * @return array|null
     */
    public function findSolidaryAsksForDriver(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('sa')
        ->join('sa.solidarySolution', 'ss')
        ->join('ss.solidaryMatching', 'sm')
        ->leftJoin('sm.matching', 'm')
        ->leftJoin('m.proposalOffer', 'po')
        ->leftJoin('po.user', 'pou')
        ->leftJoin('sm.solidaryUser', 'su')
        ->leftJoin('su.user', 'suu')
        ->orWhere('pou.id = :user')
        ->orWhere('suu.id = :user')
        ->setParameter('user', $user->getId())
        ->orderBy('sa.updatedDate', 'DESC');

        return $query->getQuery()->getResult();
    }
}
