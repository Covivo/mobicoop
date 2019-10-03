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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Criteria;
use App\Geography\Service\ZoneManager;
use App\Geography\Entity\Direction;
use App\User\Service\UserManager;
use App\Community\Entity\Community;

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository
{
    const METERS_BY_DEGREE = 111319;
    
    private $repository;
    private $zoneManager;
    private $userManager;
    
    public function __construct(EntityManagerInterface $entityManager, ZoneManager $zoneManager, UserManager $userManager)
    {
        $this->repository = $entityManager->getRepository(Proposal::class);
        $this->zoneManager = $zoneManager;
        $this->userManager = $userManager;
    }
    
    /**
     * Find proposals matching the proposal passed as an argument.
     *
     * Here we search for proposal that have similar properties :
     * - drivers for passenger proposal, passengers for driver proposal
     * - similar dates
     * - similar times (~ passenger time is after driver time)
     * - similar basic geographical zones
     *
     * TODO : We also limit to the drivers that have enough seats left in their car for the passenger's needs.
     *
     * It is a pre-filter, the idea is to limit the next step : the route calculations (that cannot be done directly in the model).
     * The fine time matching will be done during the route calculation process.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal, bool $excludeProposalUser=true)
    {
        // the "master" proposal is simply called the "proposal"
        // the potential matching proposals are called the "candidates"

        // we search the matchings in the proposal entity
        $query = $this->repository->createQueryBuilder('p')
        ->select(['p','u'])
        //->select(['p','u','p.id as proposalId','SUM(ac.seats) as nbSeats'])
        // we need the criteria (for the dates, number of seats...)
        ->join('p.criteria', 'c')
        // we will need the user informations
        ->join('p.user', 'u')
        // we need the directions and the geographical zones
        ->leftJoin('c.directionDriver', 'dd')->leftJoin('dd.zones', 'zd')
        ->leftJoin('c.directionPassenger', 'dp')->leftJoin('dp.zones', 'zp')
        // we need the matchings and asks to check the available seats
        //->leftJoin('p.matchingOffers', 'm')->leftjoin('m.asks', 'a')->leftJoin('a.criteria', 'ac')->addGroupBy('proposalId')
        //we need the communities
        ->leftJoin('p.communities', 'co');

        // do we exclude the user itself ?
        if ($excludeProposalUser) {
            $query->andWhere('p.user != :userProposal')
            ->setParameter('userProposal', $proposal->getUser());
        }

        // COMMUNITIES
        $filterCommunities = "((co.proposalsHidden = 0 OR co.proposalsHidden is null)";
        // this function returns the id of a Community object
        $fCommunities = function (Community $community) {
            return $community->getId();
        };
        // we use the fCommunities function to create an array of ids of the user's private communities
        $privateCommunities = array_map($fCommunities, $this->userManager->getPrivateCommunities($proposal->getUser()));
        if (is_array($privateCommunities) && count($privateCommunities)>0) {
            // we finally implode this array for filtering
            $filterCommunities .= " OR (co.id IN (" . implode(',', $privateCommunities) . "))";
        }
        $filterCommunities .= ")";
        $query->andWhere($filterCommunities);

        // GEOGRAPHICAL ZONES
        // we search the zones where the user is passenger and/or driver
        $zoneDriverWhere = '';
        $zonePassengerWhere = '';
        if ($proposal->getCriteria()->isDriver()) {
            $zonesAsDriver = $proposal->getCriteria()->getDirectionDriver()->getZones();
            $zones = [];
            foreach ($zonesAsDriver as $zone) {
                $zones[] = $zone->getZoneid();
            }
            $zonePassengerWhere = 'zp.thinness = :thinnessPassenger and zp.zoneid IN(' . implode(',', $zones) . ')';
            $query->setParameter('thinnessPassenger', $this->getPrecision($proposal->getCriteria()->getDirectionDriver()));
        }
        if ($proposal->getCriteria()->isPassenger()) {
            $zonesAsPassenger = $proposal->getCriteria()->getDirectionPassenger()->getZones();
            $zones = [];
            foreach ($zonesAsPassenger as $zone) {
                $zones[] = $zone->getZoneid();
            }
            $zoneDriverWhere = 'zd.thinness = :thinnessDriver and zd.zoneid IN(' . implode(',', $zones) . ')';
            $query->setParameter('thinnessDriver', $this->getPrecision($proposal->getCriteria()->getDirectionPassenger()));
        }

        // SEATS AVAILABLE
        // $seatsDriverWhere = '(1=1)';
        // if ($proposal->getCriteria()->isDriver()) {
        //     $seatsDriverWhere = 'a.status = ' . Ask::STATUS_ACCEPTED;
        // }

        // we search if the user can be passenger and/or driver
        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
            // $query->andWhere('((c.driver = 1 and ' . $zoneDriverWhere . ' and ' . $seatsDriverWhere . ') OR (c.passenger = 1 and ' . $zonePassengerWhere . '))');
            $query->andWhere('((c.driver = 1 and ' . $zoneDriverWhere . ') OR (c.passenger = 1 and ' . $zonePassengerWhere . '))');
        } elseif ($proposal->getCriteria()->isDriver()) {
            $query->andWhere('(c.passenger = 1 and ' . $zonePassengerWhere . ')');
        } elseif ($proposal->getCriteria()->isPassenger()) {
            $query->andWhere('(c.driver = 1 and ' . $zoneDriverWhere . ')');
        }
        
        // FREQUENCIES
        $punctualAndWhere = "";
        $regularAndWhere = "";
        switch ($proposal->getCriteria()->getFrequency()) {
            
            case Criteria::FREQUENCY_PUNCTUAL:

                // DATES AND TIME

                // for a punctual proposal, we search for punctual proposals (and regular candidate proposals if parametered)

                // dates :
                // - punctual candidates, we limit the search :
                //   - exactly to fromDate if strictDate is true
                //   - to the days after the fromDate and before toDate if it's defined (if the user wants to travel any day within a certain range)
                //   (@todo limit automatically the search to the x next days if toDate is not defined ?)
                // - regular candidates, we limit the search :
                //   - to the week day of the proposal

                // times :
                // if we use times, we limit the search to the passengers that have their max starting time after the min starting time of the driver :
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
                // if we use times
                if ($proposal->getCriteria()->getFromTime()) {
                    if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                        $punctualAndWhere .= ' and (
                            (c.passenger = 1 and c.maxTime >= :minTime) or 
                            (c.driver = 1 and c.minTime <= :maxTime)
                        )';
                        $setMinTime = true;
                        $setMaxTime = true;
                    } elseif ($proposal->getCriteria()->isDriver()) {
                        $punctualAndWhere .= ' and c.passenger = 1 and c.maxTime >= :minTime';
                        $setMinTime = true;
                    } else {
                        $punctualAndWhere .= ' and c.driver = 1 and c.minTime <= :maxTime';
                        $setMaxTime = true;
                    }
                }
                $punctualAndWhere .= ')';
                
                // 'where' part of regular candidates
                $regularAndWhere = "";
                if (!$proposal->getCriteria()->isStrictPunctual()) {
                    $regularDay = '';
                    $regularTime = '';
                    switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                                    
                        case 0:     // sunday
                                    $regularDay = ' and c.sunCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.sunMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.sunMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.sunMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.sunMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 1:     // monday
                                    $regularDay = ' and c.monCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.monMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.monMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.monMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.monMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 2:     // tuesday
                                    $regularDay = ' and c.tueCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.tueMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.tueMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.tueMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.tueMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 3:     // wednesday
                                    $regularDay = ' and c.wedCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.wedMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.wedMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.wedMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.wedMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 4:     // thursday
                                    $regularDay = ' and c.thuCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.thuMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.thuMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.thuMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.thuMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 5:     //friday
                                    $regularDay = ' and c.friCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.friMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.friMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.friMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.friMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                        case 6:     // saturday
                                    $regularDay = ' and c.satCheck = 1';
                                    if ($proposal->getCriteria()->getFromTime()) {
                                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                                            $regularTime = ' and (
                                                (c.passenger = 1 and c.satMaxTime >= :minTime) or 
                                                (c.driver = 1 and c.satMinTime <= :maxTime)
                                            )';
                                            $setMinTime = true;
                                            $setMaxTime = true;
                                        } elseif ($proposal->getCriteria()->isDriver()) {
                                            $regularTime = ' and (c.passenger = 1 and c.satMaxTime >= :minTime)';
                                            $setMinTime = true;
                                        } else {
                                            $regularTime = ' and (c.driver = 1 and c.satMinTime <= :maxTime)';
                                            $setMaxTime = true;
                                        }
                                    }
                                    break;
                    }
                    $regularAndWhere = '(c.frequency=' . Criteria::FREQUENCY_REGULAR . ' and c.fromDate <= :fromDate and c.toDate >= :fromDate' . $regularDay . $regularTime . ')';
                }

                if ($setMinTime) {
                    $query->setParameter('minTime', $proposal->getCriteria()->getMinTime()->format('H:i'));
                }
                if ($setMaxTime) {
                    $query->setParameter('maxTime', $proposal->getCriteria()->getMaxTime()->format('H:i'));
                }

                break;
        
            case Criteria::FREQUENCY_REGULAR:

                // DATES AND TIME

                // for a regular proposal, we search for regular proposals (and punctual candidate proposals if parametered)

                // dates :
                // - punctual candidates, we limit the search :
                //   - to the candidates that have their day in common with the proposal
                // - regular candidates, we limit the search :
                //   - to the candidates that have at least one day in common with the proposal

                // times :
                // if we use times, we limit the search to the passengers that have their max starting time after the min starting time of the driver :
                //
                //      min             max
                //      >|-------D-------|
                //          |-------P-------|<
                //          min             max

                $setToDate = true;
                $setMonMinTime = $setMonMaxTime = false;    // ---
                $setTueMinTime = $setTueMaxTime = false;    //
                $setWedMinTime = $setWedMaxTime = false;    //
                $setThuMinTime = $setThuMaxTime = false;    // Flags for regular min/max times
                $setFriMinTime = $setFriMaxTime = false;    //
                $setSatMinTime = $setSatMaxTime = false;    //
                $setSunMinTime = $setSunMaxTime = false;    // ---
                $setMinTime = false;  // ---
                $setMaxTime = false;  //
                $setMonTime = false;  //
                $setTueTime = false;  //
                $setWedTime = false;  // Flags for punctual min/max times
                $setThuTime = false;  //
                $setFriTime = false;  //
                $setSatTime = false;  //
                $setSunTime = false;  // ---
                $regularSunDay = $regularMonDay = $regularTueDay = $regularWedDay = $regularThuDay = $regularFriDay = $regularSatDay = "";

                $days = "";         // used to check if a given punctual candidate day is matching
                $minTime = "(";
                $maxTime = "(";
                $regularDay = "";
                $regularTime = "";
                if ($proposal->getCriteria()->isSunCheck()) {
                    $regularSunDay = 'c.sunCheck = 1';
                    $days .= "1,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getSunTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularSunDay .= ' and (
                                (c.passenger = 1 and c.sunMaxTime >= :sunMinTime) or 
                                (c.driver = 1 and c.sunMinTime <= :sunMaxTime)
                            )';
                            $setSunMinTime = true;
                            $setSunMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularSunDay .= ' and (c.passenger = 1 and c.sunMaxTime >= :sunMinTime)';
                            $setSunMinTime = true;
                        } else {
                            $regularSunDay .= ' and (c.driver = 1 and c.sunMinTime <= :sunMaxTime)';
                            $setSunMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 1 and c.maxTime >= :sunMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 1 and c.minTime <= :sunMaxTime) OR '; // used for punctual candidate
                            $setSunTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isMonCheck()) {
                    $regularMonDay = 'c.monCheck = 1';
                    $days .= "2,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getMonTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularMonDay .= ' and (
                                (c.passenger = 1 and c.monMaxTime >= :monMinTime) or 
                                (c.driver = 1 and c.monMinTime <= :monMaxTime)
                            )';
                            $setMonMinTime = true;
                            $setMonMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularMonDay .= ' and (c.passenger = 1 and c.monMaxTime >= :monMinTime)';
                            $setMonMinTime = true;
                        } else {
                            $regularMonDay .= ' and (c.driver = 1 and c.monMinTime <= :monMaxTime)';
                            $setMonMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 2 and c.maxTime >= :monMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 2 and c.minTime <= :monMaxTime) OR '; // used for punctual candidate
                            $setMonTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isTueCheck()) {
                    $regularTueDay = 'c.tueCheck = 1';
                    $days .= "3,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getTueTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularTueDay .= ' and (
                                (c.passenger = 1 and c.tueMaxTime >= :tueMinTime) or 
                                (c.driver = 1 and c.tueMinTime <= :tueMaxTime)
                            )';
                            $setTueMinTime = true;
                            $setTueMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularTueDay .= ' and (c.passenger = 1 and c.tueMaxTime >= :tueMinTime)';
                            $setTueMinTime = true;
                        } else {
                            $regularTueDay .= ' and (c.driver = 1 and c.tueMinTime <= :tueMaxTime)';
                            $setTueMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 3 and c.maxTime >= :tueMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 3 and c.minTime <= :tueMaxTime) OR '; // used for punctual candidate
                            $setTueTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isWedCheck()) {
                    $regularWedDay = 'c.wedCheck = 1';
                    $days .= "4,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getWedTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularWedDay .= ' and (
                                (c.passenger = 1 and c.wedMaxTime >= :wedMinTime) or 
                                (c.driver = 1 and c.wedMinTime <= :wedMaxTime)
                            )';
                            $setWedMinTime = true;
                            $setWedMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularWedDay .= ' and (c.passenger = 1 and c.wedMaxTime >= :wedMinTime)';
                            $setWedMinTime = true;
                        } else {
                            $regularWedDay .= ' and (c.driver = 1 and c.wedMinTime <= :wedMaxTime)';
                            $setWedMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 4 and c.maxTime >= :wedMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 4 and c.minTime <= :wedMaxTime) OR '; // used for punctual candidate
                            $setWedTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isThuCheck()) {
                    $regularThuDay = 'c.thuCheck = 1';
                    $days .= "5,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getThuTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularThuDay .= ' and (
                                (c.passenger = 1 and c.thuMaxTime >= :thuMinTime) or 
                                (c.driver = 1 and c.thuMinTime <= :thuMaxTime)
                            )';
                            $setThuMinTime = true;
                            $setThuMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularThuDay .= ' and (c.passenger = 1 and c.thuMaxTime >= :thuMinTime)';
                            $setThuMinTime = true;
                        } else {
                            $regularThuDay .= ' and (c.driver = 1 and c.thuMinTime <= :thuMaxTime)';
                            $setThuMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 5 and c.maxTime >= :thuMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 5 and c.minTime <= :thuMaxTime) OR '; // used for punctual candidate
                            $setThuTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isFriCheck()) {
                    $regularFriDay = 'c.friCheck = 1';
                    $days .= "6,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getFriTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularFriDay .= ' and (
                                (c.passenger = 1 and c.friMaxTime >= :friMinTime) or 
                                (c.driver = 1 and c.friMinTime <= :friMaxTime)
                            )';
                            $setFriMinTime = true;
                            $setFriMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularFriDay .= ' and (c.passenger = 1 and c.friMaxTime >= :friMinTime)';
                            $setFriMinTime = true;
                        } else {
                            $regularFriDay .= ' and (c.driver = 1 and c.friMinTime <= :friMaxTime)';
                            $setFriMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 6 and c.maxTime >= :friMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 6 and c.minTime <= :friMaxTime) OR '; // used for punctual candidate
                            $setFriTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isSatCheck()) {
                    $regularSatDay = 'c.satCheck = 1';
                    $days .= "7,"; // used for punctual candidate
                    if ($proposal->getCriteria()->getSatTime()) {
                        if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                            $regularSatDay .= ' and (
                                (c.passenger = 1 and c.satMaxTime >= :satMinTime) or 
                                (c.driver = 1 and c.satMinTime <= :satMaxTime)
                            )';
                            $setSatMinTime = true;
                            $setSatMaxTime = true;
                        } elseif ($proposal->getCriteria()->isDriver()) {
                            $regularSatDay .= ' and (c.passenger = 1 and c.satMaxTime >= :satMinTime)';
                            $setSatMinTime = true;
                        } else {
                            $regularSatDay .= ' and (c.driver = 1 and c.satMinTime <= :satMaxTime)';
                            $setSatMaxTime = true;
                        }
                        if (!$proposal->getCriteria()->isStrictRegular()) {
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 7 and c.maxTime >= :satMinTime) OR '; //
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 7 and c.minTime <= :satMaxTime) OR '; // used for punctual candidate
                            $setSatTime = true;
                        }
                    }
                }

                // delete the last comma
                $days = substr($days, 0, -1);

                // delete the last OR
                if ($minTime != "(") {
                    $minTime = substr($minTime, 0, -4) . ')';
                } else {
                    $minTime = "";
                }
                if ($maxTime != "(") {
                    $maxTime = substr($maxTime, 0, -4) . ')';
                } else {
                    $maxTime = "";
                }

                // 'where' part of punctual candidates
                if (!$proposal->getCriteria()->isStrictRegular()) {
                    // if the proposal is not strictly regualr, we include the punctual proposals
                    $punctualAndWhere = '(';
                    $punctualAndWhere .= 'c.frequency=' . Criteria::FREQUENCY_PUNCTUAL . ' and c.fromDate >= :fromDate and c.fromDate <= :toDate and DAYOFWEEK(c.fromDate) IN (' . $days . ')';
                    if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                        $punctualAndWhere .= ' and (
                            (c.passenger = 1' . ($minTime != "" ? ' and ' . $minTime : '') . ') or 
                            (c.driver = 1' . ($maxTime != "" ? ' and ' . $maxTime : '') . ')
                        )';
                        $setMinTime = true;
                        $setMaxTime = true;
                    } elseif ($proposal->getCriteria()->isDriver()) {
                        $punctualAndWhere .= ' and c.passenger = 1' . ($minTime != "" ? ' and ' . $minTime : '');
                        $setMinTime = true;
                    } else {
                        $punctualAndWhere .= ' and c.driver = 1' . ($maxTime != "" ? ' and ' . $maxTime : '');
                        $setMaxTime = true;
                    }
                    $punctualAndWhere .= ')';
                }

                // 'where' part of regular candidates
                $regularAndWhere = '(c.frequency=' . Criteria::FREQUENCY_REGULAR . ' and (';

                // we have to check that date ranges match (P = proposal; C = candidate)
                //              min             max
                //               |-------P-------|
                // (1)        |----------C----------|          OK
                // (2)               |---C---|                 OK
                // (3)      |-----C-----|                      OK
                // (4)                       |-----C-----|     OK
                // (5)  |--C--|                                NOT OK
                // (6)                              |---C---|  NOT OK
                
                $regularAndWhere .= '(c.fromDate <= :fromDate and c.fromDate <= :toDate and c.toDate >= :toDate and c.toDate >= :fromDate) OR ';  // (1)
                $regularAndWhere .= '(c.fromDate >= :fromDate and c.fromDate <= :toDate and c.toDate <= :toDate and c.toDate >= :fromDate) OR ';  // (2)
                $regularAndWhere .= '(c.fromDate <= :fromDate and c.fromDate <= :toDate and c.toDate <= :toDate and c.toDate >= :fromDate) OR ';  // (3)
                $regularAndWhere .= '(c.fromDate >= :fromDate and c.fromDate <= :toDate and c.toDate >= :toDate and c.toDate >= :fromDate)';      // (4)
                $regularAndWhere .= ") and (" .
                (($regularSunDay<>"") ? '(' . $regularSunDay . ') OR ' : '') .
                (($regularMonDay<>"") ? '(' . $regularMonDay . ') OR ' : '') .
                (($regularTueDay<>"") ? '(' . $regularTueDay . ') OR ' : '') .
                (($regularWedDay<>"") ? '(' . $regularWedDay . ') OR ' : '') .
                (($regularThuDay<>"") ? '(' . $regularThuDay . ') OR ' : '') .
                (($regularFriDay<>"") ? '(' . $regularFriDay . ') OR ' : '') .
                (($regularSatDay<>"") ? '(' . $regularSatDay . ') OR ' : '');
                // delete the last OR
                $regularAndWhere = substr($regularAndWhere, 0, -4) . '))';

                if ($setMonMinTime || ($setMinTime && $setMonTime)) {
                    $query->setParameter('monMinTime', $proposal->getCriteria()->getMonMinTime()->format('H:i'));
                }
                if ($setMonMaxTime || ($setMaxTime && $setMonTime)) {
                    $query->setParameter('monMaxTime', $proposal->getCriteria()->getMonMaxTime()->format('H:i'));
                }
                if ($setTueMinTime || ($setMinTime && $setTueTime)) {
                    $query->setParameter('tueMinTime', $proposal->getCriteria()->getTueMinTime()->format('H:i'));
                }
                if ($setTueMaxTime || ($setMaxTime && $setTueTime)) {
                    $query->setParameter('tueMaxTime', $proposal->getCriteria()->getTueMaxTime()->format('H:i'));
                }
                if ($setWedMinTime || ($setMinTime && $setWedTime)) {
                    $query->setParameter('wedMinTime', $proposal->getCriteria()->getWedMinTime()->format('H:i'));
                }
                if ($setWedMaxTime || ($setMaxTime && $setWedTime)) {
                    $query->setParameter('wedMaxTime', $proposal->getCriteria()->getWedMaxTime()->format('H:i'));
                }
                if ($setThuMinTime || ($setMinTime && $setThuTime)) {
                    $query->setParameter('thuMinTime', $proposal->getCriteria()->getThuMinTime()->format('H:i'));
                }
                if ($setThuMaxTime || ($setMaxTime && $setThuTime)) {
                    $query->setParameter('thuMaxTime', $proposal->getCriteria()->getThuMaxTime()->format('H:i'));
                }
                if ($setFriMinTime || ($setMinTime && $setFriTime)) {
                    $query->setParameter('friMinTime', $proposal->getCriteria()->getFriMinTime()->format('H:i'));
                }
                if ($setFriMaxTime || ($setMaxTime && $setFriTime)) {
                    $query->setParameter('friMaxTime', $proposal->getCriteria()->getFriMaxTime()->format('H:i'));
                }
                if ($setSatMinTime || ($setMinTime && $setSatTime)) {
                    $query->setParameter('satMinTime', $proposal->getCriteria()->getSatMinTime()->format('H:i'));
                }
                if ($setSatMaxTime || ($setMaxTime && $setSatTime)) {
                    $query->setParameter('satMaxTime', $proposal->getCriteria()->getSatMaxTime()->format('H:i'));
                }
                if ($setSunMinTime || ($setMinTime && $setSunTime)) {
                    $query->setParameter('sunMinTime', $proposal->getCriteria()->getSunMinTime()->format('H:i'));
                }
                if ($setSunMaxTime || ($setMaxTime && $setSunTime)) {
                    $query->setParameter('sunMaxTime', $proposal->getCriteria()->getSunMaxTime()->format('H:i'));
                }
                break;
        
        }

        if ($punctualAndWhere != "" && $regularAndWhere != '') {
            $query->andWhere('(' . $punctualAndWhere . ' or ' .$regularAndWhere . ')');
        } elseif ($punctualAndWhere != "") {
            $query->andWhere($punctualAndWhere);
        } elseif ($regularAndWhere != "") {
            $query->andWhere($regularAndWhere);
        }
        $query->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));

        if ($setToDate) {
            $query->setParameter('toDate', $proposal->getCriteria()->getToDate()->format('Y-m-d'));
        }

        // var_dump($punctualAndWhere);
        // var_dump($regularAndWhere);
        // var_dump($query->getQuery()->getSql());
        // foreach ($query->getQuery()->getParameters() as $parameter) {
        //     echo $parameter->getName() . " ";
        // }
        // exit;
        // var_dump(count($query->getQuery()->getParameters()));exit;

        // we launch the request and return the result
        return $query->getQuery()->getResult();
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
        $thinnesses = ZoneManager::THINNESSES;
        sort($thinnesses);
        $i = 0;
        $found = false;
        while (!$found && $i < count($thinnesses)) {
            if (($direction->getDistance()/4)<($thinnesses[$i]*self::METERS_BY_DEGREE)) {
                $found = true;
            } else {
                $i++;
            }
        }
        if ($found) {
            return $thinnesses[$i];
        }
        return array_pop($thinnesses);
    }
}
