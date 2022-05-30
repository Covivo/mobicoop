<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Repository;

use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method null|Matching find($id, $lockMode = null, $lockVersion = null)
 * @method null|Matching findOneBy(array $criteria, array $orderBy = null)
 */
class MatchingRepository
{
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Matching::class);
        $this->entityManager = $entityManager;
    }

    public function find(int $id): ?Matching
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * Link related matchings for a Proposal
     * (link outward and return matchings).
     *
     * @param int $proposalId The proposal id
     */
    public function linkRelatedMatchings(int $proposalId)
    {
        $conn = $this->entityManager->getConnection();
        $sql = '
            UPDATE matching AS MRA
            INNER JOIN proposal AS POA ON POA.id = MRA.proposal_offer_id
            INNER JOIN proposal AS PRA ON PRA.id = MRA.proposal_request_id
            SET matching_linked_id = (
                SELECT MRR.id FROM matching AS MRR
                INNER JOIN proposal AS PRR ON PRR.id = MRR.proposal_request_id
                INNER JOIN proposal AS POR ON POR.id = MRR.proposal_offer_id
                WHERE
                    POR.id = POA.proposal_linked_id AND
                    PRR.id = PRA.proposal_linked_id
            )
            WHERE MRA.matching_linked_id IS NULL AND (MRA.proposal_offer_id = '.$proposalId.' OR MRA.proposal_request_id = '.$proposalId.')';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }

    /**
     * Link opposite matchings for a Proposal
     * (link matchings as driver and passenger).
     *
     * @param int $proposalId The proposal id
     */
    public function linkOppositeMatchings(int $proposalId)
    {
        $conn = $this->entityManager->getConnection();
        $sql = '
            UPDATE matching AS MR
            INNER JOIN proposal AS PO ON PO.id = MR.proposal_offer_id
            SET matching_opposite_id = (
                SELECT MO.id FROM matching AS MO
                INNER JOIN proposal AS PR ON PR.id = MO.proposal_request_id
                WHERE PO.id = PR.id AND MR.proposal_request_id = MO.proposal_offer_id
            )
            WHERE MR.matching_opposite_id IS NULL AND (MR.proposal_offer_id = '.$proposalId.')';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
    }

    public function getProposalMatchingAsOffersWithBothUsers(Proposal $proposal): ?array
    {
        $query = $this->repository->createQueryBuilder('m')
            ->join('m.proposalOffer', 'p')
            ->where('m.proposalRequest = :proposal')
            ->andWhere('p.user is not null')
            ->setParameter('proposal', $proposal)
        ;

        return $query->getQuery()->getResult();
    }

    public function getProposalMatchingAsRequestsWithBothUsers(Proposal $proposal): ?array
    {
        $query = $this->repository->createQueryBuilder('m')
            ->join('m.proposalRequest', 'p')
            ->where('m.proposalOffer = :proposal')
            ->andWhere('p.user is not null')
            ->setParameter('proposal', $proposal)
        ;

        return $query->getQuery()->getResult();
    }
}
