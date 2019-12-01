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
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\AskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedUrgentEvent;
use App\Carpool\Event\MatchingNewEvent;
use App\Carpool\Event\PassengerAskAdDeletedEvent;
use App\Carpool\Event\PassengerAskAdDeletedUrgentEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Repository\ProposalRepository;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use App\Community\Service\CommunityManager;
use App\DataProvider\Entity\GeoRouterProvider;
use App\DataProvider\Entity\Response;
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

use App\Import\Entity\UserImport;

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
    private $resultManager;
    private $formatDataManager;
    private $params;
    private $internalMessageManager;

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
    public function __construct(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher, ProposalRepository $proposalRepository, DirectionRepository $directionRepository, GeoRouter $geoRouter, ZoneManager $zoneManager, TerritoryManager $territoryManager, CommunityManager $communityManager, LoggerInterface $logger, UserRepository $userRepository, EventDispatcherInterface $dispatcher, AskManager $askManager, ResultManager $resultManager, FormatDataManager $formatDataManager, InternalMessageManager $internalMessageManager, array $params)
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
        $this->resultManager = $resultManager;
        $this->resultManager->setParams($params);
        $this->formatDataManager = $formatDataManager;
        $this->params = $params;
        $this->internalMessageManager = $internalMessageManager;
    }

    /**
     * Get a proposal by its id.
     *
     * @param integer $id
     * @return Proposal|null
     */
    public function get(int $id)
    {
        return $this->proposalRepository->find($id);
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
                if ($community = $this->communityManager->getCommunity($communityId)) {
                    $proposal->addCommunity($community);
                }
            }
        }

        // Get the matchings for the given proposal.
        return $this->treatProposal($proposal, false, true);
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
        return $this->treatProposal($this->setDefaults($proposal));
    }

    /**
     * Set default parameters for a proposal
     *
     * @param Proposal $proposal    The proposal
     * @return Proposal             The proposal treated
     */
    private function setDefaults(Proposal $proposal)
    {
        if (is_null($proposal->getCriteria()->getAnyRouteAsPassenger())) {
            $proposal->getCriteria()->setAnyRouteAsPassenger($this->params['defaultAnyRouteAsPassenger']);
        }
        if (is_null($proposal->getCriteria()->isStrictDate())) {
            $proposal->getCriteria()->setStrictDate($this->params['defaultStrictDate']);
        }
        if (is_null($proposal->getCriteria()->getPriceKm())) {
            $proposal->getCriteria()->setPriceKm($this->params['defaultPriceKm']);
        }
        if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            if (is_null($proposal->getCriteria()->isStrictPunctual())) {
                $proposal->getCriteria()->setStrictPunctual($this->params['defaultStrictPunctual']);
            }
            if (is_null($proposal->getCriteria()->getMarginDuration())) {
                $proposal->getCriteria()->setMarginDuration($this->params['defaultMarginTime']);
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
            if (is_null($proposal->getCriteria()->getToDate())) {
                // end date is usually null, except when creating a proposal after a matching search
                $endDate = clone $proposal->getCriteria()->getFromDate();
                // the date can be immutable
                $toDate = $endDate->add(new \DateInterval('P' . $this->params['defaultRegularLifeTime'] . 'Y'));
                $proposal->getCriteria()->setToDate($toDate);
            }
        }
        return $proposal;
    }

    /**
     * Treat a proposal.
     *
     * @param Proposal  $proposal               The proposal to treat
     * @param boolean   $persist                If we persist the proposal in the database (false for a simple search)
     * @param bool      $excludeProposalUser    Exclude the matching proposals made by the proposal user
     * @return Proposal The treated proposal
     */
    public function treatProposal(Proposal $proposal, $persist = true, bool $excludeProposalUser = true)
    {
        $date = new \DateTime("UTC");
        $this->logger->info('Proposal creation | Start ' . $date->format("Ymd H:i:s.u"));
        
        // set min and max times
        $proposal = $this->setMinMax($proposal);
        
        // set the directions
        $proposal = $this->setDirections($proposal);

        // we have the directions, we can compute the lacking prices
        $proposal = $this->setPrices($proposal);
        
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
                // dispatch en event
                // maybe send a unique event for all matchings ?
                $event = new MatchingNewEvent($matching, $proposal->getUser());
                $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
            }

            // dispatch en event
            $event = new ProposalPostedEvent($proposal);
            $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);
        }

        return $proposal;
    }

    /**
     * Calculation of min and max times.
     * We calculate the min and max times only if the time is set (it could be not set for a simple search)
     *
     * @param Proposal $proposal    The proposal
     * @return Proposal             The proposal treated
     */
    private function setMinMax(Proposal $proposal)
    {
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
        return $proposal;
    }

    /**
     * Set the directions for a proposal
     *
     * @param Proposal $proposal    The proposal
     * @return Proposal             The proposal treated
     */
    private function setDirections(Proposal $proposal)
    {
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
        return $proposal;
    }

    /**
     * Set the prices for a proposal
     *
     * @param Proposal $proposal    The proposal
     * @return Proposal             The proposal treated
     */
    private function setPrices(Proposal $proposal)
    {
        if ($proposal->getCriteria()->getDirectionDriver()) {
            $proposal->getCriteria()->setDriverComputedPrice((string)((int)$proposal->getCriteria()->getDirectionDriver()->getDistance()*(float)$proposal->getCriteria()->getPriceKm()/1000));
            $proposal->getCriteria()->setDriverComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$proposal->getCriteria()->getDriverComputedPrice(), $proposal->getCriteria()->getFrequency()));
        }
        if ($proposal->getCriteria()->getDirectionPassenger()) {
            $proposal->getCriteria()->setPassengerComputedPrice((string)((int)$proposal->getCriteria()->getDirectionPassenger()->getDistance()*(float)$proposal->getCriteria()->getPriceKm()/1000));
            $proposal->getCriteria()->setPassengerComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$proposal->getCriteria()->getPassengerComputedPrice(), $proposal->getCriteria()->getFrequency()));
        }
        return $proposal;
    }

    /**
     * Link related matchings of a proposal.
     * This methods links the corresponding outward and return matchings of a proposal.
     *
     * @param Proposal $proposal
     * @return Proposal
     */
    public function linkRelatedMatchings(Proposal $proposal)
    {
        // link as an offer
        foreach ($proposal->getMatchingRequests() as $matching) {
            // we search the linked matching
            if ($matching->getProposalRequest()->getProposalLinked()) {
                // the request proposal has a linked proposal, we loop through its matchingOffers to check if one of them is the proposalLinked
                foreach ($matching->getProposalRequest()->getProposalLinked()->getMatchingOffers() as $potentialMatchingLinked) {
                    if ($potentialMatchingLinked->getProposalOffer() === $proposal->getProposalLinked()) {
                        // we found a matching linked !
                        $matching->setMatchingLinked($potentialMatchingLinked);
                        break;
                    }
                }
            }
        }
        // link as a request
        foreach ($proposal->getMatchingOffers() as $matching) {
            // we search the linked matching
            if ($matching->getProposalOffer()->getProposalLinked()) {
                // the offer proposal has a linked proposal, we loop through its matchingRequests to check if one of them is the proposalLinked
                foreach ($matching->getProposalOffer()->getProposalLinked()->getMatchingRequests() as $potentialMatchingLinked) {
                    if ($potentialMatchingLinked->getProposalRequest() === $proposal->getProposalLinked()) {
                        // we found a matching linked !
                        $matching->setMatchingLinked($potentialMatchingLinked);
                        break;
                    }
                }
            }
        }
        return $proposal;
    }

    /**
     * Link opposite matchings of a proposal.
     * This methods links the corresponding matchings of a proposal where the roles can be reversed.
     *
     * @param Proposal $proposal
     * @return Proposal
     */
    public function linkOppositeMatchings(Proposal $proposal)
    {
        // link as an offer
        foreach ($proposal->getMatchingRequests() as $matchingRequest) {
            // we search the opposite matching
            foreach ($proposal->getMatchingOffers() as $matchingOffer) {
                if ($matchingRequest->getProposalRequest() === $matchingOffer->getProposalOffer()) {
                    // we found a matching linked !
                    $matchingRequest->setMatchingOpposite($matchingOffer);
                    break;
                }
            }
        }
        return $proposal;
    }



    /************
    *   MASS    *
    *************/

    /**
     * Set the directions and default values for imported users proposals and criterias
     *
     * @param integer $batch    The batch size
     * @return void
     */
    public function setDirectionsAndDefaultsForImport(int $batch)
    {
        $qCriteria = $this->entityManager->createQuery('SELECT c from App\Carpool\Entity\Criteria c JOIN c.proposal p JOIN p.user u JOIN u.import i WHERE i.status='.UserImport::STATUS_USER_TREATED);

        $addressesForRoutesDriver = [];
        $addressesForRoutesPassenger = [];

        $this->logger->info('setDirectionsForProposals | Start creating arrays for calculation at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $iterableResult = $qCriteria->iterate();
        foreach ($iterableResult as $row) {
            $criteria = $row[0];
            $addressesDriver = [];
            $addressesPassenger = [];
            foreach ($criteria->getProposal()->getWaypoints() as $waypoint) {
                if ($criteria->isDriver()) {
                    $addressesDriver[] = $waypoint->getAddress();
                }
                if ($criteria->isPassenger() && ($waypoint->getPosition() == 0 || $waypoint->isDestination())) {
                    $addressesPassenger[] = $waypoint->getAddress();
                }
            }
            if (count($addressesDriver)>0) {
                $addressesForRoutesDriver[$criteria->getId()][] = $addressesDriver;
            }
            if (count($addressesPassenger)>0) {
                $addressesForRoutesPassenger[$criteria->getId()][] = $addressesPassenger;
            }
        }

        $this->logger->info('setDirectionsForProposals | Start georouter multiple calculation for driver at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ownerDriverRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutesDriver, false, true);
        $this->logger->info('setDirectionsForProposals | Start georouter multiple calculation for passenger at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ownerPassengerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutesPassenger, false, true);

        $qCriteria = $this->entityManager->createQuery('SELECT c from App\Carpool\Entity\Criteria c JOIN c.proposal p JOIN p.user u JOIN u.import i WHERE i.status='.UserImport::STATUS_USER_TREATED);

        $pool = 0;
        $iterableResult = $qCriteria->iterate();
        foreach ($iterableResult as $row) {
            $criteria = $row[0];
            if (isset($ownerDriverRoutes[$criteria->getId()])) {
                $direction = $ownerDriverRoutes[$criteria->getId()][0];
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $this->entityManager->persist($direction);
                $criteria->setDirectionDriver($direction);
                $criteria->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100);
                $criteria->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100);
            }
            if (isset($ownerPassengerRoutes[$criteria->getId()])) {
                $direction = $ownerPassengerRoutes[$criteria->getId()][0];
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $this->entityManager->persist($direction);
                $criteria->setDirectionPassenger($direction);
            }

            if (is_null($criteria->getAnyRouteAsPassenger())) {
                $criteria->setAnyRouteAsPassenger($this->params['defaultAnyRouteAsPassenger']);
            }
            if (is_null($criteria->isStrictDate())) {
                $criteria->setStrictDate($this->params['defaultStrictDate']);
            }
            if (is_null($criteria->getPriceKm())) {
                $criteria->setPriceKm($this->params['defaultPriceKm']);
            }
            if ($criteria->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                if (is_null($criteria->isStrictPunctual())) {
                    $criteria->setStrictPunctual($this->params['defaultStrictPunctual']);
                }
                if (is_null($criteria->getMarginDuration())) {
                    $criteria->setMarginDuration($this->params['defaultMarginTime']);
                }
            } else {
                if (is_null($criteria->isStrictRegular())) {
                    $criteria->setStrictRegular($this->params['defaultStrictRegular']);
                }
                if (is_null($criteria->getMonMarginDuration())) {
                    $criteria->setMonMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getTueMarginDuration())) {
                    $criteria->setTueMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getWedMarginDuration())) {
                    $criteria->setWedMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getThuMarginDuration())) {
                    $criteria->setThuMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getFriMarginDuration())) {
                    $criteria->setFriMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getSatMarginDuration())) {
                    $criteria->setSatMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getSunMarginDuration())) {
                    $criteria->setSunMarginDuration($this->params['defaultMarginTime']);
                }
                if (is_null($criteria->getToDate())) {
                    // end date is usually null, except when creating a proposal after a matching search
                    $endDate = clone $criteria->getFromDate();
                    // the date can be immutable
                    $toDate = $endDate->add(new \DateInterval('P' . $this->params['defaultRegularLifeTime'] . 'Y'));
                    $criteria->setToDate($toDate);
                }
            }

            if ($criteria->getFrequency() == Criteria::FREQUENCY_PUNCTUAL && $criteria->getFromTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFromTime(), $criteria->getMarginDuration());
                $criteria->setMinTime($minTime);
                $criteria->setMaxTime($maxTime);
            } else {
                if ($criteria->isMonCheck() && $criteria->getMonTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getMonTime(), $criteria->getMonMarginDuration());
                    $criteria->setMonMinTime($minTime);
                    $criteria->setMonMaxTime($maxTime);
                }
                if ($criteria->isTueCheck() && $criteria->getTueTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getTueTime(), $criteria->getTueMarginDuration());
                    $criteria->setTueMinTime($minTime);
                    $criteria->setTueMaxTime($maxTime);
                }
                if ($criteria->isWedCheck() && $criteria->getWedTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getWedTime(), $criteria->getWedMarginDuration());
                    $criteria->setWedMinTime($minTime);
                    $criteria->setWedMaxTime($maxTime);
                }
                if ($criteria->isThuCheck() && $criteria->getThuTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getThuTime(), $criteria->getThuMarginDuration());
                    $criteria->setThuMinTime($minTime);
                    $criteria->setThuMaxTime($maxTime);
                }
                if ($criteria->isFriCheck() && $criteria->getFriTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFriTime(), $criteria->getFriMarginDuration());
                    $criteria->setFriMinTime($minTime);
                    $criteria->setFriMaxTime($maxTime);
                }
                if ($criteria->isSatCheck() && $criteria->getSatTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSatTime(), $criteria->getSatMarginDuration());
                    $criteria->setSatMinTime($minTime);
                    $criteria->setSatMaxTime($maxTime);
                }
                if ($criteria->isSunCheck() && $criteria->getSunTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSunTime(), $criteria->getSunMarginDuration());
                    $criteria->setSunMinTime($minTime);
                    $criteria->setSunMaxTime($maxTime);
                }
            }

            if ($criteria->getDirectionDriver()) {
                $criteria->setDriverComputedPrice((string)((int)$criteria->getDirectionDriver()->getDistance()*(float)$criteria->getPriceKm()/1000));
                $criteria->setDriverComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getDriverComputedPrice(), $criteria->getFrequency()));
            }
            if ($criteria->getDirectionPassenger()) {
                $criteria->setPassengerComputedPrice((string)((int)$criteria->getDirectionPassenger()->getDistance()*(float)$criteria->getPriceKm()/1000));
                $criteria->setPassengerComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getPassengerComputedPrice(), $criteria->getFrequency()));
            }
            $this->entityManager->persist($criteria);

            // batch
            $pool++;
            if ($pool>=$batch) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $pool = 0;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Create matchings for multiple proposals at once
     *
     * @param array $proposals  The proposals to treat
     * @return array            The proposals treated
     */
    public function createMatchingsForProposals(array $proposals)
    {

        // 1 - make an array of all potential matching proposals for each proposal
        // findPotentialMatchingsForProposals :
        // $potentialProposals = [
        //     'proposalID' => [
        //         'proposal1',
        //         'proposal2',
        //         ...
        //     ]
        // ];

        // 2 - make an array of candidates as driver and passenger
        // $candidatesProposals = [
        //     'proposalID' => [
        //         'candidateDrivers' => [
        //         ],
        //         'candidatePassengers' => [
        //         ]
        //     ]
        // ];

        // todo : make only as drivers as passengers will be obviously tested !!
        $candidatesProposals = $this->proposalMatcher->findPotentialMatchingsForProposals($proposals);

        // 3 - make an array of all routes for single pass mass georouting
        // => multimatch

        foreach ($proposals as $proposal) {
            // matching analyze
            $this->logger->info('Multi Proposal matching | Start ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $proposal = $this->proposalMatcher->createMatchingsForProposal($proposal, true);
            $this->logger->info('Multi Proposal matching | End ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $this->entityManager->persist($proposal);
        }
        $this->entityManager->flush();
        return $proposals;
    }

    /**
     * Create linked and opposite matchings for multiple proposals at once
     *
     * @param array $proposals  The proposals to treat
     * @return array            The proposals treated
     */
    public function createLinkedAndOppositesForProposals(array $proposals)
    {
        foreach ($proposals as $proposal) {
            // if the proposal is a round trip, we want to link the potential matching results
            if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
                $proposal = $this->linkRelatedMatchings($proposal);
                $this->entityManager->persist($proposal);
            }
            // if the requester can be driver and passenger, we want to link the potential opposite matching results
            if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                // linking for the outward
                $proposal = $this->linkOppositeMatchings($proposal);
                $this->entityManager->persist($proposal);
                if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
                    // linking for the return
                    $return = $this->linkOppositeMatchings($proposal->getProposalLinked());
                    $this->entityManager->persist($return);
                }
            }
        }
        $this->entityManager->flush();
        return $proposals;
    }

    /************
    *   RDEX    *
    *************/

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

    /**
     * @param Proposal $proposal
     * @param array|null $body
     * @return Response
     * @throws \Exception
     */
    public function deleteProposal(Proposal $proposal, ?array $body)
    {
        $asks = $this->askManager->getAsksFromProposal($proposal);

        $this->entityManager->remove($proposal);

        if (count($asks) > 0) {
            /** @var Ask $ask */
            foreach ($asks as $ask) {
                if ($body["deletionMessage"]) {
                    // creates a new thread, todo: adapt after "carpoolMessagesFromAskHistories" branch merge to avoid that
                    $askHistory = new AskHistory();
                    $message = $this->internalMessageManager->createMessage($proposal->getUser(), [$ask->getUser()], $body["deletionMessage"], null, null);
                    $askHistory->setMessage($message);
                    $askHistory->setAsk($ask);
                    $askHistory->setStatus($ask->getStatus());
                    $askHistory->setType($ask->getType());

                    $this->entityManager->persist($askHistory);
                }

                $now = new \DateTime();

                // todo: change status after update
                // Accepted
                if ($ask->getStatus() === 3) {

                    // Ask user is driver
                    if ($this->askManager->isAskUserDriver($ask)) {
                        /** @var Criteria $criteria */
                        $criteria = $ask->getMatching()->getProposalOffer()->getCriteria()->getFromDate();
                        $askDateTime = $criteria->getFromTime() ?
                            new \DateTime($criteria->getFromDate()->format('Y-m-d') . ' ' . $criteria->getFromTime()->format('H:i:s')) :
                            new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                        // If ad is in more than 24h
                        if (strtotime($now) - strtotime($askDateTime) > 24*60*60) {
                            $event = new DriverAskAdDeletedEvent($ask);
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new DriverAskAdDeletedUrgentEvent($ask);
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedUrgentEvent::NAME, $event);
                        }

                        // Ask user is passenger
                    } elseif ($this->askManager->isAskUserPassenger($ask)) {

                        /** @var Criteria $criteria */
                        $criteria = $ask->getMatching()->getProposalRequest()->getCriteria();
                        $askDateTime = $criteria->getFromTime() ?
                            new \DateTime($criteria->getFromDate()->format('Y-m-d') . ' ' . $criteria->getFromTime()->format('H:i:s')) :
                            new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                        // If ad is in more than 24h
                        if ($askDateTime->getTimestamp() - $now->getTimestamp() < 24*60*60) {
                            $event = new PassengerAskAdDeletedEvent($ask);
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new PassengerAskAdDeletedUrgentEvent($ask);
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedUrgentEvent::NAME, $event);
                        }
                    }
                }
                // Pending
                elseif ($ask->getStatus() === 2) {
                    $event = new AskAdDeletedEvent($ask);
                    $this->eventDispatcher->dispatch(AskAdDeletedEvent::NAME, $event);
                }
            }
        }

        $this->entityManager->flush();

        return new Response(204, "Deleted with success");
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
