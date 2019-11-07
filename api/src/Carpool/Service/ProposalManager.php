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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\ResultItem;
use App\Carpool\Entity\ResultRole;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Repository\ProposalRepository;
use App\Community\Service\CommunityManager;
use App\DataProvider\Entity\GeoRouterProvider;
use App\Geography\Entity\Address;
use App\Geography\Entity\Zone;
use App\Geography\Repository\DirectionRepository;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\TerritoryManager;
use App\Geography\Service\ZoneManager;
use App\Service\FormatDataManager;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_BOTH = 3;

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
    private $communityManager;
    private $askManager;
    private $formatDataManager;
    private $params;

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
    public function __construct(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher, ProposalRepository $proposalRepository, DirectionRepository $directionRepository, GeoRouter $geoRouter, ZoneManager $zoneManager, TerritoryManager $territoryManager, CommunityManager $communityManager, LoggerInterface $logger, UserRepository $userRepository, EventDispatcherInterface $dispatcher, AskManager $askManager, FormatDataManager $formatDataManager, array $params)
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
        $this->eventDispatcher = $dispatcher;
        $this->communityManager = $communityManager;
        $this->askManager = $askManager;
        $this->formatDataManager = $formatDataManager;
        $this->params = $params;
    }

    /**
     * Create a proposal for a simple search.
     *
     * @param Address $origin                   The origin
     * @param Address $destination              The destination
     * @param integer $frequency                The frequency of the trip (1=punctual, 2=regular)
     * @param \Datetime|null $date              The date and time of the trip
     * @param boolean|null $useTime             True to use the time part of the date, false to ignore the time part
     * @param boolean|null $strictDate          True to limit the search to the date, false to search even in the next days (only for punctual trip)
     * @param boolean|null $strictPunctual      True to search only in punctual trips for punctual search, false to search also in regular trips
     * @param boolean|null $strictRegular       True to search only in regular trips for regular search, false to search also in punctual trips
     * @param integer|null $marginTime          The margin time in seconds
     * @param integer|null $regularLifeTime     The lifetime of a regular proposal in years
     * @param integer|null $userId              The id of the user that makes the query
     * @param integer|null $role                The role of the user that makes the query (1=driver, 2=passenger, 3=both)
     * @param integer|null $type                The type of the trip (1=one way, 2=return trip)
     * @param boolean|null $anyRouteAsPassenger True if the passenger accepts any route (not implemented yet)
     * @param integer|null $communityId         The id of the community to search in
     * @return void
     */
    public function searchMatchings(
        Address $origin,
        Address $destination,
        int $frequency,
        ?\Datetime $date = null,
        ?bool $useTime = null,
        ?bool $strictDate = null,
        ?bool $strictPunctual = null,
        ?bool $strictRegular = null,
        ?int $marginTime = null,
        ?int $regularLifeTime = null,
        ?int $userId = null,
        ?int $role = null,
        ?int $type = null,
        ?bool $anyRouteAsPassenger = null,
        ?int $communityId = null
    ) {
        // initialisation of the parameters
        $useTime = !is_null($useTime) ? $useTime : $this->params['defaultUseTime'];
        $strictDate = !is_null($strictDate) ? $strictDate : $this->params['defaultStrictDate'];
        $strictPunctual = !is_null($strictPunctual) ? $strictPunctual : $this->params['defaultStrictPunctual'];
        $strictRegular = !is_null($strictRegular) ? $strictRegular : $this->params['defaultStrictRegular'];
        $marginTime = !is_null($marginTime) ? $marginTime : $this->params['defaultMarginTime'];
        $regularLifeTime = !is_null($regularLifeTime) ? $regularLifeTime : $this->params['defaultRegularLifeTime'];
        $role = !is_null($role) ? $role : $this->params['defaultRole'];
        $type = !is_null($type) ? $type : $this->params['defaultType'];
        $anyRouteAsPassenger = !is_null($anyRouteAsPassenger) ? $anyRouteAsPassenger : $this->params['defaultAnyRouteAsPassenger'];
        
        // we create a new Proposal object with its Criteria and Waypoints
        $proposal = new Proposal();
        // we set the type, but for now we only treat the outward
        $proposal->setType($type == Proposal::TYPE_ONE_WAY ? Proposal::TYPE_ONE_WAY : Proposal::TYPE_OUTWARD);
        $criteria = new Criteria();
        $criteria->setDriver($role == self::ROLE_DRIVER || $role == self::ROLE_BOTH);
        $criteria->setPassenger($role == self::ROLE_PASSENGER || $role == self::ROLE_BOTH);
        if ($date) {
            $criteria->setFromDate($date);
            if ($useTime) {
                $criteria->setFromTime($date);
            }
        }
        $criteria->setMarginDuration($marginTime);
        $criteria->setFrequency($frequency);
        $criteria->setAnyRouteAsPassenger($anyRouteAsPassenger);
        if ($frequency == Criteria::FREQUENCY_REGULAR) {
            // for regular proposal we set every day as a possible carpooling day
            $criteria->setMonCheck(true);
            $criteria->setTueCheck(true);
            $criteria->setWedCheck(true);
            $criteria->setThuCheck(true);
            $criteria->setFriCheck(true);
            $criteria->setSatCheck(true);
            $criteria->setSunCheck(true);
            $criteria->setMonMarginDuration($marginTime);
            $criteria->setTueMarginDuration($marginTime);
            $criteria->setWedMarginDuration($marginTime);
            $criteria->setThuMarginDuration($marginTime);
            $criteria->setFriMarginDuration($marginTime);
            $criteria->setSatMarginDuration($marginTime);
            $criteria->setSunMarginDuration($marginTime);
            if ($useTime) {
                $criteria->setMonTime($date);
                $criteria->setTueTime($date);
                $criteria->setWedTime($date);
                $criteria->setThuTime($date);
                $criteria->setFriTime($date);
                $criteria->setSatTime($date);
                $criteria->setSunTime($date);
            }
            // we set the end date
            $endDate = clone $date;
            $endDate->add(new \DateInterval('P' . $regularLifeTime . 'Y'));
            $criteria->setToDate($endDate);
        }
        $criteria->setStrictDate($strictDate);
        $criteria->setStrictPunctual($strictPunctual);
        $criteria->setStrictRegular($strictRegular);
        $proposal->setCriteria($criteria);
        
        if (!is_null($userId)) {
            if ($user = $this->userRepository->find($userId)) {
                $proposal->setUser($user);
            }
        }
        
        $waypointOrigin = new Waypoint();
        $waypointOrigin->setAddress($origin);
        $waypointOrigin->setPosition(0);
        $waypointOrigin->setDestination(false);
        
        $waypointDestination = new Waypoint();
        $waypointDestination->setAddress($destination);
        $waypointDestination->setPosition(1);
        $waypointDestination->setDestination(true);
        
        $proposal->addWaypoint($waypointOrigin);
        $proposal->addWaypoint($waypointDestination);

        // community
        if ($communityId && $userId) {
            // we check if the user is member of the community
            if ($this->communityManager->isRegistered($communityId, $userId)) {
                if ($community = $this->communityManager->get($communityId)) {
                    $proposal->addCommunity($community);
                }
            }
        }

        // Get the matchings for the given proposal.
        return $this->createProposal($proposal, false, true);
    }

    /**
     * Create a proposal.
     *
     * @param Proposal  $proposal               The proposal to create
     * @param boolean   $persist                If we persist the proposal in the database (false for a simple search)
     * @param bool      $excludeProposalUser    Exclude the matching proposals made by the proposal user
     * @return Proposal The created proposal
     */
    public function createProposal(Proposal $proposal, $persist = true, bool $excludeProposalUser = true)
    {
        $date = new \DateTime("UTC");
        $this->logger->info('Proposal creation | Start ' . $date->format("Ymd H:i:s.u"));
                
        // calculation of the min and max times
        // we calculate the min and max times only if the time is set (it could be not set for a simple search)
        if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL && $proposal->getCriteria()->getFromTime()) {
            list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFromTime(), $proposal->getCriteria()->getMarginDuration());
            $proposal->getCriteria()->setMinTime($minTime);
            $proposal->getCriteria()->setMaxTime($maxTime);
        } else {
            if ($proposal->getCriteria()->isMonCheck() && $proposal->getCriteria()->getMonTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getMonTime(), $proposal->getCriteria()->getMonMarginDuration());
                $proposal->getCriteria()->setMonMinTime($minTime);
                $proposal->getCriteria()->setMonMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isTueCheck() && $proposal->getCriteria()->getTueTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getTueTime(), $proposal->getCriteria()->getTueMarginDuration());
                $proposal->getCriteria()->setTueMinTime($minTime);
                $proposal->getCriteria()->setTueMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isWedCheck() && $proposal->getCriteria()->getWedTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getWedTime(), $proposal->getCriteria()->getWedMarginDuration());
                $proposal->getCriteria()->setWedMinTime($minTime);
                $proposal->getCriteria()->setWedMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isThuCheck() && $proposal->getCriteria()->getThuTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getThuTime(), $proposal->getCriteria()->getThuMarginDuration());
                $proposal->getCriteria()->setThuMinTime($minTime);
                $proposal->getCriteria()->setThuMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isFriCheck() && $proposal->getCriteria()->getFriTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFriTime(), $proposal->getCriteria()->getFriMarginDuration());
                $proposal->getCriteria()->setFriMinTime($minTime);
                $proposal->getCriteria()->setFriMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSatCheck() && $proposal->getCriteria()->getSatTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSatTime(), $proposal->getCriteria()->getSatMarginDuration());
                $proposal->getCriteria()->setSatMinTime($minTime);
                $proposal->getCriteria()->setSatMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSunCheck() && $proposal->getCriteria()->getSunTime()) {
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
            // for now we only keep the first route !
            // if we ever want alternative routes we should pass the route as parameter of this method
            // (problem : the route has no id, we should pass the whole route to check which route is chosen by the user...
            //      => we would have to think of a way to simplify...)
            $direction = $routes[0];
            // creation of the crossed zones
            $direction = $this->zoneManager->createZonesForDirection($direction);
            if ($proposal->getCriteria()->isDriver()) {
                $proposal->getCriteria()->setDirectionDriver($direction);
                $proposal->getCriteria()->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100);
                $proposal->getCriteria()->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100);
            }
            if ($proposal->getCriteria()->isPassenger()) {
                // if the user is passenger we keep only the first and last points
                $routes = $this->geoRouter->getRoutes([$addresses[0],$addresses[count($addresses)-1]]);
                $direction = $routes[0];
                // creation of the crossed zones
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $proposal->getCriteria()->setDirectionPassenger($direction);
            }
        }
        
        // matching analyze
        $this->logger->info('Proposal creation | Start matching ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $proposal = $this->proposalMatcher->createMatchingsForProposal($proposal, $excludeProposalUser);
        $this->logger->info('Proposal creation | End matching ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        if ($persist) {
            // TODO : here we should remove the previously matched proposal if they already exist
            $this->entityManager->persist($proposal);
            $this->logger->info('Proposal creation | End persist ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }
        
        $end = new \DateTime("UTC");
        $this->logger->info('Proposal creation | Total duration ' . ($end->diff($date))->format("%s.%f seconds"));
        
        $matchingOffers = $proposal->getMatchingOffers();
        $matchingRequests = $proposal->getMatchingRequests();
        $matchings=[];
        while (($item = array_shift($matchingOffers)) !== null && array_push($matchings, $item));
        while (($item = array_shift($matchingRequests)) !== null && array_push($matchings, $item));
        if ($persist) {
            foreach ($matchings as $matching) {
                // if there is a matched proposal we need to find the right matching and create the Ask
                // but only for the outward if it's a return trip, to avoid creating 2 asks for the same trip
                if (!is_null($proposal->getMatchingProposal()) && is_null($proposal->getMatchingLinked())) {
                    if ($proposal->getMatchingProposal()->getId() === $matching->getProposalOffer()->getId() ||
                        $proposal->getMatchingProposal()->getId() === $matching->getProposalRequest()->getId()
                    ) {
                        // we create the ask
                        $this->askManager->createAskFromMatchedProposal($proposal, $matching, $proposal->hasFormalAsk());
                        // we set the matching linked if we need to create a forced reverse matching (can be the case for regular return trips)
                        $proposal->setMatchingLinked($matching);
                    }
                }

                // dispatch en event
                // maybe send a unique event for all matchings ?
                $event = new MatchingNewEvent($matching, $proposal->getUser());
                $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
            }
            // dispatch en event
            $event = new ProposalPostedEvent($proposal);
            $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);
        }

        // we treat the matchings to return the results
        $proposal->setResults($this->createResults($proposal));

        return $proposal;
    }

    /**
     * Prepare a proposal for persist.
     * Used when posting a proposal to populate default values like proposal validity.
     *
     * @param Proposal $proposal
     * @return void
     */
    public function prepareProposal(Proposal $proposal): Proposal
    {
        if (is_null($proposal->getCriteria()->getAnyRouteAsPassenger())) {
            $proposal->getCriteria()->setAnyRouteAsPassenger($this->params['defaultAnyRouteAsPassenger']);
        }
        if (is_null($proposal->getCriteria()->isStrictDate())) {
            $proposal->getCriteria()->setStrictDate($this->params['defaultStrictDate']);
        }
        if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            if (is_null($proposal->getCriteria()->isStrictPunctual())) {
                $proposal->getCriteria()->setStrictPunctual($this->params['defaultStrictPunctual']);
            }
            if (is_null($proposal->getCriteria()->getMarginDuration())) {
                $proposal->setMarginDuration($this->params['defaultMarginTime']);
            }
        } else {
            if (is_null($proposal->getCriteria()->isStrictRegular())) {
                $proposal->getCriteria()->setStrictRegular($this->params['defaultStrictRegular']);
            }
            if (is_null($proposal->getCriteria()->getMonMarginDuration())) {
                $proposal->getCriteria()->setMonMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getTueMarginDuration())) {
                $proposal->getCriteria()->setTueMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getWedMarginDuration())) {
                $proposal->getCriteria()->setWedMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getThuMarginDuration())) {
                $proposal->getCriteria()->setThuMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getFriMarginDuration())) {
                $proposal->getCriteria()->setFriMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getSatMarginDuration())) {
                $proposal->getCriteria()->setSatMarginDuration($this->params['defaultMarginTime']);
            }
            if (is_null($proposal->getCriteria()->getSunMarginDuration())) {
                $proposal->getCriteria()->setSunMarginDuration($this->params['defaultMarginTime']);
            }
            $endDate = clone $proposal->getCriteria()->getFromDate();
            $endDate->add(new \DateInterval('P' . $this->params['defaultRegularLifeTime'] . 'Y'));
            $proposal->getCriteria()->setToDate($endDate);
        }
        return $this->createProposal($proposal);
    }

    /**
     * Create "user-friendly" results from the matchings of a proposal
     *
     * @param Proposal $proposal    The proposal with its matchings
     * @return array                The array of results
     */
    private function createResults(Proposal $proposal)
    {
        $results = [];
        // we group the matchings by matching proposalId to merge potential driver and/or passenger candidates
        $matchings = [];
        // we search the matchings as an offer
        foreach ($proposal->getMatchingRequests() as $request) {
            $matchings[$request->getProposalRequest()->getId()]['request'] = $request;
        }
        // we search the matchings as a request
        foreach ($proposal->getMatchingOffers() as $offer) {
            $matchings[$offer->getProposalOffer()->getId()]['offer'] = $offer;
        }
        // we iterate through the matchings to create the results
        foreach ($matchings as $proposalId => $matching) {
            $result = new Result();

            /************/
            /*  REQUEST */
            /************/
            if (isset($matching['request'])) {
                // the carpooler can be passenger
                if (is_null($result->getFrequency())) {
                    $result->setFrequency($matching['request']->getCriteria()->getFrequency());
                }
                if (is_null($result->getFrequencyResult())) {
                    $result->setFrequencyResult($matching['request']->getProposalRequest()->getCriteria()->getFrequency());
                }
                if (is_null($result->getCarpooler())) {
                    $result->setCarpooler($matching['request']->getProposalRequest()->getUser());
                }
                if (is_null($result->getComment()) && !is_null($matching['request']->getProposalRequest()->getComment())) {
                    $result->setComment($matching['request']->getProposalRequest()->getComment());
                }
                $resultDriver = new ResultRole();
                // outward
                $outward = new ResultItem();
                $outward->setProposalId($proposalId);
                if ($matching['request']->getId() !== Matching::DEFAULT_ID) {
                    $outward->setMatchingId($matching['request']->getId());
                }
                if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    // the search/ad proposal is punctual
                    // we have to calculate the date and time of the carpool
                    // date :
                    // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                    // - if the matching proposal is regular, it's the date of the search/ad (as the matching proposal "matches", it means that the date is valid => the date is in the range of the regular matching proposal)
                    if ($matching['request']->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $outward->setDate($matching['request']->getProposalRequest()->getCriteria()->getFromDate());
                    } else {
                        $outward->setDate($proposal->getCriteria()->getFromDate());
                    }
                    // time
                    // the carpooler is passenger, the proposal owner is driver : we use his time if it's set
                    if ($proposal->getCriteria()->getFromTime()) {
                        $outward->setTime($proposal->getCriteria()->getFromTime());
                    } else {
                        // the time is not set, it must be the matching results of a search (and not an ad)
                        // we have to calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // we init the time to the one of the carpooler
                        if ($matching['request']->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                            // the carpooler proposal is punctual, we take the fromTime
                            $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getFromTime();
                        } else {
                            // the carpooler proposal is regular, we have to take the search/ad day's time
                            switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                                case 0: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSunTime();
                                    break;
                                }
                                case 1: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getMonTime();
                                    break;
                                }
                                case 2: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getTueTime();
                                    break;
                                }
                                case 3: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getWedTime();
                                    break;
                                }
                                case 4: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getThuTime();
                                    break;
                                }
                                case 5: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getFriTime();
                                    break;
                                }
                                case 6: {
                                    $fromTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSatTime();
                                    break;
                                }
                            }
                        }
                        // we search the pickup duration
                        $filters = $matching['request']->getFilters();
                        $pickupDuration = null;
                        foreach ($filters['route'] as $value) {
                            if ($value['candidate'] == 2 && $value['position'] == 0) {
                                $pickupDuration = (int)round($value['duration']);
                                break;
                            }
                        }
                        if ($pickupDuration) {
                            $fromTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $outward->setTime($fromTime);
                    }
                } else {
                    // the search or ad is regular => no date
                    // we have to find common days (if it's a search the common days should be the carpooler days)
                    // we check if pickup times have been calculated already
                    if (isset($matching['request']->getFilters()['pickup'])) {
                        // we have pickup times, it must be the matching results of an ad (and not a search)
                        // the carpooler is passenger, the proposal owner is driver : we use his time as it must be set
                        // we use the times even if we don't use them, maybe we'll need them in the future
                        // we set the global time for each day, we will erase it if we discover that all days have not the same time
                        // this way we are sure that if all days have the same time, the global time will be set and ok
                        if (isset($matching['request']->getFilters()['pickup']['monMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['monMaxPickupTime'])) {
                            $outward->setMonCheck(true);
                            $outward->setMonTime($proposal->getCriteria()->getMonTime());
                            $outward->setTime($proposal->getCriteria()->getMonTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['tueMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['tueMaxPickupTime'])) {
                            $outward->setTueCheck(true);
                            $outward->setTueTime($proposal->getCriteria()->getTueTime());
                            $outward->setTime($proposal->getCriteria()->getTueTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['wedMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['wedMaxPickupTime'])) {
                            $outward->setWedCheck(true);
                            $outward->setWedTime($proposal->getCriteria()->getWedTime());
                            $outward->setTime($proposal->getCriteria()->getWedTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['thuMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['thuMaxPickupTime'])) {
                            $outward->setThuCheck(true);
                            $outward->setThuTime($proposal->getCriteria()->getThuTime());
                            $outward->setTime($proposal->getCriteria()->getThuTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['friMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['friMaxPickupTime'])) {
                            $outward->setFriCheck(true);
                            $outward->setFriTime($proposal->getCriteria()->getFriTime());
                            $outward->setTime($proposal->getCriteria()->getFriTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['satMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['satMaxPickupTime'])) {
                            $outward->setSatCheck(true);
                            $outward->setSatTime($proposal->getCriteria()->getSatTime());
                            $outward->setTime($proposal->getCriteria()->getSatTime());
                        }
                        if (isset($matching['request']->getFilters()['pickup']['sunMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['sunMaxPickupTime'])) {
                            $outward->setSunCheck(true);
                            $outward->setSunTime($proposal->getCriteria()->getSunTime());
                            $outward->setTime($proposal->getCriteria()->getSunTime());
                        }
                    } else {
                        // no pick up times, it must be the matching results of a search (and not an ad)
                        // the days are the carpooler days
                        $outward->setMonCheck($matching['request']->getProposalRequest()->getCriteria()->isMonCheck());
                        $outward->setTueCheck($matching['request']->getProposalRequest()->getCriteria()->isTueCheck());
                        $outward->setWedCheck($matching['request']->getProposalRequest()->getCriteria()->isWedCheck());
                        $outward->setThuCheck($matching['request']->getProposalRequest()->getCriteria()->isThuCheck());
                        $outward->setFriCheck($matching['request']->getProposalRequest()->getCriteria()->isFriCheck());
                        $outward->setSatCheck($matching['request']->getProposalRequest()->getCriteria()->isSatCheck());
                        $outward->setSunCheck($matching['request']->getProposalRequest()->getCriteria()->isSunCheck());
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $filters = $matching['request']->getFilters();
                        $pickupDuration = null;
                        foreach ($filters['route'] as $value) {
                            if ($value['candidate'] == 2 && $value['position'] == 0) {
                                $pickupDuration = (int)round($value['duration']);
                                break;
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($matching['request']->getProposalRequest()->getCriteria()->isMonCheck()) {
                            $monTime = clone $matching['request']->getProposalRequest()->getCriteria()->getMonTime();
                            if ($pickupDuration) {
                                $monTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setMonTime($monTime);
                            $outward->setTime($monTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isTueCheck()) {
                            $tueTime = clone $matching['request']->getProposalRequest()->getCriteria()->getTueTime();
                            if ($pickupDuration) {
                                $tueTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setTueTime($tueTime);
                            $outward->setTime($tueTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isWedCheck()) {
                            $wedTime = clone $matching['request']->getProposalRequest()->getCriteria()->getWedTime();
                            if ($pickupDuration) {
                                $wedTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setWedTime($wedTime);
                            $outward->setTime($wedTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isThuCheck()) {
                            $thuTime = clone $matching['request']->getProposalRequest()->getCriteria()->getThuTime();
                            if ($pickupDuration) {
                                $thuTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setThuTime($thuTime);
                            $outward->setTime($thuTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isFriCheck()) {
                            $friTime = clone $matching['request']->getProposalRequest()->getCriteria()->getFriTime();
                            if ($pickupDuration) {
                                $friTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setFriTime($friTime);
                            $outward->setTime($friTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isSatCheck()) {
                            $satTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSatTime();
                            if ($pickupDuration) {
                                $satTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setSatTime($satTime);
                            $outward->setTime($satTime);
                        }
                        if ($matching['request']->getProposalRequest()->getCriteria()->isSunCheck()) {
                            $sunTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSunTime();
                            if ($pickupDuration) {
                                $sunTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setSunTime($sunTime);
                            $outward->setTime($sunTime);
                        }
                    }
                    $outward->setMultipleTimes();
                    if ($outward->hasMultipleTimes()) {
                        $outward->setTime(null);
                    }
                    // fromDate is the max between the search date and the fromDate of the matching proposal
                    $outward->setFromDate(max(
                        $matching['request']->getProposalRequest()->getCriteria()->getFromDate(),
                        $proposal->getCriteria()->getFromDate()
                    ));
                    $outward->setToDate($matching['request']->getProposalRequest()->getCriteria()->getToDate());
                }
                // waypoints of the outward
                $waypoints = [];
                $time = $outward->getTime() ? clone $outward->getTime() : null;
                // we will have to compute the number of steps fo reach candidate
                $steps = [
                    'requester' => 0,
                    'carpooler' => 0
                ];
                // first pass to get the maximum position fo each candidate
                foreach ($matching['request']->getFilters()['route'] as $key=>$waypoint) {
                    if ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['requester']) {
                        $steps['requester'] = (int)$waypoint['position'];
                    } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['carpooler']) {
                        $steps['carpooler'] = (int)$waypoint['position'];
                    }
                }
                // second pass to fill the waypoints array
                foreach ($matching['request']->getFilters()['route'] as $key=>$waypoint) {
                    $curTime = null;
                    if ($time) {
                        $curTime = clone $time;
                    }
                    if ($curTime) {
                        $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
                    }
                    $waypoints[$key] = [
                        'id' => $key,
                        'person' => $waypoint['candidate'] == 1 ? 'requester' : 'carpooler',
                        'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
                        'time' =>  $curTime,
                        'address' => $waypoint['address'],
                        'type' => $waypoint['position'] == '0' ? 'origin' :
                            (
                                ($waypoint['candidate'] == 1) ? ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
                                ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
                            )
                    ];
                    // origin and destination guess
                    if ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                        $outward->setOrigin($waypoint['address']);
                        $outward->setOriginPassenger($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['carpooler']) {
                        $outward->setDestination($waypoint['address']);
                        $outward->setDestinationPassenger($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                        $outward->setOriginDriver($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['requester']) {
                        $outward->setDestinationDriver($waypoint['address']);
                    }
                }
                $outward->setWaypoints($waypoints);
                
                // statistics
                $outward->setOriginalDistance($matching['request']->getFilters()['originalDistance']);
                $outward->setAcceptedDetourDistance($matching['request']->getFilters()['acceptedDetourDistance']);
                $outward->setNewDistance($matching['request']->getFilters()['newDistance']);
                $outward->setDetourDistance($matching['request']->getFilters()['detourDistance']);
                $outward->setDetourDistancePercent($matching['request']->getFilters()['detourDistancePercent']);
                $outward->setOriginalDuration($matching['request']->getFilters()['originalDuration']);
                $outward->setAcceptedDetourDuration($matching['request']->getFilters()['acceptedDetourDuration']);
                $outward->setNewDuration($matching['request']->getFilters()['newDuration']);
                $outward->setDetourDuration($matching['request']->getFilters()['detourDuration']);
                $outward->setDetourDurationPercent($matching['request']->getFilters()['detourDurationPercent']);
                $outward->setCommonDistance($matching['request']->getFilters()['commonDistance']);

                // price
                // if the requester price per km is set we use it
                if ($proposal->getCriteria()->getPriceKm()) {
                    $outward->setPriceKm($proposal->getCriteria()->getPriceKm());
                } else {
                    // otherwise we use the common price
                    $outward->setPriceKm($this->params['defaultPriceKm']);
                }
                // if the requester price is set we use it
                if ($proposal->getCriteria()->getPrice()) {
                    $outward->setOriginalPrice($proposal->getCriteria()->getPrice());
                } else {
                    // otherwise we use the common price
                    $outward->setOriginalPrice((string)((int)$matching['request']->getFilters()['originalDistance']*(float)$outward->getPriceKm()/1000));
                }
                // the computed price is the price to be paid by the passenger
                // it's ((common distance + detour distance) * price by km)
                $outward->setComputedPrice((string)(((int)$matching['request']->getFilters()['commonDistance']+(int)$matching['request']->getFilters()['detourDistance'])*(float)$outward->getPriceKm()/1000));
                $outward->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$outward->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                $resultDriver->setOutward($outward);
                
                // return trip, only for regular trip for now
                if ($matching['request']->getProposalRequest()->getProposalLinked() && $proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching['request']->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $proposalLinked = $matching['request']->getProposalRequest()->getProposalLinked();
                    $matchingRelated = $matching['request']->getMatchingRelated();
                    
                    // /!\ we only treat the return days /!\
                    $return = new ResultItem();
                    // we use the carpooler days as we don't have a matching here
                    $return->setMonCheck($proposalLinked->getCriteria()->isMonCheck());
                    $return->setTueCheck($proposalLinked->getCriteria()->isTueCheck());
                    $return->setWedCheck($proposalLinked->getCriteria()->isWedCheck());
                    $return->setThuCheck($proposalLinked->getCriteria()->isThuCheck());
                    $return->setFriCheck($proposalLinked->getCriteria()->isFriCheck());
                    $return->setSatCheck($proposalLinked->getCriteria()->isSatCheck());
                    $return->setSunCheck($proposalLinked->getCriteria()->isSunCheck());
                    $return->setFromDate($proposalLinked->getCriteria()->getFromDate());
                    $return->setToDate($proposalLinked->getCriteria()->getToDate());

                    if ($matchingRelated) {
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $filters = $matchingRelated->getFilters();
                        $pickupDuration = null;
                        foreach ($filters['route'] as $value) {
                            if ($value['candidate'] == 2 && $value['position'] == 0) {
                                $pickupDuration = (int)round($value['duration']);
                                break;
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($proposalLinked->getCriteria()->isMonCheck()) {
                            $monTime = clone $proposalLinked->getCriteria()->getMonTime();
                            if ($pickupDuration) {
                                $monTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setMonTime($monTime);
                            $return->setTime($monTime);
                        }
                        if ($proposalLinked->getCriteria()->isTueCheck()) {
                            $tueTime = clone $proposalLinked->getCriteria()->getTueTime();
                            if ($pickupDuration) {
                                $tueTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setTueTime($tueTime);
                            $return->setTime($tueTime);
                        }
                        if ($proposalLinked->getCriteria()->isWedCheck()) {
                            $wedTime = clone $proposalLinked->getCriteria()->getWedTime();
                            if ($pickupDuration) {
                                $wedTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setWedTime($wedTime);
                            $return->setTime($wedTime);
                        }
                        if ($proposalLinked->getCriteria()->isThuCheck()) {
                            $thuTime = clone $proposalLinked->getCriteria()->getThuTime();
                            if ($pickupDuration) {
                                $thuTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setThuTime($thuTime);
                            $return->setTime($thuTime);
                        }
                        if ($proposalLinked->getCriteria()->isFriCheck()) {
                            $friTime = clone $proposalLinked->getCriteria()->getFriTime();
                            if ($pickupDuration) {
                                $friTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setFriTime($friTime);
                            $return->setTime($friTime);
                        }
                        if ($proposalLinked->getCriteria()->isSatCheck()) {
                            $satTime = clone $proposalLinked->getCriteria()->getSatTime();
                            if ($pickupDuration) {
                                $satTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSatTime($satTime);
                            $return->setTime($satTime);
                        }
                        if ($proposalLinked->getCriteria()->isSunCheck()) {
                            $sunTime = clone $proposalLinked->getCriteria()->getSunTime();
                            if ($pickupDuration) {
                                $sunTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSunTime($sunTime);
                            $return->setTime($sunTime);
                        }
                        // fromDate is the max between the search date and the fromDate of the matching proposal
                        $return->setFromDate(max(
                            $matchingRelated->getProposalRequest()->getCriteria()->getFromDate(),
                            $proposal->getCriteria()->getFromDate()
                        ));
                        $return->setToDate($matchingRelated->getProposalRequest()->getCriteria()->getToDate());
                    
                        // waypoints of the return
                        $waypoints = [];
                        $time = $return->getTime() ? clone $return->getTime() : null;
                        // we will have to compute the number of steps for each candidate
                        $steps = [
                            'requester' => 0,
                            'carpooler' => 0
                        ];
                        // first pass to get the maximum position for each candidate
                        foreach ($matchingRelated->getFilters()['route'] as $key=>$waypoint) {
                            if ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['requester']) {
                                $steps['requester'] = (int)$waypoint['position'];
                            } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['carpooler']) {
                                $steps['carpooler'] = (int)$waypoint['position'];
                            }
                        }
                        // second pass to fill the waypoints array
                        foreach ($matchingRelated->getFilters()['route'] as $key=>$waypoint) {
                            $curTime = null;
                            if ($time) {
                                $curTime = clone $time;
                            }
                            if ($curTime) {
                                $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
                            }
                            $waypoints[$key] = [
                                'id' => $key,
                                'person' => $waypoint['candidate'] == 1 ? 'requester' : 'carpooler',
                                'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
                                'time' =>  $curTime,
                                'address' => $waypoint['address'],
                                'type' => $waypoint['position'] == '0' ? 'origin' :
                                    (
                                        ($waypoint['candidate'] == 1) ? ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
                                        ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
                                    )
                            ];
                            // origin and destination guess
                            if ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                                $return->setOrigin($waypoint['address']);
                                $return->setOriginPassenger($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['carpooler']) {
                                $return->setDestination($waypoint['address']);
                                $return->setDestinationPassenger($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                                $return->setOriginDriver($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['requester']) {
                                $return->setDestinationDriver($waypoint['address']);
                            }
                        }
                        $return->setWaypoints($waypoints);
                        
                        // statistics
                        if ($matchingRelated->getFilters()['originalDistance']) {
                            $return->setOriginalDistance($matchingRelated->getFilters()['originalDistance']);
                        }
                        if ($matchingRelated->getFilters()['acceptedDetourDistance']) {
                            $return->setAcceptedDetourDistance($matchingRelated->getFilters()['acceptedDetourDistance']);
                        }
                        if ($matchingRelated->getFilters()['newDistance']) {
                            $return->setNewDistance($matchingRelated->getFilters()['newDistance']);
                        }
                        if ($matchingRelated->getFilters()['detourDistance']) {
                            $return->setDetourDistance($matchingRelated->getFilters()['detourDistance']);
                        }
                        if ($matchingRelated->getFilters()['detourDistancePercent']) {
                            $return->setDetourDistancePercent($matchingRelated->getFilters()['detourDistancePercent']);
                        }
                        if ($matchingRelated->getFilters()['originalDuration']) {
                            $return->setOriginalDuration($matchingRelated->getFilters()['originalDuration']);
                        }
                        if ($matchingRelated->getFilters()['acceptedDetourDuration']) {
                            $return->setAcceptedDetourDuration($matchingRelated->getFilters()['acceptedDetourDuration']);
                        }
                        if ($matchingRelated->getFilters()['newDuration']) {
                            $return->setNewDuration($matchingRelated->getFilters()['newDuration']);
                        }
                        if ($matchingRelated->getFilters()['detourDuration']) {
                            $return->setDetourDuration($matchingRelated->getFilters()['detourDuration']);
                        }
                        if ($matchingRelated->getFilters()['detourDurationPercent']) {
                            $return->setDetourDurationPercent($matchingRelated->getFilters()['detourDurationPercent']);
                        }
                        if ($matchingRelated->getFilters()['commonDistance']) {
                            $return->setCommonDistance($matchingRelated->getFilters()['commonDistance']);
                        }

                        // price
                        // if the requester price per km is set we use it
                        if ($proposal->getCriteria()->getPriceKm()) {
                            $return->setPriceKm($proposal->getCriteria()->getPriceKm());
                        } else {
                            // otherwise we use the common price
                            $return->setPriceKm($this->params['defaultPriceKm']);
                        }
                        // if the requester price is set we use it
                        if ($proposal->getCriteria()->getPrice()) {
                            $return->setOriginalPrice($proposal->getCriteria()->getPrice());
                        } else {
                            // otherwise we use the common price
                            $return->setOriginalPrice((string)((int)$matchingRelated->getFilters()['originalDistance']*(float)$return->getPriceKm()/1000));
                        }
                        // the computed price is the price to be paid by the passenger
                        // it's ((common distance + detour distance) * price by km)
                        $return->setComputedPrice((string)(((int)$matchingRelated->getFilters()['commonDistance']+(int)$matchingRelated->getFilters()['detourDistance'])*(float)$return->getPriceKm()/1000));
                        $return->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$return->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                    }
                    $return->setMultipleTimes();
                    if ($return->hasMultipleTimes()) {
                        $return->setTime(null);
                    }

                    $resultDriver->setReturn($return);
                }

                // seats
                $resultDriver->setSeats($proposal->getCriteria()->getSeats() ? $proposal->getCriteria()->getSeats() : 1);
                $result->setResultDriver($resultDriver);
            }

            /************/
            /*  OFFER   */
            /************/
            if (isset($matching['offer'])) {
                // the carpooler can be driver
                if (is_null($result->getFrequency())) {
                    $result->setFrequency($matching['offer']->getCriteria()->getFrequency());
                }
                if (is_null($result->getFrequencyResult())) {
                    $result->setFrequencyResult($matching['offer']->getProposalOffer()->getCriteria()->getFrequency());
                }
                if (is_null($result->getCarpooler())) {
                    $result->setCarpooler($matching['offer']->getProposalOffer()->getUser());
                }
                if (is_null($result->getComment()) && !is_null($matching['offer']->getProposalOffer()->getComment())) {
                    $result->setComment($matching['offer']->getProposalOffer()->getComment());
                }
                $resultPassenger = new ResultRole();

                // outward
                $outward = new ResultItem();
                $outward->setProposalId($proposalId);
                if ($matching['offer']->getId() !== Matching::DEFAULT_ID) {
                    $outward->setMatchingId($matching['offer']->getId());
                }
                $driverFromTime = null;
                if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    // the search/ad proposal is punctual
                    // we have to calculate the date and time of the carpool
                    // date :
                    // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                    // - if the matching proposal is regular, it's the date of the search/ad (as the matching proposal "matches", it means that the date is valid => the date is in the range of the regular matching proposal)
                    if ($matching['offer']->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $outward->setDate($matching['offer']->getProposalOffer()->getCriteria()->getFromDate());
                    } else {
                        $outward->setDate($proposal->getCriteria()->getFromDate());
                    }
                    // time
                    // the carpooler is driver, the proposal owner is passenger
                    // we have to calculate the starting time using the carpooler time
                    // we init the time to the one of the carpooler
                    if ($matching['offer']->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        // the carpooler proposal is punctual, we take the fromTime
                        $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFromTime();
                    } else {
                        // the carpooler proposal is regular, we have to take the search/ad day's time
                        switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                            case 0: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSunTime();
                                break;
                            }
                            case 1: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getMonTime();
                                break;
                            }
                            case 2: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getTueTime();
                                break;
                            }
                            case 3: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getWedTime();
                                break;
                            }
                            case 4: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getThuTime();
                                break;
                            }
                            case 5: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFriTime();
                                break;
                            }
                            case 6: {
                                $fromTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSatTime();
                                break;
                            }
                        }
                    }
                    // we search the pickup duration
                    $filters = $matching['offer']->getFilters();
                    $pickupDuration = null;
                    foreach ($filters['route'] as $value) {
                        if ($value['candidate'] == 2 && $value['position'] == 0) {
                            $pickupDuration = (int)round($value['duration']);
                            break;
                        }
                    }
                    $driverFromTime = clone $fromTime;
                    if ($pickupDuration) {
                        $fromTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                    }
                    $outward->setTime($fromTime);
                } else {
                    // the search or ad is regular => no date
                    // we have to find common days (if it's a search the common days should be the carpooler days)
                    // we check if pickup times have been calculated already
                    // we set the global time for each day, we will erase it if we discover that all days have not the same time
                    // this way we are sure that if all days have the same time, the global time will be set and ok
                    if (isset($matching['offer']->getFilters()['pickup'])) {
                        // we have pickup times, it must be the matching results of an ad (and not a search)
                        // the carpooler is driver, the proposal owner is passenger : we use his time as it must be set
                        if (isset($matching['offer']->getFilters()['pickup']['monMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['monMaxPickupTime'])) {
                            $outward->setMonCheck(true);
                            $outward->setMonTime($proposal->getCriteria()->getMonTime());
                            $outward->setTime($proposal->getCriteria()->getMonTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['tueMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['tueMaxPickupTime'])) {
                            $outward->setTueCheck(true);
                            $outward->setTueTime($proposal->getCriteria()->getTueTime());
                            $outward->setTime($proposal->getCriteria()->getTueTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['wedMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['wedMaxPickupTime'])) {
                            $outward->setWedCheck(true);
                            $outward->setWedTime($proposal->getCriteria()->getWedTime());
                            $outward->setTime($proposal->getCriteria()->getWedTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['thuMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['thuMaxPickupTime'])) {
                            $outward->setThuCheck(true);
                            $outward->setThuTime($proposal->getCriteria()->getThuTime());
                            $outward->setTime($proposal->getCriteria()->getThuTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['friMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['friMaxPickupTime'])) {
                            $outward->setFriCheck(true);
                            $outward->setFriTime($proposal->getCriteria()->getFriTime());
                            $outward->setTime($proposal->getCriteria()->getFriTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['satMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['satMaxPickupTime'])) {
                            $outward->setSatCheck(true);
                            $outward->setSatTime($proposal->getCriteria()->getSatTime());
                            $outward->setTime($proposal->getCriteria()->getSatTime());
                        }
                        if (isset($matching['offer']->getFilters()['pickup']['sunMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['sunMaxPickupTime'])) {
                            $outward->setSunCheck(true);
                            $outward->setSunTime($proposal->getCriteria()->getSunTime());
                            $outward->setTime($proposal->getCriteria()->getSunTime());
                        }
                        $driverFromTime = $outward->getTime();
                    } else {
                        // no pick up times, it must be the matching results of a search (and not an ad)
                        // the days are the carpooler days
                        $outward->setMonCheck($matching['offer']->getProposalOffer()->getCriteria()->isMonCheck());
                        $outward->setTueCheck($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck());
                        $outward->setWedCheck($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck());
                        $outward->setThuCheck($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck());
                        $outward->setFriCheck($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck());
                        $outward->setSatCheck($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck());
                        $outward->setSunCheck($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck());
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $filters = $matching['offer']->getFilters();
                        $pickupDuration = null;
                        foreach ($filters['route'] as $value) {
                            if ($value['candidate'] == 2 && $value['position'] == 0) {
                                $pickupDuration = (int)round($value['duration']);
                                break;
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isMonCheck()) {
                            $monTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getMonTime();
                            $driverFromTime = clone $monTime;
                            if ($pickupDuration) {
                                $monTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setMonTime($monTime);
                            $outward->setTime($monTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck()) {
                            $tueTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getTueTime();
                            $driverFromTime = clone $tueTime;
                            if ($pickupDuration) {
                                $tueTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setTueTime($tueTime);
                            $outward->setTime($tueTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck()) {
                            $wedTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getWedTime();
                            $driverFromTime = clone $wedTime;
                            if ($pickupDuration) {
                                $wedTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setWedTime($wedTime);
                            $outward->setTime($wedTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck()) {
                            $thuTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getThuTime();
                            $driverFromTime = clone $thuTime;
                            if ($pickupDuration) {
                                $thuTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setThuTime($thuTime);
                            $outward->setTime($thuTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck()) {
                            $friTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFriTime();
                            $driverFromTime = clone $friTime;
                            if ($pickupDuration) {
                                $friTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setFriTime($friTime);
                            $outward->setTime($friTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck()) {
                            $satTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSatTime();
                            $driverFromTime = clone $satTime;
                            if ($pickupDuration) {
                                $satTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setSatTime($satTime);
                            $outward->setTime($satTime);
                        }
                        if ($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck()) {
                            $sunTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSunTime();
                            $driverFromTime = clone $sunTime;
                            if ($pickupDuration) {
                                $sunTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $outward->setSunTime($sunTime);
                            $outward->setTime($sunTime);
                        }
                    }
                    $outward->setMultipleTimes();
                    if ($outward->hasMultipleTimes()) {
                        $outward->setTime(null);
                        $driverFromTime = null;
                    }
                    // fromDate is the max between the search date and the fromDate of the matching proposal
                    $outward->setFromDate(max(
                        $matching['offer']->getProposalOffer()->getCriteria()->getFromDate(),
                        $proposal->getCriteria()->getFromDate()
                    ));
                    $outward->setToDate($matching['offer']->getProposalOffer()->getCriteria()->getToDate());
                }
                // waypoints of the outward
                $waypoints = [];
                $time = $driverFromTime ? clone $driverFromTime : null;
                // we will have to compute the number of steps fo reach candidate
                $steps = [
                    'requester' => 0,
                    'carpooler' => 0
                ];
                // first pass to get the maximum position fo each candidate
                foreach ($matching['offer']->getFilters()['route'] as $key=>$waypoint) {
                    if ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['requester']) {
                        $steps['requester'] = (int)$waypoint['position'];
                    } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['carpooler']) {
                        $steps['carpooler'] = (int)$waypoint['position'];
                    }
                }
                // second pass to fill the waypoints array
                foreach ($matching['offer']->getFilters()['route'] as $key=>$waypoint) {
                    $curTime = null;
                    if ($time) {
                        $curTime = clone $time;
                    }
                    if ($curTime) {
                        $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
                    }
                    $waypoints[$key] = [
                        'id' => $key,
                        'person' => $waypoint['candidate'] == 2 ? 'requester' : 'carpooler',
                        'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
                        'time' =>  $curTime,
                        'address' => $waypoint['address'],
                        'type' => $waypoint['position'] == '0' ? 'origin' :
                            (
                                ($waypoint['candidate'] == 2) ? ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
                                ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
                            )
                    ];
                    // origin and destination guess
                    if ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                        $outward->setOrigin($waypoint['address']);
                        $outward->setOriginDriver($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['carpooler']) {
                        $outward->setDestination($waypoint['address']);
                        $outward->setDestinationDriver($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                        $outward->setOriginPassenger($waypoint['address']);
                    } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['requester']) {
                        $outward->setDestinationPassenger($waypoint['address']);
                    }
                }
                $outward->setWaypoints($waypoints);
                
                // statistics
                $outward->setOriginalDistance($matching['offer']->getFilters()['originalDistance']);
                $outward->setAcceptedDetourDistance($matching['offer']->getFilters()['acceptedDetourDistance']);
                $outward->setNewDistance($matching['offer']->getFilters()['newDistance']);
                $outward->setDetourDistance($matching['offer']->getFilters()['detourDistance']);
                $outward->setDetourDistancePercent($matching['offer']->getFilters()['detourDistancePercent']);
                $outward->setOriginalDuration($matching['offer']->getFilters()['originalDuration']);
                $outward->setAcceptedDetourDuration($matching['offer']->getFilters()['acceptedDetourDuration']);
                $outward->setNewDuration($matching['offer']->getFilters()['newDuration']);
                $outward->setDetourDuration($matching['offer']->getFilters()['detourDuration']);
                $outward->setDetourDurationPercent($matching['offer']->getFilters()['detourDurationPercent']);
                $outward->setCommonDistance($matching['offer']->getFilters()['commonDistance']);

                // price
                // if the carpooler price per km is set we use it
                if ($matching['offer']->getProposalOffer()->getCriteria()->getPriceKm()) {
                    $outward->setPriceKm($matching['offer']->getProposalOffer()->getCriteria()->getPriceKm());
                } else {
                    // otherwise we use the common price
                    $outward->setPriceKm($this->params['defaultPriceKm']);
                }
                // if the carpooler price is set we use it
                if ($matching['offer']->getProposalOffer()->getCriteria()->getPrice()) {
                    $outward->setOriginalPrice($matching['offer']->getProposalOffer()->getCriteria()->getPrice());
                } else {
                    // otherwise we use the common price
                    $outward->setOriginalPrice((string)((int)$matching['offer']->getFilters()['originalDistance']*(float)$outward->getPriceKm()/1000));
                }
                // the computed price is the price to be paid by the passenger
                // it's ((common distance + detour distance) * price by km)
                $outward->setComputedPrice((string)(((int)$matching['offer']->getFilters()['commonDistance']+(int)$matching['offer']->getFilters()['detourDistance'])*(float)$outward->getPriceKm()/1000));
                $outward->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$outward->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                $resultPassenger->setOutward($outward);

                // return trip, only for regular trip for now
                if ($matching['offer']->getProposalOffer()->getProposalLinked() && $proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching['offer']->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $proposalLinked = $matching['offer']->getProposalOffer()->getProposalLinked();
                    $matchingRelated = $matching['offer']->getMatchingRelated();

                    // /!\ we only treat the return days /!\
                    $return = new ResultItem();
                    // we use the carpooler days as we don't have a matching here
                    $return->setMonCheck($proposalLinked->getCriteria()->isMonCheck());
                    $return->setTueCheck($proposalLinked->getCriteria()->isTueCheck());
                    $return->setWedCheck($proposalLinked->getCriteria()->isWedCheck());
                    $return->setThuCheck($proposalLinked->getCriteria()->isThuCheck());
                    $return->setFriCheck($proposalLinked->getCriteria()->isFriCheck());
                    $return->setSatCheck($proposalLinked->getCriteria()->isSatCheck());
                    $return->setSunCheck($proposalLinked->getCriteria()->isSunCheck());
                    $return->setFromDate($proposalLinked->getCriteria()->getFromDate());
                    $return->setToDate($proposalLinked->getCriteria()->getToDate());
                    
                    if ($matchingRelated) {
                        // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                        // even if we don't use them, maybe we'll need them in the future
                        $filters = $matchingRelated->getFilters();
                        $pickupDuration = null;
                        foreach ($filters['route'] as $value) {
                            if ($value['candidate'] == 2 && $value['position'] == 0) {
                                $pickupDuration = (int)round($value['duration']);
                                break;
                            }
                        }
                        // we init the time to the one of the carpooler
                        if ($proposalLinked->getCriteria()->isMonCheck()) {
                            $monTime = clone $proposalLinked->getCriteria()->getMonTime();
                            $driverFromTime = clone $monTime;
                            if ($pickupDuration) {
                                $monTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setMonTime($monTime);
                            $return->setTime($monTime);
                        }
                        if ($proposalLinked->getCriteria()->isTueCheck()) {
                            $tueTime = clone $proposalLinked->getCriteria()->getTueTime();
                            $driverFromTime = clone $tueTime;
                            if ($pickupDuration) {
                                $tueTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setTueTime($tueTime);
                            $return->setTime($tueTime);
                        }
                        if ($proposalLinked->getCriteria()->isWedCheck()) {
                            $wedTime = clone $proposalLinked->getCriteria()->getWedTime();
                            $driverFromTime = clone $wedTime;
                            if ($pickupDuration) {
                                $wedTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setWedTime($wedTime);
                            $return->setTime($wedTime);
                        }
                        if ($proposalLinked->getCriteria()->isThuCheck()) {
                            $thuTime = clone $proposalLinked->getCriteria()->getThuTime();
                            $driverFromTime = clone $thuTime;
                            if ($pickupDuration) {
                                $thuTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setThuTime($thuTime);
                            $return->setTime($thuTime);
                        }
                        if ($proposalLinked->getCriteria()->isFriCheck()) {
                            $friTime = clone $proposalLinked->getCriteria()->getFriTime();
                            $driverFromTime = clone $friTime;
                            if ($pickupDuration) {
                                $friTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setFriTime($friTime);
                            $return->setTime($friTime);
                        }
                        if ($proposalLinked->getCriteria()->isSatCheck()) {
                            $satTime = clone $proposalLinked->getCriteria()->getSatTime();
                            $driverFromTime = clone $satTime;
                            if ($pickupDuration) {
                                $satTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSatTime($satTime);
                            $return->setTime($satTime);
                        }
                        if ($proposalLinked->getCriteria()->isSunCheck()) {
                            $sunTime = clone $proposalLinked->getCriteria()->getSunTime();
                            $driverFromTime = clone $sunTime;
                            if ($pickupDuration) {
                                $sunTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSunTime($sunTime);
                            $return->setTime($sunTime);
                        }
                        // fromDate is the max between the search date and the fromDate of the matching proposal
                        $return->setFromDate(max(
                            $matchingRelated->getProposalOffer()->getCriteria()->getFromDate(),
                            $proposal->getCriteria()->getFromDate()
                        ));
                        $return->setToDate($matchingRelated->getProposalOffer()->getCriteria()->getToDate());
                        
                        // waypoints of the return
                        $waypoints = [];
                        $time = $driverFromTime ? clone $driverFromTime : null;
                        // we will have to compute the number of steps for each candidate
                        $steps = [
                            'requester' => 0,
                            'carpooler' => 0
                        ];
                        // first pass to get the maximum position for each candidate
                        foreach ($matchingRelated->getFilters()['route'] as $key=>$waypoint) {
                            if ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['requester']) {
                                $steps['requester'] = (int)$waypoint['position'];
                            } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['carpooler']) {
                                $steps['carpooler'] = (int)$waypoint['position'];
                            }
                        }
                        // second pass to fill the waypoints array
                        foreach ($matchingRelated->getFilters()['route'] as $key=>$waypoint) {
                            $curTime = null;
                            if ($time) {
                                $curTime = clone $time;
                            }
                            if ($curTime) {
                                $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
                            }
                            $waypoints[$key] = [
                                'id' => $key,
                                'person' => $waypoint['candidate'] == 2 ? 'requester' : 'carpooler',
                                'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
                                'time' =>  $curTime,
                                'address' => $waypoint['address'],
                                'type' => $waypoint['position'] == '0' ? 'origin' :
                                    (
                                        ($waypoint['candidate'] == 2) ? ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step') :
                                        ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step')
                                    )
                            ];
                            // origin and destination guess
                            if ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                                $return->setOrigin($waypoint['address']);
                                $return->setOriginDriver($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['carpooler']) {
                                $return->setDestination($waypoint['address']);
                                $return->setDestinationDriver($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                                $return->setOriginPassenger($waypoint['address']);
                            } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['requester']) {
                                $return->setDestinationPassenger($waypoint['address']);
                            }
                        }
                        $return->setWaypoints($waypoints);
                        
                        // statistics
                        if ($matchingRelated->getFilters()['originalDistance']) {
                            $return->setOriginalDistance($matchingRelated->getFilters()['originalDistance']);
                        }
                        if ($matchingRelated->getFilters()['acceptedDetourDistance']) {
                            $return->setAcceptedDetourDistance($matchingRelated->getFilters()['acceptedDetourDistance']);
                        }
                        if ($matchingRelated->getFilters()['newDistance']) {
                            $return->setNewDistance($matchingRelated->getFilters()['newDistance']);
                        }
                        if ($matchingRelated->getFilters()['detourDistance']) {
                            $return->setDetourDistance($matchingRelated->getFilters()['detourDistance']);
                        }
                        if ($matchingRelated->getFilters()['detourDistancePercent']) {
                            $return->setDetourDistancePercent($matchingRelated->getFilters()['detourDistancePercent']);
                        }
                        if ($matchingRelated->getFilters()['originalDuration']) {
                            $return->setOriginalDuration($matchingRelated->getFilters()['originalDuration']);
                        }
                        if ($matchingRelated->getFilters()['acceptedDetourDuration']) {
                            $return->setAcceptedDetourDuration($matchingRelated->getFilters()['acceptedDetourDuration']);
                        }
                        if ($matchingRelated->getFilters()['newDuration']) {
                            $return->setNewDuration($matchingRelated->getFilters()['newDuration']);
                        }
                        if ($matchingRelated->getFilters()['detourDuration']) {
                            $return->setDetourDuration($matchingRelated->getFilters()['detourDuration']);
                        }
                        if ($matchingRelated->getFilters()['detourDurationPercent']) {
                            $return->setDetourDurationPercent($matchingRelated->getFilters()['detourDurationPercent']);
                        }
                        if ($matchingRelated->getFilters()['commonDistance']) {
                            $return->setCommonDistance($matchingRelated->getFilters()['commonDistance']);
                        }

                        // price
                        // if the carpooler price per km is set we use it
                        if ($matchingRelated->getProposalOffer()->getCriteria()->getPriceKm()) {
                            $return->setPriceKm($matchingRelated->getProposalOffer()->getCriteria()->getPriceKm());
                        } else {
                            // otherwise we use the common price
                            $return->setPriceKm($this->params['defaultPriceKm']);
                        }
                        // if the carpooler price is set we use it
                        if ($matchingRelated->getProposalOffer()->getCriteria()->getPrice()) {
                            $return->setOriginalPrice($matchingRelated->getProposalOffer()->getCriteria()->getPrice());
                        } else {
                            // otherwise we use the common price
                            $return->setOriginalPrice((string)((int)$matchingRelated->getFilters()['originalDistance']*(float)$return->getPriceKm()/1000));
                        }
                        // the computed price is the price to be paid by the passenger
                        // it's ((common distance + detour distance) * price by km)
                        $return->setComputedPrice((string)(((int)$matchingRelated->getFilters()['commonDistance']+(int)$matchingRelated->getFilters()['detourDistance'])*(float)$return->getPriceKm()/1000));
                        $return->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$return->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                    }
                    $return->setMultipleTimes();
                    if ($return->hasMultipleTimes()) {
                        $return->setTime(null);
                    }
                    
                    $resultPassenger->setReturn($return);
                }

                // seats
                $resultPassenger->setSeats($matching['offer']->getProposalOffer()->getCriteria()->getSeats() ? $matching['offer']->getProposalOffer()->getCriteria()->getSeats() : 1);
                $result->setResultPassenger($resultPassenger);
            }

            /**********************************************************************
             * global origin / destination / date / time / seats / price / return *
             **********************************************************************/
            
            // the following are used to display the summarized information about the result

            // origin / destination
            // we display the origin and destination of the passenger for his outward trip
            // if the carpooler can be driver and passenger, we choose to consider him as driver as he's the first to publish
            // we also set the originFirst and destinationLast to indicate if the driver origin / destination are different than the passenger ones

            // we first get the origin and destination of the requester
            $requesterOrigin = null;
            $requesterDestination = null;
            foreach ($proposal->getWaypoints() as $waypoint) {
                if ($waypoint->getPosition() == 0) {
                    $requesterOrigin = $waypoint->getAddress();
                }
                if ($waypoint->isDestination()) {
                    $requesterDestination = $waypoint->getAddress();
                }
            }
            if ($result->getResultDriver() && !$result->getResultPassenger()) {
                // the carpooler is passenger only, we use his origin and destination
                $result->setOrigin($result->getResultDriver()->getOutward()->getOrigin());
                $result->setDestination($result->getResultDriver()->getOutward()->getDestination());
                // we check if his origin and destination are first and last of the whole journey
                // we use the gps coordinates
                $result->setOriginFirst(false);
                if ($result->getOrigin()->getLatitude() == $requesterOrigin->getLatitude() && $result->getOrigin()->getLongitude() == $requesterOrigin->getLongitude()) {
                    $result->setOriginFirst(true);
                }
                $result->setDestinationLast(false);
                if ($result->getDestination()->getLatitude() == $requesterDestination->getLatitude() && $result->getDestination()->getLongitude() == $requesterDestination->getLongitude()) {
                    $result->setDestinationLast(true);
                }
                // driver and passenger origin/destination
                $result->setOriginDriver($result->getResultDriver()->getOutward()->getOriginDriver());
                $result->setDestinationDriver($result->getResultDriver()->getOutward()->getDestinationDriver());
                $result->setOriginPassenger($result->getResultDriver()->getOutward()->getOriginPassenger());
                $result->setDestinationPassenger($result->getResultDriver()->getOutward()->getDestinationPassenger());
            } else {
                // the carpooler can be driver, we use the requester origin and destination
                $result->setOrigin($requesterOrigin);
                $result->setDestination($requesterDestination);
                // we check if his origin and destination are first and last of the whole journey
                // we use the gps coordinates
                $result->setOriginFirst(false);
                if ($result->getOrigin()->getLatitude() == $result->getResultPassenger()->getOutward()->getOrigin()->getLatitude() && $result->getOrigin()->getLongitude() == $result->getResultPassenger()->getOutward()->getOrigin()->getLongitude()) {
                    $result->setOriginFirst(true);
                }
                $result->setDestinationLast(false);
                if ($result->getDestination()->getLatitude() == $result->getResultPassenger()->getOutward()->getDestination()->getLatitude() && $result->getDestination()->getLongitude() == $result->getResultPassenger()->getOutward()->getDestination()->getLongitude()) {
                    $result->setDestinationLast(true);
                }
                // driver and passenger origin/destination
                $result->setOriginDriver($result->getResultPassenger()->getOutward()->getOriginDriver());
                $result->setDestinationDriver($result->getResultPassenger()->getOutward()->getDestinationDriver());
                $result->setOriginPassenger($result->getResultPassenger()->getOutward()->getOriginPassenger());
                $result->setDestinationPassenger($result->getResultPassenger()->getOutward()->getDestinationPassenger());
            }

            // date / time / seats / price
            // if the request is regular, there is no date, but we keep a start date
            // otherwise we display the date of the matching proposal computed before depending on if the carpooler can be driver and/or passenger
            if ($result->getResultDriver() && !$result->getResultPassenger()) {
                // the carpooler is passenger only
                if ($result->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $result->setDate($result->getResultDriver()->getOutward()->getDate());
                    $result->setTime($result->getResultDriver()->getOutward()->getTime());
                } else {
                    $result->setStartDate($result->getResultDriver()->getOutward()->getFromDate());
                    $result->setToDate($result->getResultDriver()->getOutward()->getToDate());
                }
                $result->setPrice($result->getResultDriver()->getOutward()->getComputedPrice());
                $result->setRoundedPrice($result->getResultDriver()->getOutward()->getComputedRoundedPrice());
                $result->setSeats($result->getResultDriver()->getSeats());
            } else {
                // the carpooler is driver or passenger
                if ($result->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $result->setDate($result->getResultPassenger()->getOutward()->getDate());
                    $result->setTime($result->getResultPassenger()->getOutward()->getTime());
                } else {
                    $result->setStartDate($result->getResultPassenger()->getOutward()->getFromDate());
                    $result->setToDate($result->getResultPassenger()->getOutward()->getToDate());
                }
                $result->setPrice($result->getResultPassenger()->getOutward()->getComputedPrice());
                $result->setRoundedPrice($result->getResultPassenger()->getOutward()->getComputedRoundedPrice());
                $result->setSeats($result->getResultPassenger()->getSeats());
            }
            // regular days and times
            if ($result->getFrequencyResult() == Criteria::FREQUENCY_REGULAR) {
                if ($result->getResultDriver() && !$result->getResultPassenger()) {
                    // the carpooler is passenger only
                    $result->setMonCheck($result->getResultDriver()->getOutward()->isMonCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isMonCheck()));
                    $result->setTueCheck($result->getResultDriver()->getOutward()->isTueCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isTueCheck()));
                    $result->setWedCheck($result->getResultDriver()->getOutward()->isWedCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isWedCheck()));
                    $result->setThuCheck($result->getResultDriver()->getOutward()->isThuCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isThuCheck()));
                    $result->setFriCheck($result->getResultDriver()->getOutward()->isFriCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isFriCheck()));
                    $result->setSatCheck($result->getResultDriver()->getOutward()->isSatCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isSatCheck()));
                    $result->setSunCheck($result->getResultDriver()->getOutward()->isSunCheck() || ($result->getResultDriver()->getReturn() && $result->getResultDriver()->getReturn()->isSunCheck()));
                    if (!$result->getResultDriver()->getOutward()->hasMultipleTimes()) {
                        if ($result->getResultDriver()->getOutward()->getMonTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getMonTime());
                        } elseif ($result->getResultDriver()->getOutward()->getTueTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getTueTime());
                        } elseif ($result->getResultDriver()->getOutward()->getWedTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getWedTime());
                        } elseif ($result->getResultDriver()->getOutward()->getThuTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getThuTime());
                        } elseif ($result->getResultDriver()->getOutward()->getFriTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getFriTime());
                        } elseif ($result->getResultDriver()->getOutward()->getSatTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getSatTime());
                        } elseif ($result->getResultDriver()->getOutward()->getSunTime()) {
                            $result->setOutwardTime($result->getResultDriver()->getOutward()->getSunTime());
                        }
                    }
                    if ($result->getResultDriver()->getReturn() && !$result->getResultDriver()->getReturn()->hasMultipleTimes()) {
                        if ($result->getResultDriver()->getReturn()->getMonTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getMonTime());
                        } elseif ($result->getResultDriver()->getReturn()->getTueTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getTueTime());
                        } elseif ($result->getResultDriver()->getReturn()->getWedTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getWedTime());
                        } elseif ($result->getResultDriver()->getReturn()->getThuTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getThuTime());
                        } elseif ($result->getResultDriver()->getReturn()->getFriTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getFriTime());
                        } elseif ($result->getResultDriver()->getReturn()->getSatTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getSatTime());
                        } elseif ($result->getResultDriver()->getReturn()->getSunTime()) {
                            $result->setReturnTime($result->getResultDriver()->getReturn()->getSunTime());
                        }
                    }
                } else {
                    // the carpooler is driver or passenger
                    $result->setMonCheck($result->getResultPassenger()->getOutward()->isMonCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isMonCheck()));
                    $result->setTueCheck($result->getResultPassenger()->getOutward()->isTueCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isTueCheck()));
                    $result->setWedCheck($result->getResultPassenger()->getOutward()->isWedCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isWedCheck()));
                    $result->setThuCheck($result->getResultPassenger()->getOutward()->isThuCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isThuCheck()));
                    $result->setFriCheck($result->getResultPassenger()->getOutward()->isFriCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isFriCheck()));
                    $result->setSatCheck($result->getResultPassenger()->getOutward()->isSatCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isSatCheck()));
                    $result->setSunCheck($result->getResultPassenger()->getOutward()->isSunCheck() || ($result->getResultPassenger()->getReturn() && $result->getResultPassenger()->getReturn()->isSunCheck()));
                    if (!$result->getResultPassenger()->getOutward()->hasMultipleTimes()) {
                        if ($result->getResultPassenger()->getOutward()->getMonTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getMonTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getTueTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getTueTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getWedTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getWedTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getThuTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getThuTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getFriTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getFriTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getSatTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getSatTime());
                        } elseif ($result->getResultPassenger()->getOutward()->getSunTime()) {
                            $result->setOutwardTime($result->getResultPassenger()->getOutward()->getSunTime());
                        }
                    }
                    if ($result->getResultPassenger()->getReturn() && !$result->getResultPassenger()->getReturn()->hasMultipleTimes()) {
                        if ($result->getResultPassenger()->getReturn()->getMonTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getMonTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getTueTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getTueTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getWedTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getWedTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getThuTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getThuTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getFriTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getFriTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getSatTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getSatTime());
                        } elseif ($result->getResultPassenger()->getReturn()->getSunTime()) {
                            $result->setReturnTime($result->getResultPassenger()->getReturn()->getSunTime());
                        }
                    }
                }
            }

            // return trip ?
            $result->setReturn(false);
            if ($result->getResultDriver() && !$result->getResultPassenger()) {
                // the carpooler is passenger only
                if (!is_null($result->getResultDriver()->getReturn())) {
                    $result->setReturn(true);
                }
            } else {
                // the carpooler is driver or passenger
                if (!is_null($result->getResultPassenger()->getReturn())) {
                    $result->setReturn(true);
                }
            }

            $results[] = $result;
        }
        return $results;
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
