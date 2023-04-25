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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Ressource\Ad;
use App\Community\Entity\Community;
use App\Geography\Service\GeoTools;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method null|Proposal find($id, $lockMode = null, $lockVersion = null)
 * @method null|Proposal findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository
{
    private $bearing_range;                   // if used, only accept proposal where the bearing direction (cape) is not at the opposite, more or less the range degrees
    // for example, if the bearing is 0 (s->n), the proposals where the bearing is between 170 and 190 (~ n->s) are excluded
    private $passenger_proportion;           // minimum passenger distance relative to the driver distance, eg passenger distance should be at least 30% of the driver distance
    private $max_distance_punctual;          // percentage of the driver direction to compute the max distance between driver and passenger directions (punctual)
    private $max_distance_regular;           // percentage of the driver direction to compute the max distance between driver and passenger directions (regular)
    private $distance_ratio;              // ratio to use when computing distance filter (used to convert geographic degrees to metres)

    private $use_bearing;                   // use the ~bearing check~ filtering
    private $use_bbox;                      // use the ~bbox check~ filtering (check if the (extended) bounding box of the proposals intersect)
    private $use_passenger_proportion;      // use the ~passenger distance proportion~
    private $use_distance;                  // use the ~distance between the driver and the passenger~ filtering

    private $repository;
    private $userManager;
    private $geoTools;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, GeoTools $geoTools, array $params)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Proposal::class);
        $this->userManager = $userManager;
        $this->geoTools = $geoTools;
        $this->bearing_range = $params['bearingRange'];
        $this->passenger_proportion = $params['passengerProportion'];
        $this->max_distance_punctual = $params['maxDistancePunctual'];
        $this->max_distance_regular = $params['maxDistanceRegular'];
        $this->distance_ratio = $params['distanceRatio'];
        $this->use_bearing = $params['useBearing'];
        $this->use_bbox = $params['useBbox'];
        $this->use_passenger_proportion = $params['usePassengerProportion'];
        $this->use_distance = $params['useDistance'];
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
     * We also filter the dynamic ads :
     * - a dynamic driver ad can only match with a dynamic passenger ad (for a dynamic driver, the matching is made only to check the validity, they are not presented to the driver)
     * - a dynamic passenger ad can match with any driver ad
     *
     * We can also filter with communities.
     * TODO : limit to the drivers that have enough seats left in their car for the passenger's needs.
     *
     * We also filter solidary proposals :
     * - a solidary exclusive proposal can only match with a solidary proposal
     * - a solidary proposal can match with any proposal
     *
     * On the other hand, filters for bearing, passenger proportion and distance to passenger's waypoints are disabled for a solidary proposal without destination.
     *
     * It is a pre-filter, the idea is to limit the next step : the route calculations (that cannot be done directly in the model).
     * The fine time matching will be done during the route calculation process.
     *
     * TODO : find the matching also in existing matchings !
     * => an accepted carpool ask can be considered as a new proposal, with a new direction consisting in driver and passenger waypoints
     * => the original proposals shouldn't be used as proposals (excluded for new searches and ad posts, recomputed for existing matchings)
     *      we should use the original and the resulting proposal and use the best match (the shortest in time)
     * => the driver seats should be reduced by the number of passengers in the new matchingProposal->criteria object
     *
     * @param Proposal $proposal            The proposal to match
     * @param bool     $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @param bool     $driversOnly         Exclude the matching proposals as passenger (used for import)
     *
     * @return null|array|\Doctrine\DBAL\Driver\Statement|mixed
     */
    public function findMatchingProposals(Proposal $proposal, bool $excludeProposalUser = true, bool $driversOnly = false)
    {
        // the "master" proposal is simply called the "proposal"
        // the potential matching proposals are called the "candidates"

        // we search the matchings in the proposal entity
        $query = $this->repository->createQueryBuilder('p');

        $selection = [
            'p.id as pid',
            'u.id as uid',
            'c.driver',
            'c.passenger',
            'c.maxDetourDuration',
            'c.maxDetourDistance',
            'dp.duration as dpduration',
            'dp.distance as dpdistance',
            'w.id as wid',
            'w.position',
            'w.destination',
            'w.reached',
            'a.name as addressName',
            'a.longitude',
            'a.latitude',
            'a.streetAddress',
            'a.postalCode',
            'a.addressLocality',
            'a.addressCountry',
            'a.elevation',
            'a.houseNumber',
            'a.street',
            'a.subLocality',
            'a.localAdmin',
            'a.county',
            'a.macroCounty',
            'a.region',
            'a.macroRegion',
            'a.countryCode',
        ];
        if (!$driversOnly) {
            $selection[] = 'dd.duration as ddduration';
            $selection[] = 'dd.distance as dddistance';
        }

        $query->select($selection);

        // ->select(['p','u','p.id as proposalId','SUM(ac.seats) as nbSeats'])
        // we need the criteria (for the dates, number of seats...)
        $query->join('p.criteria', 'c')
            // we will need the user informations
            ->join('p.user', 'u')
            // we need the directions and the geographical zones
            ->leftJoin('c.directionPassenger', 'dp')
            ->leftJoin('p.waypoints', 'w')
            ->leftJoin('w.address', 'a')
            // we need the matchings and asks to check the available seats
            // ->leftJoin('p.matchingOffers', 'm')->leftjoin('m.asks', 'a')->leftJoin('a.criteria', 'ac')->addGroupBy('proposalId')
            // we need the communities
            ->leftJoin('p.communities', 'co')
        ;

        if (!$driversOnly) {
            $query->leftJoin('c.directionDriver', 'dd');
        }

        // do we exclude the user itself if the proposal isn't anonymous ?
        if ($excludeProposalUser && $proposal->getUser()) {
            $query->andWhere('p.user != :userProposal')
                ->setParameter('userProposal', $proposal->getUser())
            ;
        }

        // exclude private proposals
        $query->andWhere('p.private IS NULL or p.private = 0');

        // // exclude paused proposals
        // $query->andWhere('(p.paused IS NULL or p.paused = 0)');

        // DYNAMIC ADS
        if ($proposal->getCriteria()->isDriver() && $proposal->isDynamic() && $proposal->isActive() && !$proposal->isFinished()) {
            // for dynamic drivers, validity check of dynamic passengers
            $query->andWhere('p.dynamic = 1 AND p.active=1 AND p.finished=0');
        } elseif ($proposal->getCriteria()->isPassenger() && $proposal->isDynamic() && $proposal->isActive() && !$proposal->isFinished()) {
            // for dynamic passengers, match with any driver (not only dynamic) or valid dynamic drivers
            $query->andWhere('(p.dynamic = 0) OR (p.dynamic = 1 AND p.active=1 AND p.finished=0)');
        }

        // SOLIDARY
        if ($proposal->getCriteria()->isSolidaryExclusive()) {
            // solidary exclusive proposal => can match only with solidary proposals
            $query->andWhere('c.solidary = 1');
        } elseif (!$proposal->getCriteria()->isSolidary()) {
            // not a solidary proposal => solidary exclusive are excluded
            $query->andWhere('c.solidaryExclusive IS NULL or c.solidaryExclusive = 0');
        }

        // COMMUNITIES
        // here we exclude the proposals that are posted in communities for which the user is not member
        $filterUserCommunities = '(co.proposalsHidden = 0 OR co.proposalsHidden is null)';
        // this function returns the id of a Community object
        $fCommunities = function (Community $community) {
            return $community->getId();
        };
        // we use the fCommunities function to create an array of ids of the user's private communities
        $privateCommunities = array_map($fCommunities, $this->userManager->getPrivateCommunities($proposal->getUser()));
        if (is_array($privateCommunities) && count($privateCommunities) > 0) {
            // we finally implode this array for filtering
            $filterUserCommunities .= ' OR (co.id IN ('.implode(',', $privateCommunities).'))';
        }
        $query->andWhere($filterUserCommunities);

        // here we filter to the given proposal communities
        if ($proposal->getCommunities()) {
            $communities = array_map($fCommunities, $proposal->getCommunities());
            if (is_array($communities) && count($communities) > 0) {
                // we finally implode this array for filtering
                $query->andWhere('co.id IN ('.implode(',', $communities).')');
            }
        }

        // GEOGRAPHICAL ZONES
        // we search the zones where the user is passenger and/or driver
        $zoneDriverWhere = '';
        $zonePassengerWhere = '';
        if ($proposal->getCriteria()->isDriver()) {
            // sometimes when updating an Ad (major update, deletion - creation), linked proposal doesnt seem to have DirectionDriver setup while the owner is Driver
            // and the linkedCriteria isDriver seems to be set to true
            if (
                is_null($proposal->getCriteria()->getDirectionDriver())
                && !is_null($proposal->getProposalLinked())
                && !is_null($proposal->getProposalLinked()->getCriteria()->getDirectionDriver())
            ) {
                $proposal->getCriteria()->setDirectionDriver($proposal->getProposalLinked()->getCriteria()->getDirectionDriver());
            }
            $zonePassengerWhere = '';

            // bearing => we exclude the proposals if their direction is outside the authorize range (opposite bearing +/- BEARING_RANGE degrees)
            // restriction not applied for solidary proposals without destination
            if ($this->use_bearing) {
                if ('' != $zonePassengerWhere) {
                    $zonePassengerWhere .= ' and ';
                }
                $range = $this->geoTools->getOppositeBearing($proposal->getCriteria()->getDirectionDriver()->getBearing(), $this->bearing_range);
                if ($range['min'] <= $range['max']) {
                    // usual case, eg. 140 to 160
                    $zonePassengerWhere .= '(p.noDestination = 1 or dp.bearing <= :minDriverRange or dp.bearing >= :maxDriverRange)';
                    $query->setParameter('minDriverRange', $range['min']);
                    $query->setParameter('maxDriverRange', $range['max']);
                } elseif ($range['min'] > $range['max']) {
                    // the range is like between 350 and 10
                    $zonePassengerWhere .= '(p.noDestination = 1 or dp.bearing >= :maxDriverRange and dp.bearing <= :minDriverRange)';
                    $query->setParameter('minDriverRange', $range['min']);
                    $query->setParameter('maxDriverRange', $range['max']);
                }
            }

            // bounding box
            if ($this->use_bbox) {
                if ('' != $zonePassengerWhere) {
                    $zonePassengerWhere .= ' and ';
                }
                $zonePassengerWhere .= '(ST_INTERSECTS(dp.geoJsonBbox,ST_GeomFromText(\''.
                    $this->getGeoPolygon(
                        $this->geoTools->moveGeoLon(
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMinLon(),
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMinLat(),
                            -$this->getBBoxExtension($proposal->getCriteria()->getDirectionDriver()->getDistance())
                        ),
                        $this->geoTools->moveGeoLat(
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMinLat(),
                            -$this->getBBoxExtension($proposal->getCriteria()->getDirectionDriver()->getDistance())
                        ),
                        $this->geoTools->moveGeoLon(
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMaxLon(),
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMaxLat(),
                            $this->getBBoxExtension($proposal->getCriteria()->getDirectionDriver()->getDistance())
                        ),
                        $this->geoTools->moveGeoLat(
                            $proposal->getCriteria()->getDirectionDriver()->getBboxMaxLat(),
                            $this->getBBoxExtension($proposal->getCriteria()->getDirectionDriver()->getDistance())
                        )
                    ).'\'))=1)';
            }

            // passenger proportion
            // restriction not applied for solidary proposals without destination
            if ($this->use_passenger_proportion) {
                if ('' != $zonePassengerWhere) {
                    $zonePassengerWhere .= ' and ';
                }
                $zonePassengerWhere .= '(p.noDestination = 1 or dp.distance >= '.$proposal->getCriteria()->getDirectionDriver()->getDistance() * $this->passenger_proportion.')';
            }

            // distance to passenger
            if ($this->use_distance) {
                if ('' != $zonePassengerWhere) {
                    $zonePassengerWhere .= ' and ';
                }
                $maxDistance = Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency() ?
                    ($proposal->getCriteria()->getDirectionDriver()->getDistance() * $this->max_distance_punctual / $this->distance_ratio) : ($proposal->getCriteria()->getDirectionDriver()->getDistance() * $this->max_distance_regular / $this->distance_ratio);
                $query
                    ->join('p.waypoints', 'w2')
                    ->join('p.waypoints', 'w3')
                    ->join('w2.address', 'a2')
                    ->join('w3.address', 'a3')
                    ->join('\App\Geography\Entity\Direction', 'dirDri')
                    ->andWhere('w2.position = 0 and w3.destination = 1')
                    ->andWhere('dirDri.id = :dirDriId')
                ;
                $zonePassengerWhere .= 'ST_Distance(dirDri.geoJsonSimplified,a2.geoJson)<='.$maxDistance.' and ST_Distance(dirDri.geoJsonSimplified,a3.geoJson)<='.$maxDistance;
                $query->setParameter('dirDriId', $proposal->getCriteria()->getDirectionDriver()->getId());
            }
        }

        if (!$driversOnly && $proposal->getCriteria()->isPassenger()) {
            // bearing => we exclude the proposals if their direction is outside the authorize range (opposite bearing +/- BEARING_RANGE degrees)
            // restriction not applied for solidary proposals without destination
            if ($this->use_bearing && $proposal->getCriteria()->getDirectionPassenger()->getDistance() > 0) {
                if ('' != $zoneDriverWhere) {
                    $zoneDriverWhere .= ' and ';
                }
                $range = $this->geoTools->getOppositeBearing($proposal->getCriteria()->getDirectionPassenger()->getBearing(), $this->bearing_range);
                if ($range['min'] <= $range['max']) {
                    // usual case, eg. 140 to 160
                    $zoneDriverWhere .= '(dd.bearing <= :minPassengerRange or dd.bearing >= :maxPassengerRange)';
                    $query->setParameter('minPassengerRange', $range['min']);
                    $query->setParameter('maxPassengerRange', $range['max']);
                } elseif ($range['min'] > $range['max']) {
                    // the range is like between 350 and 10
                    $zoneDriverWhere .= '(dd.bearing >= :maxPassengerRange and dd.bearing <= :minPassengerRange)';
                    $query->setParameter('minPassengerRange', $range['min']);
                    $query->setParameter('maxPassengerRange', $range['max']);
                }
            }

            // bounding box
            if ($this->use_bbox) {
                if ('' != $zoneDriverWhere) {
                    $zoneDriverWhere .= ' and ';
                }
                $zoneDriverWhere .= '(ST_INTERSECTS(dd.geoJsonBbox,ST_GeomFromText(\''.
                    $this->getGeoPolygon(
                        $this->geoTools->moveGeoLon(
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMinLon(),
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMinLat(),
                            -$this->getBBoxExtension($proposal->getCriteria()->getDirectionPassenger()->getDistance())
                        ),
                        $this->geoTools->moveGeoLat(
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMinLat(),
                            -$this->getBBoxExtension($proposal->getCriteria()->getDirectionPassenger()->getDistance())
                        ),
                        $this->geoTools->moveGeoLon(
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMaxLon(),
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMaxLat(),
                            $this->getBBoxExtension($proposal->getCriteria()->getDirectionPassenger()->getDistance())
                        ),
                        $this->geoTools->moveGeoLat(
                            $proposal->getCriteria()->getDirectionPassenger()->getBboxMaxLat(),
                            $this->getBBoxExtension($proposal->getCriteria()->getDirectionPassenger()->getDistance())
                        )
                    ).'\'))=1)';
            }

            // passenger proportion
            if ($this->use_passenger_proportion && $proposal->getCriteria()->getDirectionPassenger()->getDistance() > 0) {
                if ('' != $zoneDriverWhere) {
                    $zoneDriverWhere .= ' and ';
                }
                $zoneDriverWhere .= '(dd.distance >= '.$proposal->getCriteria()->getDirectionPassenger()->getDistance() * (1 - $this->passenger_proportion).')';
            }

            // distance to passenger
            if ($this->use_distance) {
                $origin = 0;
                $destination = 0;
                foreach ($proposal->getWaypoints() as $waypoint) {
                    if (0 == $waypoint->getPosition()) {
                        $origin = "Point('".$waypoint->getAddress()->getLongitude()."','".$waypoint->getAddress()->getLatitude()."')";
                    } elseif (1 == $waypoint->isDestination()) {
                        $destination = "Point('".$waypoint->getAddress()->getLongitude()."','".$waypoint->getAddress()->getLatitude()."')";
                    }
                }
                if ('' != $zoneDriverWhere) {
                    $zoneDriverWhere .= ' and ';
                }
                // $query
                // ->join('p.waypoints', 'w4')
                // ->join('p.waypoints', 'w5')
                // ->join('w4.address','a4')
                // ->join('w5.address','a5')
                // ->andWhere('w4.position = 0 and w5.destination = 1');
                $zoneDriverWhere .= '((c.frequency='.Criteria::FREQUENCY_PUNCTUAL.' and ST_Distance(dd.geoJsonSimplified,'.$origin.')<=(dd.distance*'.$this->max_distance_punctual.'/'.$this->distance_ratio.')) OR ';
                $zoneDriverWhere .= '(c.frequency='.Criteria::FREQUENCY_REGULAR.' and ST_Distance(dd.geoJsonSimplified,'.$origin.')<=(dd.distance*'.$this->max_distance_regular.'/'.$this->distance_ratio.'))';
                $zoneDriverWhere .= ') and ';
                $zoneDriverWhere .= '((c.frequency='.Criteria::FREQUENCY_PUNCTUAL.' and ST_Distance(dd.geoJsonSimplified,'.$destination.')<=(dd.distance*'.$this->max_distance_punctual.'/'.$this->distance_ratio.')) OR ';
                $zoneDriverWhere .= '(c.frequency='.Criteria::FREQUENCY_REGULAR.' and ST_Distance(dd.geoJsonSimplified,'.$destination.')<=(dd.distance*'.$this->max_distance_regular.'/'.$this->distance_ratio.'))';
                $zoneDriverWhere .= ')';
            }
        }

        // SEATS AVAILABLE
        // $seatsDriverWhere = '(1=1)';
        // if ($proposal->getCriteria()->isDriver()) {
        //     $seatsDriverWhere = 'a.status = ' . Ask::STATUS_ACCEPTED;
        // }

        // we search if the user can be passenger and/or driver
        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
            // $query->andWhere('((c.driver = 1 and ' . $zoneDriverWhere . ' and ' . $seatsDriverWhere . ') OR (c.passenger = 1 and ' . $zonePassengerWhere . '))');
            $query->andWhere('(c.driver = 1 and '.$zoneDriverWhere.') OR (c.passenger = 1 and '.$zonePassengerWhere.')');
        } elseif ($proposal->getCriteria()->isDriver()) {
            $query->andWhere('c.passenger = 1 and '.$zonePassengerWhere);
        } elseif ($proposal->getCriteria()->isPassenger()) {
            $query->andWhere('c.driver = 1 and '.$zoneDriverWhere);
        }

        // FREQUENCIES
        $punctualAndWhere = '';
        $regularAndWhere = '';

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
                //   - exactly to fromDate if strictDate is true (we verify that the corresponding day is carpooled)
                //   - to the carpooled days after the fromDate

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
                    $punctualAndWhere .= 'c.frequency='.Criteria::FREQUENCY_PUNCTUAL.' and c.fromDate = :fromDate';
                } else {
                    $punctualAndWhere .= 'c.frequency='.Criteria::FREQUENCY_PUNCTUAL.' and c.fromDate >= :fromDate';
                    if (!is_null($proposal->getCriteria()->getToDate())) {
                        $punctualAndWhere .= ' and c.fromDate <= :toDate';
                        $setToDate = true;
                    }
                }
                // if we use times
                if ($proposal->getCriteria()->getFromTime() && $proposal->getUseTime()) {
                    if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                $regularAndWhere = '';
                if (!$proposal->getCriteria()->isStrictPunctual()) {
                    $proposalFromDate = \DateTime::createFromFormat('U', $proposal->getCriteria()->getFromDate()->format('U'));
                    $regularAndWhere = ' (';

                    // we search for regular proposal that have :
                    // - their fromDate strictly after the proposal fromDate if the proposal fromDate is non strict (the regular proposal is in the future and it's fine !)
                    // OR
                    // - their fromDate strictly before the proposal fromDate AND
                    //   their toDate after or equal the proposal fromDate AND
                    //   - the day of the proposal fromDate is one carpooled for the regular proposal AND the proposal fromDate is strict
                    //   OR
                    //   - the day or one of the 7 next days of the proposal fromDate is one carpooled for the regular proposal AND the proposal fromDate is non strict
                    if (!$proposal->getCriteria()->isStrictDate()) {
                        $regularAndWhere .= "c.fromDate > '".$proposalFromDate->format('Y-m-d')."' or (";
                    }
                    $regularAndWhere .= "c.fromDate <= '".$proposalFromDate->format('Y-m-d')."' and ";

                    // we may loop for a whole week to find a corresponding carpool day
                    // if we search for a strict date, we will loop only once : this particular day
                    $nbLoop = ($proposal->getCriteria()->isStrictDate()) ? 0 : 7;
                    for ($offset = 0; $offset <= $nbLoop; ++$offset) {
                        list($where, $minTime, $maxTime) = $this->buildRegularAndWhere(
                            $proposalFromDate,
                            $offset,
                            true === $proposal->getCriteria()->isDriver() ? true : false,
                            true === $proposal->getCriteria()->isPassenger() ? true : false,
                            $driversOnly,
                            $proposal->getCriteria()->getFromTime() && $proposal->getUseTime()
                        );
                        $regularAndWhere .= $where;
                        if ($minTime) {
                            $setMinTime = true;
                        }
                        if ($maxTime) {
                            $setMaxTime = true;
                        }
                    }
                    if (!$proposal->getCriteria()->isStrictDate()) {
                        $regularAndWhere .= ')';
                    }
                    $regularAndWhere .= ')';
                }

                // var_dump($regularAndWhere);die;
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
                $setTueMinTime = $setTueMaxTime = false;
                $setWedMinTime = $setWedMaxTime = false;
                $setThuMinTime = $setThuMaxTime = false;    // Flags for regular min/max times
                $setFriMinTime = $setFriMaxTime = false;
                $setSatMinTime = $setSatMaxTime = false;
                $setSunMinTime = $setSunMaxTime = false;    // ---
                $setMinTime = false;  // ---
                $setMaxTime = false;
                $setMonTime = false;
                $setTueTime = false;
                $setWedTime = false;  // Flags for punctual min/max times
                $setThuTime = false;
                $setFriTime = false;
                $setSatTime = false;
                $setSunTime = false;  // ---
                $regularSunDay = $regularMonDay = $regularTueDay = $regularWedDay = $regularThuDay = $regularFriDay = $regularSatDay = '';

                $days = '';         // used to check if a given punctual candidate day is matching
                $minTime = '(';
                $maxTime = '(';
                $regularDay = '';
                $regularTime = '';
                if ($proposal->getCriteria()->isSunCheck()) {
                    $regularSunDay = 'c.sunCheck = 1';
                    $days .= '1,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getSunTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 1 and c.maxTime >= :sunMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 1 and c.minTime <= :sunMaxTime) OR '; // used for punctual candidate
                            $setSunTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isMonCheck()) {
                    $regularMonDay = 'c.monCheck = 1';
                    $days .= '2,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getMonTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 2 and c.maxTime >= :monMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 2 and c.minTime <= :monMaxTime) OR '; // used for punctual candidate
                            $setMonTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isTueCheck()) {
                    $regularTueDay = 'c.tueCheck = 1';
                    $days .= '3,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getTueTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 3 and c.maxTime >= :tueMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 3 and c.minTime <= :tueMaxTime) OR '; // used for punctual candidate
                            $setTueTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isWedCheck()) {
                    $regularWedDay = 'c.wedCheck = 1';
                    $days .= '4,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getWedTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 4 and c.maxTime >= :wedMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 4 and c.minTime <= :wedMaxTime) OR '; // used for punctual candidate
                            $setWedTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isThuCheck()) {
                    $regularThuDay = 'c.thuCheck = 1';
                    $days .= '5,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getThuTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 5 and c.maxTime >= :thuMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 5 and c.minTime <= :thuMaxTime) OR '; // used for punctual candidate
                            $setThuTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isFriCheck()) {
                    $regularFriDay = 'c.friCheck = 1';
                    $days .= '6,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getFriTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 6 and c.maxTime >= :friMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 6 and c.minTime <= :friMaxTime) OR '; // used for punctual candidate
                            $setFriTime = true;
                        }
                    }
                }
                if ($proposal->getCriteria()->isSatCheck()) {
                    $regularSatDay = 'c.satCheck = 1';
                    $days .= '7,'; // used for punctual candidate
                    if ($proposal->getCriteria()->getSatTime()) {
                        if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
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
                            $minTime .= '(DAYOFWEEK(c.fromDate) = 7 and c.maxTime >= :satMinTime) OR ';
                            $maxTime .= '(DAYOFWEEK(c.fromDate) = 7 and c.minTime <= :satMaxTime) OR '; // used for punctual candidate
                            $setSatTime = true;
                        }
                    }
                }

                // delete the last comma
                $days = substr($days, 0, -1);

                // delete the last OR
                if ('(' != $minTime) {
                    $minTime = substr($minTime, 0, -4).')';
                } else {
                    $minTime = '';
                }
                if ('(' != $maxTime) {
                    $maxTime = substr($maxTime, 0, -4).')';
                } else {
                    $maxTime = '';
                }

                // 'where' part of punctual candidates
                if (!$proposal->getCriteria()->isStrictRegular()) {
                    // if the proposal is not strictly regualr, we include the punctual proposals
                    $punctualAndWhere = '(';
                    $punctualAndWhere .= 'c.frequency='.Criteria::FREQUENCY_PUNCTUAL.' and c.fromDate >= :fromDate and c.fromDate <= :toDate and DAYOFWEEK(c.fromDate) IN ('.$days.')';
                    if (!$driversOnly && $proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                        $punctualAndWhere .= ' and (
                            (c.passenger = 1'.('' != $minTime ? ' and '.$minTime : '').') or
                            (c.driver = 1'.('' != $maxTime ? ' and '.$maxTime : '').')
                        )';
                        $setMinTime = true;
                        $setMaxTime = true;
                    } elseif ($proposal->getCriteria()->isDriver()) {
                        $punctualAndWhere .= ' and c.passenger = 1'.('' != $minTime ? ' and '.$minTime : '');
                        $setMinTime = true;
                    } else {
                        $punctualAndWhere .= ' and c.driver = 1'.('' != $maxTime ? ' and '.$maxTime : '');
                        $setMaxTime = true;
                    }
                    $punctualAndWhere .= ')';
                }

                // 'where' part of regular candidates
                $regularAndWhere = '(c.frequency='.Criteria::FREQUENCY_REGULAR.' and (';

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
                $regularAndWhere .= ') and ('.
                    (('' != $regularSunDay) ? '('.$regularSunDay.') OR ' : '').
                    (('' != $regularMonDay) ? '('.$regularMonDay.') OR ' : '').
                    (('' != $regularTueDay) ? '('.$regularTueDay.') OR ' : '').
                    (('' != $regularWedDay) ? '('.$regularWedDay.') OR ' : '').
                    (('' != $regularThuDay) ? '('.$regularThuDay.') OR ' : '').
                    (('' != $regularFriDay) ? '('.$regularFriDay.') OR ' : '').
                    (('' != $regularSatDay) ? '('.$regularSatDay.') OR ' : '');
                // delete the last OR
                $regularAndWhere = substr($regularAndWhere, 0, -4).'))';

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

        if ('' != $punctualAndWhere && '' != $regularAndWhere) {
            $query->andWhere('('.$punctualAndWhere.' or '.$regularAndWhere.')');
        } elseif ('' != $punctualAndWhere) {
            $query->andWhere($punctualAndWhere);
        } elseif ('' != $regularAndWhere) {
            $query->andWhere($regularAndWhere);
        }
        $query->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));

        if ($setToDate) {
            $query->setParameter('toDate', $proposal->getCriteria()->getToDate()->format('Y-m-d'));
        }
        // echo '(' . $punctualAndWhere . ' or ' .$regularAndWhere . ')';
        // var_dump($query->getQuery()->getSql());
        // foreach ($query->getQuery()->getParameters() as $parameter) {
        //     echo $parameter->getName() . " " . ($parameter->getValue() instanceof User ? $parameter->getValue()->getId() : $parameter->getValue());
        // }
        // exit;
        // var_dump(count($query->getQuery()->getParameters()));exit;

        // We filter pseudonymized users
        $query
            ->andWhere('u.status != :pseudonymizedStatus')
            ->setParameter('pseudonymizedStatus', User::STATUS_PSEUDONYMIZED)
        ;

        // we launch the request and return the result
        return $query->getQuery()->getResult();
    }

    public function find(int $id): ?Proposal
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Proposal
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find proposals linked to imported users
     * We exclude proposals with wrong directions (can happen when importing data, when we can't sanitize the input data such as bad geographical coordinates).
     *
     * @return array
     */
    public function findImportedProposals(int $status)
    {
        $query = $this->repository->createQueryBuilder('p')
            ->select('p.id')
            ->join('p.criteria', 'c')
            ->join('c.directionDriver', 'd')
            ->join('p.user', 'u')
            ->join('u.import', 'i')
            ->where('i.status = :status and d.distance>0')
            ->andwhere('((c.frequency = 1 and c.fromDate >= :limitDate) or (c.frequency = 2 and c.toDate >= :limitDate and c.monCheck = 1 or c.tueCheck = 1 or c.wedCheck = 1 or c.thuCheck = 1 or c.friCheck = 1 or c.satCheck = 1 or c.sunCheck = 1)) and (p.private is null or p.private = 0)')
            ->setParameter('limitDate', (new \DateTime())->format('Y-m-d'))
            ->setParameter('status', $status)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find all valid proposals ids
     * We exclude proposals with wrong directions (can happen when importing data, when we can't sanitize the input data such as bad geographical coordinates).
     *
     * @param int $status
     *
     * @return array
     */
    public function findAllValidWithoutMatchingsProposalIds()
    {
        $query = $this->repository->createQueryBuilder('p')
            ->select('p.id')
            ->join('p.criteria', 'c')
            ->join('c.directionDriver', 'd')
            ->join('p.user', 'u')
            ->leftJoin('p.matchingRequests', 'mr')
            ->leftJoin('p.matchingOffers', 'mo')
            ->where('d.distance>0 and mr.id is null and mo.id is null')
            ->andwhere('(c.frequency = 1 or (c.monCheck = 1 or c.tueCheck = 1 or c.wedCheck = 1 or c.thuCheck = 1 or c.friCheck = 1 or c.satCheck = 1 or c.sunCheck = 1)) and (p.private is null or p.private = 0)')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find all proposals for a given user :
     * - the published ads
     * - the unpublished ads with accepted asks.
     *
     * @param User $user The user
     *
     * @return null|array The proposals found
     */
    public function findAllForUser(User $user)
    {
        // first we search the published outward proposals (returns will be included later)
        $query = $this->repository->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('p.criteria', 'c')
            ->where('(p.user = :user)')
            ->orderBy('c.fromDate', 'ASC')
            ->setParameter('user', $user)
        ;
        $proposals = $query->getQuery()->getResult();

        // then we search the accepted proposals as driver or passenger
        $acceptedProposals = $this->findAcceptedProposals($user);

        // then we merge the proposals
        foreach ($acceptedProposals as $acceptedProposal) {
            $found = false;
            foreach ($proposals as $proposal) {
                if ($proposal->getId() == $acceptedProposal->getId()) {
                    $found = true;

                    break;
                }
            }
            if (!$found) {
                $proposals[] = $acceptedProposal;
            }
        }

        return $proposals;
    }

    /**
     * Find accepted proposals for a given user.
     *
     * @param User $user The user
     *
     * @return null|array The proposals found
     */
    public function findAcceptedProposals(User $user)
    {
        // first we search the accepted proposals as driver
        $query = $this->repository->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('p.matchingRequests', 'mr')
            ->join('mr.asks', 'a')
            ->where('p.user = :user and (a.status = :acceptedAsDriver or a.status = :acceptedAsPassenger)')
            ->setParameter('user', $user)
            ->setParameter('acceptedAsDriver', Ask::STATUS_ACCEPTED_AS_DRIVER)
            ->setParameter('acceptedAsPassenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ;
        $proposalsDriver = $query->getQuery()->getResult();

        // then we search the accepted proposals as passenger
        $query = $this->repository->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('p.matchingOffers', 'mo')
            ->join('mo.asks', 'a')
            ->where('p.user = :user and (a.status = :acceptedAsDriver or a.status = :acceptedAsPassenger)')
            ->setParameter('user', $user)
            ->setParameter('acceptedAsDriver', Ask::STATUS_ACCEPTED_AS_DRIVER)
            ->setParameter('acceptedAsPassenger', Ask::STATUS_ACCEPTED_AS_PASSENGER)
        ;
        $proposalsPassenger = $query->getQuery()->getResult();

        // we create the results (we can't simply merge the results, we have to keep only outward for return trips if both outward and return are carpooled)
        $proposals = [];
        // we start with the proposals as driver
        foreach ($proposalsDriver as $proposalDriver) {
            // we have to check for an existing outward for a return proposal => we keep only outward
            if (Proposal::TYPE_RETURN == $proposalDriver->getType()) {
                $foundOutward = false;
                foreach ($proposalsDriver as $proposal) {
                    if ($proposal->getId() == $proposalDriver->getProposalLinked()->getId()) {
                        $foundOutward = true;

                        break;
                    }
                }
                if (!$foundOutward) {
                    // only return trip, we check that the proposal doesn't exist yet in the final array
                    $found = false;
                    foreach ($proposals as $proposal) {
                        if ($proposal->getId() == $proposalDriver->getId()) {
                            $found = true;

                            break;
                        }
                    }
                    if (!$found) {
                        $proposals[] = $proposalDriver;
                    }
                }
            } else {
                // outward or oneway, we check that the proposal doesn't exist yet in the final array
                $found = false;
                foreach ($proposals as $proposal) {
                    if ($proposal->getId() == $proposalDriver->getId()) {
                        $found = true;

                        break;
                    }
                }
                if (!$found) {
                    $proposals[] = $proposalDriver;
                }
            }
        }
        // we merge the proposals as passenger
        foreach ($proposalsPassenger as $proposalPassenger) {
            // we have to check for an existing outward for a return proposal => we keep only outward
            if (Proposal::TYPE_RETURN == $proposalPassenger->getType()) {
                $foundOutward = false;
                foreach ($proposalsPassenger as $proposal) {
                    if ($proposal->getId() == $proposalPassenger->getProposalLinked()->getId()) {
                        $foundOutward = true;

                        break;
                    }
                }
                if (!$foundOutward) {
                    // only return trip, we check that the proposal doesn't exist yet in the final array
                    $found = false;
                    foreach ($proposals as $proposal) {
                        if ($proposal->getId() == $proposalPassenger->getId()) {
                            $found = true;

                            break;
                        }
                    }
                    if (!$found) {
                        $proposals[] = $proposalPassenger;
                    }
                }
            } else {
                // outward or oneway, we check that the proposal doesn't exist yet in the final array
                $found = false;
                foreach ($proposals as $proposal) {
                    if ($proposal->getId() == $proposalPassenger->getId()) {
                        $found = true;

                        break;
                    }
                }
                if (!$found) {
                    $proposals[] = $proposalPassenger;
                }
            }
        }

        return $proposals;
    }

    /**
     * Get the proposals (not private only) valid for a specific date (optional : a specific user).
     *
     * @param \Datetime $date                 The date we want the Proposal valid for
     * @param User      $user                 Optional : A specific User
     * @param bool      $onlyOneWayOrOutward  Optional : Return only oneway and ouward proposals
     * @param int       $minDistanceDriver    Optional : Return only if driver's direction distance is > $minDistanceDriver (in meters)
     * @param int       $minDistancePassenger Optional : Return only if passenger's direction distance is > $minDistancePassenger (in meters)
     * @param array     $excludedProposalIds  Optional : Array if proposal id we want to exclude
     *
     * @return Proposal[]
     */
    public function findByDate(\DateTime $date, User $user = null, bool $onlyOneWayOrOutward = false, int $minDistanceDriver = null, int $minDistancePassenger = null, array $excludedProposalIds = []): array
    {
        $query = $this->repository->createQueryBuilder('p')
            ->join('p.criteria', 'c')
            ->where('(c.frequency = :punctualFrequency and c.fromDate = :date) or
                    (c.frequency = :regularFrequency and c.fromDate <= :date and c.toDate >= :date and c.'.strtolower($date->format('D')).'Check=1) ')
            ->andWhere('p.private = 0')
            ->andWhere('p.paused = 0')
            ->setParameter('punctualFrequency', Criteria::FREQUENCY_PUNCTUAL)
            ->setParameter('regularFrequency', Criteria::FREQUENCY_REGULAR)
            ->setParameter('date', $date->format('Ymd'))
        ;

        if (!is_null($onlyOneWayOrOutward)) {
            $query->andWhere('p.type = :typeOneWay or p.type = :typeOutward')
                ->setParameter('typeOneWay', Proposal::TYPE_ONE_WAY)
                ->setParameter('typeOutward', Proposal::TYPE_OUTWARD)
            ;
        }

        if (!is_null($user)) {
            $query->andWhere('p.user = :user')
                ->setParameter('user', $user)
            ;
        }

        if (!is_null($minDistanceDriver)) {
            $query->join('c.directionDriver', 'dv')
                ->andWhere('dv.distance > :minDistanceDriver')
                ->setParameter('minDistanceDriver', $minDistanceDriver)
            ;
        }

        if (!is_null($minDistancePassenger)) {
            $query->join('c.directionPassenger', 'dp')
                ->andWhere('dp.distance > :minDistancePassenger')
                ->setParameter('minDistancePassenger', $minDistancePassenger)
            ;
        }

        if (count($excludedProposalIds) > 0) {
            $query->andWhere('p.id not in (:excludedProposalIds)')
                ->setParameter('excludedProposalIds', $excludedProposalIds)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * get proposals of a user linked to a community.
     *
     * @return null|Proposal[]
     */
    public function findUserCommunityProposals(User $user)
    {
        $query = $this->repository->createQueryBuilder('p')
            ->join('p.communities', 'co')
            ->where('p.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * get proposals of a user linked to an event.
     *
     * @return null|Proposal[]
     */
    public function findUserEventProposals(User $user)
    {
        $query = $this->repository->createQueryBuilder('p')
            ->where('p.event IS NOT NULL')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Get the number of active ad for a given user and a given role.
     *
     * @param int $userId The user id
     * @param int $role   The role
     *
     * @return int The number of ads
     */
    public function getNbActiveAdsForUserAndRole(int $userId, int $role): int
    {
        $now = new \DateTime();
        $qb = $this->repository->createQueryBuilder('p')
            ->leftJoin('p.criteria', 'c')
            ->leftJoin('p.user', 'u')
            ->select('count(c.id) as nb')
            ->andWhere('u.id = :id and (p.private is null or p.private = 0)')
        ;
        if (Ad::ROLE_DRIVER == $role) {
            $qb->andWhere('c.driver = 1 and (
                (c.frequency = 1 and c.fromDate >= :now) or
                (c.frequency = 2 and c.fromDate <= :now and c.toDate >= :now)
            )');
        } elseif (Ad::ROLE_PASSENGER == $role) {
            $qb->andWhere('c.passenger = 1 and (
                (c.frequency = 1 and c.fromDate >= :now) or
                (c.frequency = 2 and c.fromDate <= :now and c.toDate >= :now)
            )');
        }
        $result = $qb->setParameter('id', $userId)
            ->setParameter('now', $now->format('Y-m-d'))
            ->getQuery()->getOneOrNullResult();

        if (!is_null($result['nb'])) {
            return (int) $result['nb'];
        }

        return 0;
    }

    /**
     * Find proposals (only not private and of type 1 or 2) created between startDate and endDate.
     *
     * @param \DateTime $startDate Range start date
     * @param \DateTime $endDate   Range end date
     *
     * @return int
     */
    public function countProposalsBetweenCreateDate(\DateTime $startDate, \DateTime $endDate): ?int
    {
        $query = $this->repository->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.createdDate between :startDate and :endDate ')
            ->andWhere('p.private = 0')
            ->andWhere('p.type = :typeOneWay or p.type = :typeOutward')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('typeOneWay', Proposal::TYPE_ONE_WAY)
            ->setParameter('typeOutward', Proposal::TYPE_OUTWARD)
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return null|Proposal[]
     */
    public function findCommunityAds(Community $community): ?array
    {
        $now = new \DateTime('now');

        $query = $this->repository->createQueryBuilder('p')
            ->join('p.communities', 'com')
            ->join('p.criteria', 'c')
            ->where('com.id = :communityId')
            ->andWhere('p.private = 0')
            ->andWhere('p.type = 1 or p.type = 2')
            ->andWhere('(c.frequency = :punctual AND c.fromDate is not null AND c.fromDate >= :date) OR (c.frequency = :regular AND c.toDate is not null and c.toDate >= :date)')
            ->setParameter('communityId', $community->getId())
            ->setParameter('punctual', Criteria::FREQUENCY_PUNCTUAL)
            ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
            ->setParameter('date', $now->format('Y-m-d 00:00:00'))
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Count the avalaible ads.
     *
     * @return int
     */
    public function countAvailableAds(): ?int
    {
        $now = new \DateTime('now');

        $query = $this->repository->createQueryBuilder('p')
            ->join('p.criteria', 'c')
            ->select('count(p.id)')
            ->where('p.private = 0 OR p.private IS NULL')
            ->andWhere('(c.frequency = :punctual AND c.fromDate >= :date) OR (c.frequency = :regular AND c.toDate >= :date)')
            ->setParameter('punctual', Criteria::FREQUENCY_PUNCTUAL)
            ->setParameter('regular', Criteria::FREQUENCY_REGULAR)
            ->setParameter('date', $now->format('Y-m-d 00:00:00'))
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return null|Proposal[]
     */
    public function findUserOrphanProposals(User $user): ?array
    {
        $query = $this->repository->createQueryBuilder('p')
            ->leftJoin('p.waypoints', 'w')
            ->where('p.user = :user')
            ->andWhere('w.proposal is null')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * @return null|Proposal[]
     */
    public function findProposalsOutdated(int $numberOfDays): ?array
    {
        $now = new \DateTime();

        $query = $this->repository->createQueryBuilder('p')
            ->join('p.criteria', 'c')
            ->where('p.private = 0 OR p.private IS NULL')
            ->andWhere('c.frequency = :regularFrequency  AND c.toDate is not null')
            ->andWhere("DATE_SUB(c.toDate, :numberOfDays, 'DAY') = :now")
            ->andWhere('p.type = :typeOneWay or p.type = :typeOutward')
            ->setParameter('typeOneWay', Proposal::TYPE_ONE_WAY)
            ->setParameter('typeOutward', Proposal::TYPE_OUTWARD)
            ->setParameter('regularFrequency', Criteria::FREQUENCY_REGULAR)
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('numberOfDays', $numberOfDays)
        ;

        return $query->getQuery()->getResult();
    }

    public function findSoonExpiredAds(int $numberOfDays): ?array
    {
        $now = new \DateTime();
        $toDate = $now->modify('+'.$numberOfDays.' days')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('p')
            ->join('p.criteria', 'c')
            ->where('p.private = 0 OR p.private IS NULL')
            ->andWhere('c.frequency = :regularFrequency')
            ->andWhere('c.toDate = :toDate')
            ->setParameter('regularFrequency', Criteria::FREQUENCY_REGULAR)
            ->setParameter('toDate', $toDate)
        ;

        return $query->getQuery()->getResult();
    }

    public function findInactiveAdsSinceXDays(int $numberOfDays): ?array
    {
        $now = new \DateTime();
        $toDate = $now->modify('-'.$numberOfDays.' days')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('p')
            ->join('p.criteria', 'c')
            ->where('p.private = 0 OR p.private IS NULL')
            ->andWhere('c.frequency = :regularFrequency')
            ->andWhere('c.toDate = :toDate')
            ->setParameter('regularFrequency', Criteria::FREQUENCY_REGULAR)
            ->setParameter('toDate', $toDate)
        ;

        return $query->getQuery()->getResult();
    }

    public function findPunctualAdWithoutAskSinceXDays(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('-'.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT proposal.id AS proposal_id,
            count(DISTINCT ask.id) AS nb_ask
            FROM proposal
            INNER JOIN criteria ON proposal.criteria_id = criteria.id
            LEFT JOIN matching ON matching.proposal_offer_id = proposal.id
            LEFT JOIN ask ON ask.matching_id = matching.id
            WHERE date(proposal.created_date) = '".$createdDate."' AND criteria.frequency = 1 AND proposal.private = 0
            GROUP BY proposal.id
            HAVING nb_ask = 0;"
        );
        $stmt->execute();
        $offers = $stmt->fetchAll();

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT proposal.id AS proposal_id,
            count(DISTINCT ask.id) AS nb_ask
            FROM proposal
            INNER JOIN criteria ON proposal.criteria_id = criteria.id
            LEFT JOIN matching ON matching.proposal_request_id = proposal.id
            LEFT JOIN ask ON ask.matching_id = matching.id
            WHERE date(proposal.created_date) = '".$createdDate."' AND criteria.frequency = 1 AND proposal.private = 0
            GROUP BY proposal.id
            HAVING nb_ask = 0;"
        );
        $stmt->execute();
        $requests = $stmt->fetchAll();

        $proposals = [];
        foreach ($offers as $offer) {
            if (in_array($offer, $proposals)) {
                continue;
            }
            $proposals[] = $offer;
        }
        foreach ($requests as $request) {
            if (in_array($request, $proposals)) {
                continue;
            }
            $proposals[] = $request;
        }

        return $proposals;
    }

    public function findRegularAdWithoutAskSinceXDays(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('-'.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT proposal.id AS proposal_id,
            count(DISTINCT ask.id) AS nb_ask
            FROM proposal
            INNER JOIN criteria ON proposal.criteria_id = criteria.id
            INNER JOIN matching ON matching.proposal_offer_id = proposal.id
            LEFT JOIN ask ON ask.matching_id = matching.id
            WHERE date(proposal.created_date) = '".$createdDate."' AND criteria.frequency = 2 AND proposal.private = 0
            GROUP BY proposal.id
            HAVING nb_ask = 0;"
        );
        $stmt->execute();
        $offers = $stmt->fetchAll();

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT proposal.id AS proposal_id,
            count(DISTINCT ask.id) AS nb_ask
            FROM proposal
            INNER JOIN criteria ON proposal.criteria_id = criteria.id
            INNER JOIN matching ON matching.proposal_request_id = proposal.id
            LEFT JOIN ask ON ask.matching_id = matching.id
            WHERE date(proposal.created_date) = '".$createdDate."' AND criteria.frequency = 2 AND proposal.private = 0
            GROUP BY proposal.id
            HAVING nb_ask = 0;"
        );
        $stmt->execute();
        $requests = $stmt->fetchAll();

        $proposals = [];
        foreach ($offers as $offer) {
            if (in_array($offer, $proposals)) {
                continue;
            }
            $proposals[] = $offer;
        }
        foreach ($requests as $request) {
            if (in_array($request, $proposals)) {
                continue;
            }
            $proposals[] = $request;
        }

        return $proposals;
    }

    public function userExportActiveProposal(User $user)
    {
        $query = "SELECT tp_extended.user_id, GROUP_CONCAT(tp_extended.Annonce1_Origine) as Annonce1_Origine, GROUP_CONCAT(tp_extended.Annonce1_Destination) as Annonce1_Destination, GROUP_CONCAT(tp_extended.Annonce1_Frequence) as Annonce1_Frequence, GROUP_CONCAT(tp_extended.Annonce2_Origine) as Annonce2_Origine, GROUP_CONCAT(tp_extended.Annonce2_Destination) as Annonce2_Destination, GROUP_CONCAT(tp_extended.Annonce2_Frequence) as Annonce2_Frequence, GROUP_CONCAT(tp_extended.Annonce3_Origine) as Annonce3_Origine, GROUP_CONCAT(tp_extended.Annonce3_Destination) as Annonce3_Destination, GROUP_CONCAT(tp_extended.Annonce3_Frequence) as Annonce3_Frequence, MAX(tp_extended.NombreAnnonces) as NombreAnnonces FROM ( SELECT tp.user_id, case when tp.OrdreAnnonce = 1 then tp.AnnonceOrigine end as Annonce1_Origine, case when tp.OrdreAnnonce = 1 then tp.AnnonceDestination end as Annonce1_Destination, case when tp.OrdreAnnonce = 1 then tp.AnnonceFrequence end as Annonce1_Frequence, case when tp.OrdreAnnonce = 2 then tp.AnnonceOrigine end as Annonce2_Origine, case when tp.OrdreAnnonce = 2 then tp.AnnonceDestination end as Annonce2_Destination, case when tp.OrdreAnnonce = 2 then tp.AnnonceFrequence end as Annonce2_Frequence, case when tp.OrdreAnnonce = 3 then tp.AnnonceOrigine end as Annonce3_Origine, case when tp.OrdreAnnonce = 3 then tp.AnnonceDestination end as Annonce3_Destination, case when tp.OrdreAnnonce = 3 then tp.AnnonceFrequence end as Annonce3_Frequence, max(tp.OrdreAnnonce) as NombreAnnonces FROM ( select p.user_id, ROW_NUMBER() OVER ( PARTITION BY p.user_id ORDER BY p.created_date ASC ) as OrdreAnnonce, ad.address_locality as AnnonceOrigine, aa.address_locality as AnnonceDestination, CASE c.frequency WHEN 1 THEN 'Occasionnel' WHEN 2 THEN 'Régulier' END as AnnonceFrequence from proposal p inner join criteria c on c.id = p.criteria_id inner join waypoint wd on ( wd.proposal_id = p.id and wd.position = 0 ) inner join waypoint wa on ( wa.proposal_id = p.id and wa.destination = 1 ) inner join address ad on ad.id = wd.address_id inner join address aa on aa.id = wa.address_id where p.private = 0 and ( p.dynamic != 1 or p.dynamic is null ) and ( ( c.frequency = 1 and c.from_date > NOW() ) or c.frequency = 2 and c.to_date > NOW() ) AND p.user_id = :user_id ) as tp GROUP BY tp.user_id, Annonce1_Origine, Annonce1_Destination, Annonce1_Frequence, Annonce2_Origine, Annonce2_Destination, Annonce2_Frequence, Annonce3_Origine, Annonce3_Destination, Annonce3_Frequence ) as tp_extended GROUP BY tp_extended.user_id";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute(['user_id' => $user->getId()]);

        return $stmt->fetch();
    }

    public function getUserMaxValidityAnnonceDate(User $user)
    {
        $query = 'SELECT MAX(tmva.AnnonceFinValidite) as MaxValiditeAnnonce FROM ( select p.user_id, CASE c.frequency WHEN 1 THEN c.from_date WHEN 2 THEN c.to_date END as AnnonceFinValidite from proposal p inner join criteria c on c.id = p.criteria_id where p.private = 0 and ( p.dynamic != 1 or p.dynamic is null ) AND p.user_id = :user_id ) as tmva GROUP BY tmva.user_id';

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute(['user_id' => $user->getId()]);

        return $stmt->fetch();
    }

    /**
     * Build the regular where part for a punctual proposal.
     *
     * @param \DateTime $fromDate    The date of the journey
     * @param int       $offset      The day offset
     * @param bool      $driver      If the proposal is for a driver
     * @param bool      $passenger   If the proposal is for a passenger
     * @param bool      $driversOnly If we search only drivers
     * @param bool      $useTime     If we use the time
     */
    private function buildRegularAndWhere(
        \DateTime $fromDate,
        int $offset,
        bool $driver,
        bool $passenger,
        bool $driversOnly,
        bool $useTime
    ): array {
        $regularAndWhere = '';

        $day = clone $fromDate;
        $day->modify('+'.$offset.' day');
        $dayLitteral = strtolower($day->format('D'));

        if ($offset > 0) {
            $regularAndWhere = ' or ';
        }

        $regularAndWhere .= '(c.'.$dayLitteral.'Check = 1';
        $setMinTime = $setMaxTime = false;
        if (!$driversOnly && $driver && $passenger) {
            if ($useTime) {
                $regularAndWhere .= ' and (
                    (c.passenger = 1'.((0 == $offset) ? ' and c.'.$dayLitteral.'MaxTime >= :minTime' : '').') or
                    (c.driver = 1'.((0 == $offset) ? ' and c.'.$dayLitteral.'MinTime <= :maxTime' : '').')
                )';
                $setMinTime = true;
                $setMaxTime = true;
            }
        } elseif ($driver) {
            if ($useTime) {
                $regularAndWhere .= ' and (c.passenger = 1'.((0 == $offset) ? ' and c.'.$dayLitteral.'MaxTime >= :minTime' : '').')';
                $setMinTime = true;
            }
        } else {
            if ($useTime) {
                $regularAndWhere .= ' and (c.driver = 1'.((0 == $offset) ? ' and c.'.$dayLitteral.'MinTime <= :maxTime' : '').')';
                $setMaxTime = true;
            }
        }

        $regularAndWhere .= ' and (c.frequency='.Criteria::FREQUENCY_REGULAR.' and ';
        // $regularAndWhere .= "c.fromDate <= '".$day->format('Y-m-d')."' and ";
        $regularAndWhere .= "c.toDate >= '".$day->format('Y-m-d')."'))";

        return [
            $regularAndWhere,
            $setMinTime,
            $setMaxTime,
        ];
    }

    /**
     * Return the geoJson string representation of a bounding box polygon.
     */
    private function getGeoPolygon(float $minLon, float $minLat, float $maxLon, float $maxLat)
    {
        return 'POLYGON(('.$minLon.' '.$minLat.','.$minLon.' '.$maxLat.','.$maxLon.' '.$maxLat.','.$maxLon.' '.$minLat.','.$minLon.' '.$minLat.'))';
    }

    /**
     * Get the extension length of the bounding box from the original distance of the direction.
     *
     * @param int $distance The distance in metres
     *
     * @return int The extension in metres
     */
    private function getBBoxExtension(int $distance)
    {
        if ($distance < 20000) {
            return 3000;
        }
        if ($distance < 30000) {
            return 5000;
        }
        if ($distance < 50000) {
            return 8000;
        }
        if ($distance < 100000) {
            return 10000;
        }

        return 20000;
    }
}
