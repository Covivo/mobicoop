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
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\ProposalPostedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Criteria;
use App\Geography\Entity\Address;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\ProposalRepository;
use App\Geography\Repository\DirectionRepository;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\ZoneManager;
use App\Geography\Entity\Zone;
use App\DataProvider\Entity\GeoRouterProvider;
use Psr\Log\LoggerInterface;
use App\Geography\Service\TerritoryManager;
use App\User\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    private $entityManager;
    private $proposalMatcher;
    private $proposalRepository;
    private $geoRouter;
    private $zoneManager;
    private $directionRepository;
    private $territoryManager;
    private $userRepository;
    private $logger;
    private $eventDispatcher;
 
 /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalMatcher $proposalMatcher
     * @param ProposalRepository $proposalRepository
     * @param DirectionRepository $directionRepository
     * @param GeoRouter $geoRouter
     * @param ZoneManager $zoneManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher, ProposalRepository $proposalRepository, DirectionRepository $directionRepository, GeoRouter $geoRouter, ZoneManager $zoneManager, TerritoryManager $territoryManager, LoggerInterface $logger, UserRepository $userRepository,  EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->proposalRepository = $proposalRepository;
        $this->directionRepository = $directionRepository;
        $this->geoRouter = $geoRouter;
        $this->zoneManager = $zoneManager;
        $this->territoryManager = $territoryManager;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->eventDispatcher= $dispatcher;
    }
    
    /**
     * Create a proposal.
     *
     * @param Proposal $proposal    The proposal to create
     * @param boolean $persist      If we persist the proposal in the database (false for a simple search)
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return Proposal             The created proposal
     */
    public function createProposal(Proposal $proposal, $persist=true, bool $excludeProposalUser=true)
    {
        $date = new \DateTime("UTC");
        $this->logger->info('Proposal creation | Start ' . $date->format("Ymd H:i:s.u"));

        // temporary initialisation, will be dumped when implementation of these fields will be done
        $proposal->getCriteria()->setSeats(1);
        $proposal->getCriteria()->setAnyRouteAsPassenger(true);
        $proposal->getCriteria()->setStrictDate(true);

        // calculation of the min and max times
        if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFromTime(), $proposal->getCriteria()->getMarginDuration());
            $proposal->getCriteria()->setMinTime($minTime);
            $proposal->getCriteria()->setMaxTime($maxTime);
        } else {
            if ($proposal->getCriteria()->isMonCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getMonTime(), $proposal->getCriteria()->getMonMarginDuration());
                $proposal->getCriteria()->setMonMinTime($minTime);
                $proposal->getCriteria()->setMonMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isTueCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getTueTime(), $proposal->getCriteria()->getTueMarginDuration());
                $proposal->getCriteria()->setTueMinTime($minTime);
                $proposal->getCriteria()->setTueMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isWedCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getWedTime(), $proposal->getCriteria()->getWedMarginDuration());
                $proposal->getCriteria()->setWedMinTime($minTime);
                $proposal->getCriteria()->setWedMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isThuCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getThuTime(), $proposal->getCriteria()->getThuMarginDuration());
                $proposal->getCriteria()->setThuMinTime($minTime);
                $proposal->getCriteria()->setThuMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isFriCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFriTime(), $proposal->getCriteria()->getFriMarginDuration());
                $proposal->getCriteria()->setFriMinTime($minTime);
                $proposal->getCriteria()->setFriMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSatCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSatTime(), $proposal->getCriteria()->getSatMarginDuration());
                $proposal->getCriteria()->setSatMinTime($minTime);
                $proposal->getCriteria()->setSatMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSunCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSunTime(), $proposal->getCriteria()->getSunMarginDuration());
                $proposal->getCriteria()->setSunMinTime($minTime);
                $proposal->getCriteria()->setSunMaxTime($maxTime);
            }
        }

        // creation of the directions
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            $direction = $routes[0];
            // creation of the crossed zones
            $direction = $this->zoneManager->createZonesForDirection($direction);
            if ($proposal->getCriteria()->isDriver()) {
                $proposal->getCriteria()->setDirectionDriver($direction);
            }
            if ($proposal->getCriteria()->isPassenger()) {
                $proposal->getCriteria()->setDirectionPassenger($direction);
            }
        }

        // matching analyze
        $proposal = $this->proposalMatcher->createMatchingsForProposal($proposal, $excludeProposalUser);
        $this->logger->info('Proposal creation | End matching ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        if ($persist) {
            // TODO : here we should remove the previously matched proposal if they already exist
            $this->entityManager->persist($proposal);
            $this->logger->info('Proposal creation | End persist ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }

        $end = new \DateTime("UTC");
        $this->logger->info('Proposal creation | Total duration ' . ($end->diff($date))->format("%s.%f seconds"));
 
				// dispatch en event
				$event = new ProposalPostedEvent($proposal);
				$this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);
        return $proposal;
    }

    /**
     * Get the matchings for the given proposal.
     * Used for simple search.
     *
     * @param Proposal $proposal    The proposal for which we search the matchings
     * @return Proposal             The posted proposal with its matchings
     */
    public function getMatchings(Proposal $proposal)
    {
        return $this->createProposal($proposal, false, false);
    }

    /**
     * Create a minimal proposal for a simple search.
     * Only punctual and one way trip.
     *
     * @param float $originLatitude
     * @param float $originLongitude
     * @param float $destinationLatitude
     * @param float $destinationLongitude
     * @param \Datetime $date
     * @param int $userId
     * @return void
     */
    public function searchMatchings(
        float $originLatitude,
        float $originLongitude,
        float $destinationLatitude,
        float $destinationLongitude,
        \Datetime $date,
        ?int $userId=null
        ) {

        // we create a new Proposal object with its Criteria and Waypoints
        $proposal = new Proposal();
        $proposal->setType(Proposal::TYPE_ONE_WAY);
        $criteria = new Criteria();
        $criteria->setDriver(true);
        $criteria->setPassenger(true);
        $criteria->setFromDate($date);
        $criteria->setFromTime($date);
        $criteria->setMarginDuration(900);
        $criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        $proposal->setCriteria($criteria);

        if (!is_null($userId)) {
            if ($user = $this->userRepository->find($userId)) {
                $proposal->setUser($user);
            }
        }

        $waypointOrigin = new Waypoint();
        $originAddress = new Address();
        $originAddress->setLatitude((string)$originLatitude);
        $originAddress->setLongitude((string)$originLongitude);
        $waypointOrigin->setAddress($originAddress);
        $waypointOrigin->setPosition(0);
        $waypointOrigin->setDestination(false);

        $waypointDestination = new Waypoint();
        $destinationAddress = new Address();
        $destinationAddress->setLatitude((string)$destinationLatitude);
        $destinationAddress->setLongitude((string)$destinationLongitude);
        $waypointDestination->setAddress($destinationAddress);
        $waypointDestination->setPosition(1);
        $waypointDestination->setDestination(true);

        $proposal->addWaypoint($waypointOrigin);
        $proposal->addWaypoint($waypointDestination);

        return $this->getMatchings($proposal);
    }


    /**
     * Updates directions without zones (so by extension, updates the related proposals, that's why it's in this file...)
     * Used for testing purpose, shouldn't be useful as zones are added when proposals/directions are posted.
     *
     * @return void
     */
    public function updateZones()
    {
        if ($directions = $this->directionRepository->findAllWithoutZones()) {
            foreach ($directions as $direction) {
                if (is_null($direction->getPoints())) {
                    // we use the GeoRouterProvider as a service
                    $georouter = new GeoRouterProvider();
                    $direction->setPoints($georouter->deserializePoints($direction->getDetail(), true, filter_var($georouter::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
                }
                // creation of the crossed zones
                $zones = [];
                foreach (self::THINNESSES as $thinness) {
                    // $zones[$thinness] would be simpler and better... but we can't use a float as a key with php (transformed to string)
                    // so we use an inner value for thinness
                    $zones[] = [
                        'thinness' => $thinness,
                        'crossed' => $this->zoneManager->getZonesForAddresses($direction->getPoints(), $thinness, 0)
                    ];
                }
                foreach ($zones as $crossed) {
                    foreach ($crossed['crossed'] as $zoneCrossed) {
                        $zone = new Zone();
                        $zone->setZoneid($zoneCrossed);
                        $zone->setThinness($crossed['thinness']);
                        $direction->addZone($zone);
                    }
                }
                $this->entityManager->persist($direction);
            }
            $this->entityManager->flush();
        }
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
        $addressFrom->setLongitude((string)$from_longitude);
        $addressFrom->setLatitude((string)$from_latitude);
        // for now we don't search with coordinates, we force the localities for testing purpose
        // @todo delete the locality search only
        $addressFrom->setAddressLocality("Nancy");
        $addressTo = new Address();
        $addressTo->setLongitude((string)$to_longitude);
        $addressTo->setLatitude((string)$to_latitude);
        $addressTo->setAddressLocality("Metz");
        $waypointFrom = new Waypoint();
        $waypointFrom->setAddress($addressFrom);
        $waypointFrom->setPosition(0);
        $waypointFrom->setDestination(false);
        $waypointTo = new Waypoint();
        $waypointTo->setAddress($addressTo);
        $waypointTo->setPosition(1);
        $waypointTo->setDestination(true);
        $criteria = new Criteria();
        $criteria->setDriver(!$offer);
        $criteria->setPassenger(!$request);
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

    // returns the min and max time from a time and a margin
    private static function getMinMaxTime($time, $margin)
    {
        $minTime = clone $time;
        $maxTime = clone $time;
        $minTime->sub(new \DateInterval('PT' . $margin . 'S'));
        if ($minTime->format('j') <> $time->format('j')) {
            // the day has changed => we keep '00:00' as min time
            $minTime = new \Datetime('00:00:00');
        }
        $maxTime->add(new \DateInterval('PT' . $margin . 'S'));
        if ($maxTime->format('j') <> $time->format('j')) {
            // the day has changed => we keep '23:59:00' as max time
            $maxTime = new \Datetime('23:59:00');
        }
        return [
            $minTime,
            $maxTime
        ];
    }
}
