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
 **************************/

namespace App\Carpool\Repository;

use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Carpool\Entity\Criteria;

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Proposal::class);
    }
    
    /**
     * Find proposals matching the proposal passed as an argument.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal, bool $excludeProposalUser=true)
    {
        switch ($proposal->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                return $this->findMatchingForPunctualProposal($proposal, $excludeProposalUser);
                break;
            case Criteria::FREQUENCY_REGULAR:
                return $this->findMatchingForRegularProposal($proposal);
                break;
        }
        
        return null;
    }
    
    /**
     * Search matchings for a punctual proposal.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingForPunctualProposal(Proposal $proposal, bool $excludeProposalUser=true)
    {
        // we search the matchings in the proposal entity
        $query = $this->repository->createQueryBuilder('p')
        // we also need the criteria (for the dates, number of seats...)
        ->join('p.criteria', 'c');
        
        // do we exclude the user itself ?
        if ($excludeProposalUser) {
            $query->andWhere('p.user != :user')
            ->setParameter('user', $proposal->getUser());
        }
        
        // we search if the user can be passenger and/or driver
        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
            $query->andWhere('c.isDriver = 1 OR c.isPassenger = 1');
        } elseif ($proposal->getCriteria()->isDriver()) {
            $query->andWhere('c.isPassenger = 1');
        } elseif ($proposal->getCriteria()->isPassenger()) {
            $query->andWhere('c.isDriver = 1');
        }
        
        // DATES AND TIME

        // for a punctual proposal, we search for punctual or regular candidate proposals

        // - punctual candidates, we limit the search :
        //   - exactly to fromDate if strictDate is true
        //   - to the days after the fromDate and before toDate if it's defined (if the user wants to travel any day within a certain range)
        //   (@todo limit automatically the search to the x next days if toDate is not defined ?)
        // - regular candidates, we limit the search :
        //   - to the week day of the proposal

        $regularDay = '';
        $regularTime = '';
        switch ($proposal->getCriteria()->getFromDate()->format('w')) {
            case 0:    $regularDay = ' and c.sunCheck = 1';
                        $regularTime = ' and c.sunTime between ';
                        break;
            case 1:    $regularDay = ' and c.monCheck = 1';
                        break;
            case 2:    $regularDay = ' and c.tueCheck = 1';
                        break;
            case 3:    $regularDay = ' and c.wedCheck = 1';
                        break;
            case 4:    $regularDay = ' and c.thuCheck = 1';
                        break;
            case 5:    $regularDay = ' and c.friCheck = 1';
                        break;
            case 6:    $regularDay = ' and c.satCheck = 1';
                        break;
        }

        $setToDate = false;
        if ($proposal->getCriteria()->isStrictDate()) {
            $punctualAndWhere = '(c.frequency=' . Criteria::FREQUENCY_PUNCTUAL . ' and c.fromDate = :fromDate)';
        } else {
            $punctualAndWhere = '(c.frequency=' . Criteria::FREQUENCY_PUNCTUAL . ' and c.fromDate >= :fromDate';
            if (!is_null($proposal->getCriteria()->getToDate())) {
                $punctualAndWhere .= (' and c.fromDate <= :toDate');
                $setToDate = true;
            }
            $punctualAndWhere .= ')';
        }
        $regularAndWhere = '(c.frequency=' . Criteria::FREQUENCY_REGULAR . ' and c.fromDate <= :fromDate and c.toDate >= :fromDate' . $regularDay . ')';

        $query->andWhere('(' . $punctualAndWhere . ' or ' .$regularAndWhere . ')')
        ->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));
        if ($setToDate) {
            $query->setParameter('toDate', $proposal->getCriteria()->getToDate()->format('Y-m-d'));
        }

        // TIME
        $query->andWhere();
        
        // we launch the request and return the result
        return $query->getQuery()->getResult();
    }
    
    /**
     * Search matchings for a regular proposal.
     *
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingForRegularProposal(Proposal $proposal)
    {
        return null;
    }
}
