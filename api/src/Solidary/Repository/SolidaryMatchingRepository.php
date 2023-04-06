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

namespace App\Solidary\Repository;

use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryMatching;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryMatchingRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(SolidaryMatching::class);
    }

    public function find(int $id): ?SolidaryMatching
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

    public function findOneBy(array $criteria): ?SolidaryMatching
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Link related carpool solidary matchings for a Solidary
     * (link outward and return matchings).
     *
     * @param int $solidaryId The solidary id
     */
    public function linkRelatedSolidaryMatchings(int $solidaryId)
    {
        $conn = $this->entityManager->getConnection();
        $sql = '
            UPDATE solidary_matching AS SM1
            INNER JOIN matching as M1 ON M1.id = SM1.matching_id
            SET solidary_matching_linked_id = (
                SELECT SM2.id FROM solidary_matching AS SM2
                INNER JOIN matching as M2 ON M2.id = SM2.matching_id
                WHERE SM2.matching_id = M1.matching_linked_id
            )
            WHERE SM1.solidary_matching_linked_id IS NULL AND SM1.solidary_id = '.$solidaryId;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Find the previous solidary Matching for a solidary in Transport context.
     *
     * @param Solidary $proposal
     */
    public function findSolidaryMatchingTransportOfSolidary(Solidary $solidary): ?array
    {
        $query = $this->repository->createQueryBuilder('sm')
            ->join('sm.solidary', 's')
            ->where('sm.solidary = :solidary')
            ->andWhere('sm.solidaryUser is not null')
            ->setParameter('solidary', $solidary)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find the previous solidary Matching for a solidary in Carpool context.
     *
     * @param Solidary $proposal
     */
    public function findSolidaryMatchingCarpoolOfSolidary(Solidary $solidary): ?array
    {
        $query = $this->repository->createQueryBuilder('sm')
            ->join('sm.solidary', 's')
            ->join('sm.solidaryUser', 'su')
            ->innerJoin('su.user', 'u', 'WITH', 'u.status != :pseudonymisationStatus')
            ->where('sm.solidary = :solidary')
            ->andWhere('sm.matching is not null')
            ->setParameters([
                'pseudonymisationStatus' => User::STATUS_PSEUDONYMIZED,
                'solidary' => $solidary,
            ])
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find the Ask related to a SolidaryMatching.
     */
    public function findAskOfSolidaryMatching(SolidaryMatching $solidaryMatching): ?SolidaryAsk
    {
        $query = $this->repository->createQueryBuilder('sm')
            ->join('sm.solidarySolution', 'ss')
            ->join('ss.solidaryAsk', 'sa')
            ->join('sm.solidaryUser', 'su')
            ->innerJoin('su.user', 'u', 'WITH', 'u.status != :pseudonymisationStatus')
            ->where('sm = :solidaryMatching')
            ->setParameters([
                'pseudonymisationStatus' => User::STATUS_PSEUDONYMIZED,
                'solidaryMatching' => $solidaryMatching,
            ])
        ;

        $results = $query->getQuery()->getResult();
        if (count($results) > 0) {
            return $results[0]->getSolidarySolution()->getSolidaryAsk();
        }

        return null;
    }
}
