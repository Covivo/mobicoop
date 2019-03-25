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
use App\Geography\Service\ZoneManager;
use App\Carpool\Service\ProposalManager;
use App\Geography\Entity\Direction;

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository
{
    CONST METERS_BY_DEGREE = 111319;
    
    private $repository;
    private $zoneManager;
    
    public function __construct(EntityManagerInterface $entityManager, ZoneManager $zoneManager)
    {
        $this->repository = $entityManager->getRepository(Proposal::class);
        $this->zoneManager = $zoneManager;
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
                return $this->findMatchingsForPunctualProposal($proposal, $excludeProposalUser);
                break;
            case Criteria::FREQUENCY_REGULAR:
                return $this->findMatchingsForRegularProposal($proposal);
                break;
        }
        
        return null;
    }
    
    /**
     * Search matchings for a punctual proposal.
     *
     * Here we search for proposal that have similar properties :
     * - drivers for passenger proposal, passengers for driver proposal
     * - similar dates
     * - similar times
     * - similar basic geographical zones
     *
     * It is a pre-filter, the idea is to limit the next step : the route calculations (that cannot be done directly in the model).
     * The fine time matching will be done during the route calculation process.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingsForPunctualProposal(Proposal $proposal, bool $excludeProposalUser=true)
    {
        // the "master" proposal is simply called the "proposal"
        // the potential matching proposals are called the "candidates"

        // we search the matchings in the proposal entity
        $query = $this->repository->createQueryBuilder('p')
        // we need the criteria (for the dates, number of seats...)
        ->join('p.criteria', 'c')
        // we need the directions and the geographical zones
        ->leftJoin('c.directionDriver', 'dd')->leftJoin('dd.zones','zd')
        ->leftJoin('c.directionPassenger', 'dp')->leftJoin('dp.zones','zp');

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
        
        // GEOGRAPHICAL ZONES
        
        // we search the zones where the user is passenger and/or driver
        if ($proposal->getCriteria()->isDriver()) {
            $zonesAsDriver = $proposal->getCriteria()->getDirectionDriver()->getZones();
            $zones = [];
            foreach ($zonesAsDriver as $zone) {
                $zones[] = $zone->getZoneid();
            }
            $query->andWhere('zp.thinness = :thinnessPassenger and zp.zoneid IN(' . implode(',',$zones) . ')');
            $query->setParameter('thinnessPassenger',$this->getPrecision($proposal->getCriteria()->getDirectionDriver()));
        }
        if ($proposal->getCriteria()->isPassenger()) {
            $zonesAsPassenger = $proposal->getCriteria()->getDirectionPassenger()->getZones();
            $zones = [];
            foreach ($zonesAsPassenger as $zone) {
                $zones[] = $zone->getZoneid();
            }
            $query->andWhere('zd.thinness = :thinnessDriver and zd.zoneid IN(' . implode(',',$zones) . ')');
            $query->setParameter('thinnessDriver',$this->getPrecision($proposal->getCriteria()->getDirectionPassenger()));
        }

        // DATES AND TIME

        // for a punctual proposal, we search for punctual or regular candidate proposals

        // dates :
        // - punctual candidates, we limit the search :
        //   - exactly to fromDate if strictDate is true
        //   - to the days after the fromDate and before toDate if it's defined (if the user wants to travel any day within a certain range)
        //   (@todo limit automatically the search to the x next days if toDate is not defined ?)
        // - regular candidates, we limit the search :
        //   - to the week day of the proposal

        // times :
        // we limit the search to the passengers that have their max starting time after the min starting time of the driver :
        //
        //      min             max
        //      >|-------D-------|
        //          |-------P-------|<
        //          min             max

        $setToDate = false;
        $setMinTime = false;
        $setMaxTime = false;

        // 'where' part of punctual candidates
        $punctualAndWhere = '(';
        if ($proposal->getCriteria()->isStrictDate()) {
            $punctualAndWhere .= 'c.frequency=' . Criteria::FREQUENCY_PUNCTUAL . ' and c.fromDate = :fromDate';
        } else {
            $punctualAndWhere .= 'c.frequency=' . Criteria::FREQUENCY_PUNCTUAL . ' and c.fromDate >= :fromDate';
            if (!is_null($proposal->getCriteria()->getToDate())) {
                $punctualAndWhere .= ' and c.fromDate <= :toDate';
                $setToDate = true;
            }
        }
        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
            $punctualAndWhere .= ' and (
                (c.isPassenger = 1 and c.maxTime >= :minTime) or 
                (c.isDriver = 1 and c.minTime <= :maxTime)
            )';
            $setMinTime = true;
            $setMaxTime = true;
        } elseif ($proposal->getCriteria()->isDriver()) {
            $punctualAndWhere .= ' and c.isPassenger = 1 and c.maxTime >= :minTime';
            $setMinTime = true;
        } else {
            $punctualAndWhere .= ' and c.isDriver = 1 and c.minTime <= :maxTime';
            $setMaxTime = true;
        }
        $punctualAndWhere .= ')';
        
        // 'where' part of regular candidates
        $regularDay = '';
        $regularTime = '';
        switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                        
            case 0:     // sunday
                        $regularDay = ' and c.sunCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.sunMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.sunMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.sunMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.sunMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 1:     // monday
                        $regularDay = ' and c.monCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.monMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.monMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.monMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.monMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 2:     // tuesday
                        $regularDay = ' and c.tueCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.tueMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.tueMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.tueMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.tueMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 3:     // wednesday
                        $regularDay = ' and c.wedCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.wedMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.wedMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.wedMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.wedMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 4:     // thursday
                        $regularDay = ' and c.thuCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.thuMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.thuMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.thuMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.thuMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 5:     //friday
                        $regularDay = ' and c.friCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.friMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.friMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.friMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.friMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
            case 6:     // saturday
                        $regularDay = ' and c.satCheck = 1';
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTime = ' and (
                                (c.isPassenger = 1 and c.satMaxTime >= :minTime) or 
                                (c.isDriver = 1 and c.satMinTime <= :maxTime)
                            )';
                            $setMinTime = true;
                            $setMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTime = ' and (c.isPassenger = 1 and c.satMaxTime >= :minTime)';
                            $setMinTime = true;
                        } else {
                            $regularTime = ' and (c.isDriver = 1 and c.satMinTime <= :maxTime)';
                            $setMaxTime = true;
                        }
                        break;
        }
        $regularAndWhere = '(c.frequency=' . Criteria::FREQUENCY_REGULAR . ' and c.fromDate <= :fromDate and c.toDate >= :fromDate' . $regularDay . $regularTime . ')';

        $query->andWhere('(' . $punctualAndWhere . ' or ' .$regularAndWhere . ')')
        ->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));

        if ($setToDate) {
            $query->setParameter('toDate', $proposal->getCriteria()->getToDate()->format('Y-m-d'));
        }
        if ($setMinTime) {
            $query->setParameter('minTime', $proposal->getCriteria()->getMinTime()->format('H:i'));
        }
        if ($setMaxTime) {
            $query->setParameter('maxTime', $proposal->getCriteria()->getMaxTime()->format('H:i'));
        }

        // we launch the request and return the result
        return $query->getQuery()->getResult();
    }
    
    /**
     * Search matchings for a regular proposal.
     *
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingsForRegularProposal(Proposal $proposal)
    {
        return null;
    }

    /**
     * Get the precision of the grid for a direction.
     * For now we divide the length of the direction by a 4 factor to estimate the precision degree.
     *
     * @param Direction $direction The direction to check
     * @return int The precision in degrees
     */
    private function getPrecision(Direction $direction)
    {
        $thinnesses = ProposalManager::THINNESSES;
        sort($thinnesses);
        $i = 0;
        $found = false;
        while (!$found) {
            if (($direction->getDistance()/4)<($thinnesses[$i]*self::METERS_BY_DEGREE)) {
                $found = true;
            } else {
                $i++;
            }
        }
        return $thinnesses[$i];
    }
}
