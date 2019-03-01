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

namespace App\Carpool\Service;

use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Criteria;
use App\Geography\Entity\Address;
use App\Carpool\Entity\WayPoint;
use App\Carpool\Repository\ProposalRepository;
use App\Geography\Service\GeoRouter;
use App\Geography\Entity\Direction;
use App\DataProvider\Entity\GeoRouterProvider;
use App\Geography\Service\ZoneManager;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    private $entityManager;
    private $matchingAnalyzer;
    private $proposalRepository;
    private $geoRouter;
    // private $zoneManager;

    public function __construct(EntityManagerInterface $entityManager, MatchingAnalyzer $matchingAnalyzer, ProposalRepository $proposalRepository, GeoRouter $geoRouter)
    {
        $this->entityManager = $entityManager;
        $this->matchingAnalyzer = $matchingAnalyzer;
        $this->proposalRepository = $proposalRepository;
        $this->geoRouter = $geoRouter;
        // $this->zoneManager = $zoneManager;
    }
    
    /**
     * Create a proposal.
     *
     * @param Proposal $proposal
     */
    public function createProposal(Proposal $proposal)
    {   
        // temporary initialisation, will be dumped when implementation of these fields will be done
        $proposal->getCriteria()->setSeats(1);
        $proposal->getCriteria()->setAnyRouteAsPassenger(true);

        // creation of the directions
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            // creation of the zones
            $points = $routes[0]->deserializePoints($routes[0]->getDetail(), false, filter_var(GeoRouterProvider::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN));
            // $zones = $this->zoneManager->getZonesForAddresses($points);
            // foreach ($zones as $zone) {
            //     $routes[0]->addZone($zone);
            // }
            $proposal->getCriteria()->setDirectionDriver($routes[0]);
        }

        $this->entityManager->persist($proposal);
        $this->entityManager->flush();

        // return the proposal (not really necessary, but good practice ?)
        return $proposal;
        
        // $proposalLinked = null;
        // $reversedWaypoints = [];
        // $nbWaypoints = 0;
        // if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
        //     // we will need the reverse waypoints
        //     $nbWaypoints = count($proposal->getWaypoints());
        //     // we need to get the waypoints in reverse order
        //     // we will read the wappoints a first time to create an array with the position as index
        //     $aWaypoints = [];
        //     foreach ($proposal->getWaypoints() as $proposalWaypoint) {
        //         $aWaypoints[$proposalWaypoint->getPosition()] = $proposalWaypoint;
        //     }
        //     // we sort the array by key
        //     ksort($aWaypoints);
        //     // our array is ordered by position, we read it backwards
        //     $reversedWaypoints = array_reverse($aWaypoints);
            
        //     $proposalLinked = clone $proposal;
        //     $proposalLinked->setType(Proposal::TYPE_RETURN);
        //     // criteria
        //     $proposalLinked->setCriteria(clone $proposal->getCriteria());
        //     foreach ($reversedWaypoints as $pos=>$proposalWaypoint) {
        //         $waypoint = clone $proposalWaypoint;
        //         $waypoint->setPosition($pos);
        //         $waypoint->setIsDestination(false);
        //         // address
        //         $waypoint->setAddress(clone $proposalWaypoint->getAddress());
        //         if ($pos == ($nbWaypoints-1)) {
        //             $waypoint->setIsDestination(true);
        //         }
        //         $proposalLinked->addWaypoint($waypoint);
        //     }
        //     $proposal->setProposalLinked($proposalLinked);
        // }
        
        // persistence
        // if (!is_null($proposalLinked)) {
        //     $this->entityManager->persist($proposalLinked);
        // }

        // matching analyze
        // => should be replaced by path analyzer when it's created
        // => the analyze would be asked when all paths are analyzed and returned
        // $this->matchingAnalyzer->createMatchingsForProposal($proposal);
        // if (!is_null($proposalLinked)) {
        //     $this->matchingAnalyzer->createMatchingsForProposal($proposalLinked);
        // }
        
    }
    
    /**
     * Returns all proposals matching the parameters.
     * Used for RDEX export.
     *
     * @param bool $offer
     * @param bool $request
     * @param float $from_longitude
     * @param float $from_latitude
     * @param float $to_longitude
     * @param float $to_latitude
     * @param string $frequency
     * @param \DateTime $outward_mindate
     * @param \DateTime $outward_maxdate
     * @param string $outward_monday_mintime
     * @param string $outward_monday_maxtime
     * @param string $outward_tuesday_mintime
     * @param string $outward_tuesday_maxtime
     * @param string $outward_wednesday_mintime
     * @param string $outward_wednesday_maxtime
     * @param string $outward_thursday_mintime
     * @param string $outward_thursday_maxtime
     * @param string $outward_friday_mintime
     * @param string $outward_friday_maxtime
     * @param string $outward_saturday_mintime
     * @param string $outward_saturday_maxtime
     * @param string $outward_sunday_mintime
     * @param string $outward_sunday_maxtime
     */
    public function getProposalsForRdex(
        bool $offer,
        bool $request,
        float $from_longitude,
        float $from_latitude,
        float $to_longitude,
        float $to_latitude,
        string $frequency = null,
        \DateTime $outward_mindate = null,
        \DateTime $outward_maxdate = null,
        string $outward_monday_mintime = null,
        string $outward_monday_maxtime = null,
        string $outward_tuesday_mintime = null,
        string $outward_tuesday_maxtime = null,
        string $outward_wednesday_mintime = null,
        string $outward_wednesday_maxtime = null,
        string $outward_thursday_mintime = null,
        string $outward_thursday_maxtime = null,
        string $outward_friday_mintime = null,
        string $outward_friday_maxtime = null,
        string $outward_saturday_mintime = null,
        string $outward_saturday_maxtime = null,
        string $outward_sunday_mintime = null,
        string $outward_sunday_maxtime = null
        ) {
        // test : we return all proposals
        // we create a proposal with the parameters
        $proposal = new Proposal();
        $proposal->setType(Proposal::TYPE_ONE_WAY);
        $addressFrom = new Address();
        $addressFrom->setLongitude($from_longitude);
        $addressFrom->setLatitude($from_latitude);
        // for now we don't search with coordinates, we force the localities for testing purpose
        // @todo delete the locality search only
        $addressFrom->setAddressLocality("Nancy");
        $addressTo = new Address();
        $addressTo->setLongitude($to_longitude);
        $addressTo->setLatitude($to_latitude);
        $addressTo->setAddressLocality("Metz");
        $waypointFrom = new Waypoint();
        $waypointFrom->setAddress($addressFrom);
        $waypointFrom->setPosition(0);
        $waypointFrom->setIsDestination(false);
        $waypointTo = new Waypoint();
        $waypointTo->setAddress($addressTo);
        $waypointTo->setPosition(1);
        $waypointTo->setIsDestination(true);
        $criteria = new Criteria();
        $criteria->setIsDriver(!$offer);
        $criteria->setIsPassenger(!$request);
        if (!is_null($outward_mindate)) {
            $criteria->setFromDate($outward_mindate);
        } else {
            $criteria->setFromDate(new \DateTime());
        }
        if (!is_null($outward_maxdate)) {
            $criteria->setToDate($outward_maxdate);
        }
        $proposal->setCriteria($criteria);
        $proposal->addWaypoint($waypointFrom);
        $proposal->addWaypoint($waypointTo);
        // for now we don't use the time parameters
        // @todo add the time parameters
        return $this->proposalRepository->findMatchingProposals($proposal, false);
    }
}
