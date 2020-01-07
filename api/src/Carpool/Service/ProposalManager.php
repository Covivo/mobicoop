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
use App\Carpool\Repository\CriteriaRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use App\Community\Service\CommunityManager;
use App\DataProvider\Entity\Response;
use App\Geography\Entity\Address;
use App\Geography\Entity\Zone;
use App\Geography\Interfaces\GeorouterInterface;
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
    private $criteriaRepository;

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
    public function __construct(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher, ProposalRepository $proposalRepository, DirectionRepository $directionRepository, GeoRouter $geoRouter, ZoneManager $zoneManager, TerritoryManager $territoryManager, CommunityManager $communityManager, LoggerInterface $logger, UserRepository $userRepository, EventDispatcherInterface $dispatcher, AskManager $askManager, ResultManager $resultManager, FormatDataManager $formatDataManager, InternalMessageManager $internalMessageManager, CriteriaRepository $criteriaRepository, array $params)
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
        $this->criteriaRepository = $criteriaRepository;
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
     * Prepare a proposal for persist.
     * Used when posting a proposal to populate default values like proposal validity.
     *
     * @param Proposal $proposal
     * @param Boolean $sendEvent
     * @return void
     */
    public function prepareProposal(Proposal $proposal, bool $sendEvent=true): Proposal
    {
        return $this->treatProposal($this->setDefaults($proposal), true, true, $sendEvent);
    }

    /**
     * Set default parameters for a proposal
     *
     * @param Proposal $proposal    The proposal
     * @return Proposal             The proposal treated
     */
    private function setDefaults(Proposal $proposal)
    {
        $this->logger->info("ProposalManager : setDefaults " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

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
     * @param bool      $sendEvent              Send new matching event
     * @return Proposal The treated proposal
     */
    public function treatProposal(Proposal $proposal, $persist = true, bool $excludeProposalUser = true, bool $sendEvent = true)
    {
        $this->logger->info("ProposalManager : treatProposal " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // set min and max times
        $proposal = $this->setMinMax($proposal);
        
        // set the directions
        $proposal = $this->setDirections($proposal);

        // we have the directions, we can compute the lacking prices
        $proposal = $this->setPrices($proposal);
        
        // matching analyze
        $proposal = $this->proposalMatcher->createMatchingsForProposal($proposal, $excludeProposalUser);
        
        if ($persist) {
            $this->logger->info("ProposalManager : start persist " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            // TODO : here we should remove the previously matched proposal if they already exist
            $this->entityManager->persist($proposal);
            $this->entityManager->flush();
            $this->logger->info("ProposalManager : end persist " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }
        
        // TODO : see which events send !!!

        // $matchingOffers = $proposal->getMatchingOffers();
        // $matchingRequests = $proposal->getMatchingRequests();
        // $matchings=[];
        // while (($item = array_shift($matchingOffers)) !== null && array_push($matchings, $item));
        // while (($item = array_shift($matchingRequests)) !== null && array_push($matchings, $item));
        // if ($persist) {
        //     $matchingForEvent = null;
        //     foreach ($matchings as $matching) {
        //         $matchingForEvent = $matching; // TO DO : Choose the right matching for the event
        //         // dispatch en event
        //         // maybe send a unique event for all matchings ?
        //         $event = new MatchingNewEvent($matching, $proposal->getUser());
        //         $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
        //     }

        //     // dispatch en event
        //     $event = new ProposalPostedEvent($proposal);
        //     $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);

        //     // dispatch en event
        //     // todo determine the right matching to send
        //     if ($sendEvent && !is_null($matchingForEvent)) {
        //         $event = new MatchingNewEvent($matchingForEvent, $proposal->getUser());
        //         $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
        //     }
        //     // dispatch en event who is not sent
        //     // $event = new ProposalPostedEvent($proposal);
        //     // $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);
        // }

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
        if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
            // for now we only keep the first route !
            // if we ever want alternative routes we should pass the route as parameter of this method
            // (problem : the route has no id, we should pass the whole route to check which route is chosen by the user...
            //      => we would have to think of a way to simplify...)
            $direction = $routes[0];
            // creation of the crossed zones
            $direction = $this->zoneManager->createZonesForDirection($direction);
            $direction->setAutoGeoJsonDetail();
            if ($proposal->getCriteria()->isDriver()) {
                $proposal->getCriteria()->setDirectionDriver($direction);
                $proposal->getCriteria()->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100);
                $proposal->getCriteria()->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100);
            }
            if ($proposal->getCriteria()->isPassenger()) {
                // if the user is passenger we keep only the first and last points
                $routes = $this->geoRouter->getRoutes([$addresses[0],$addresses[count($addresses)-1]], false, false, GeorouterInterface::RETURN_TYPE_OBJECT);
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
        gc_enable();
        $this->logger->info('setDirectionsForProposals | Start creating arrays for calculation at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $criterias = $this->criteriaRepository->findByUserImportStatus(UserImport::STATUS_USER_TREATED);
        
        $addressesForRoutes = [];
        $owner = [];
        $ids = [];

        $i=0;
        $this->logger->info('setDirectionsForProposals | Start iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        foreach ($criterias as $criteria) {
            $addressesDriver = [];
            $addressesPassenger = [];
            foreach ($criteria->getProposal()->getWaypoints() as $waypoint) {
                // waypoints are already retrieved ordered by position, no need to check the position here
                if ($criteria->isDriver()) {
                    $addressesDriver[] = $waypoint->getAddress();
                }
                if ($criteria->isPassenger() && ($waypoint->getPosition() == 0 || $waypoint->isDestination())) {
                    $addressesPassenger[] = $waypoint->getAddress();
                }
            }
            if (count($addressesDriver)>0) {
                $addressesForRoutes[$i] = [$addressesDriver];
                $owner[$criteria->getId()]['driver'] = $i;
                $i++;
            }
            if (count($addressesPassenger)>0) {
                $addressesForRoutes[$i] = [$addressesPassenger];
                $owner[$criteria->getId()]['passenger'] = $i;
                $i++;
            }
            $ids[] = $criteria->getId();
        }
        $this->logger->info('setDirectionsForProposals | End iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        $this->logger->info('setDirectionsForProposals | Start get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_RAW);
        $this->logger->info('setDirectionsForProposals | End get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));


        $qCriteria = $this->entityManager->createQuery('SELECT c from App\Carpool\Entity\Criteria c WHERE c.id IN (' . implode(',', $ids) . ')');

        $iterableResult = $qCriteria->iterate();
        $this->logger->info('Start treat rows ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $pool = 0;
        foreach ($iterableResult as $row) {
            $criteria = $row[0];
            // foreach ($criterias as $criteria) {
            if (isset($owner[$criteria->getId()]['driver']) && isset($ownerRoutes[$owner[$criteria->getId()]['driver']])) {
                $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['driver']][0]);
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $direction->setSaveGeoJson(true);
                $criteria->setDirectionDriver($direction);
                $criteria->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100);
                $criteria->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100);
            }
            if (isset($owner[$criteria->getId()]['passenger']) && isset($ownerRoutes[$owner[$criteria->getId()]['passenger']])) {
                $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['passenger']][0]);
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $direction->setSaveGeoJson(true);
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
                if ($criteria->getDirectionDriver()) {
                    $criteria->setDriverComputedPrice((string)((int)$criteria->getDirectionDriver()->getDistance()*(float)$criteria->getPriceKm()/1000));
                    $criteria->setDriverComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getDriverComputedPrice(), $criteria->getFrequency()));
                }
                if ($criteria->getDirectionPassenger()) {
                    $criteria->setPassengerComputedPrice((string)((int)$criteria->getDirectionPassenger()->getDistance()*(float)$criteria->getPriceKm()/1000));
                    $criteria->setPassengerComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getPassengerComputedPrice(), $criteria->getFrequency()));
                }
            }
                
            // batch
            $pool++;
            if ($pool>=$batch) {
                $this->logger->info('Batch ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                $this->entityManager->flush();
                $this->entityManager->clear();
                gc_collect_cycles();
                $pool = 0;
            }
        }
        
        $this->logger->info('Stop treat rows ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // final flush for pending persists
        if ($pool>0) {
            $this->logger->info('Start final flush ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $this->entityManager->flush();
            $this->logger->info('Start clear ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $this->entityManager->clear();
            gc_collect_cycles();
            $this->logger->info('End flush clear ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        }
        
        $this->logger->info('End update status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }


    
    // public function setDirectionsAndDefaultsForImport(int $batch)
    // {
    //     gc_enable();
    //     $this->logger->info('setDirectionsForProposals | Start creating arrays for calculation at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

    //     $criterias = $this->criteriaRepository->findByUserImportStatus(UserImport::STATUS_USER_TREATED);
        
    //     $addressesForRoutes = [];
    //     $owner = [];
    //     $ids = [];

    //     $i=0;
    //     $this->logger->info('setDirectionsForProposals | Start iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //     foreach ($criterias as $criteria) {
    //         $addressesDriver = [];
    //         $addressesPassenger = [];
    //         foreach ($criteria->getProposal()->getWaypoints() as $waypoint) {
    //             // waypoints are already retrieved ordered by position, no need to check the position here
    //             if ($criteria->isDriver()) {
    //                 $addressesDriver[] = $waypoint->getAddress();
    //             }
    //             if ($criteria->isPassenger() && ($waypoint->getPosition() == 0 || $waypoint->isDestination())) {
    //                 $addressesPassenger[] = $waypoint->getAddress();
    //             }
    //         }
    //         if (count($addressesDriver)>0) {
    //             $addressesForRoutes[$i] = [$addressesDriver];
    //             $owner[$criteria->getId()]['driver'] = $i;
    //             $i++;
    //         }
    //         if (count($addressesPassenger)>0) {
    //             $addressesForRoutes[$i] = [$addressesPassenger];
    //             $owner[$criteria->getId()]['passenger'] = $i;
    //             $i++;
    //         }
    //         $ids[] = $criteria->getId();
    //     }
    //     $this->logger->info('setDirectionsForProposals | End iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
    //     $this->logger->info('setDirectionsForProposals | Start get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //     $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_RAW);
    //     $this->logger->info('setDirectionsForProposals | End get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
    //     $directions = [];
    //     $directionString = "";
    //     $directionStrings = [];

    //     // TODO : create SQL BATCHES
    //     $insert_criteria_sql_begin = "
    //         INSERT INTO criteria (
    //             id,
    //             max_detour_distance,
    //             max_detour_duration,
    //             direction_driver_id,
    //             direction_passenger_id,
    //             any_route_as_passenger,
    //             strict_date,
    //             price_km,
    //             strict_punctual,
    //             margin_duration,
    //             strict_regular,
    //             mon_margin_duration,
    //             tue_margin_duration,
    //             wed_margin_duration,
    //             thu_margin_duration,
    //             fri_margin_duration,
    //             sat_margin_duration,
    //             sun_margin_duration,
    //             to_date,
    //             min_time,
    //             max_time,
    //             mon_min_time,
    //             mon_max_time,
    //             tue_min_time,
    //             tue_max_time,
    //             wed_min_time,
    //             wed_max_time,
    //             thu_min_time,
    //             thu_max_time,
    //             fri_min_time,
    //             fri_max_time,
    //             sat_min_time,
    //             sat_max_time,
    //             sun_min_time,
    //             sun_max_time,
    //             driver_computed_price,
    //             driver_computed_rounded_price,
    //             passenger_computed_price,
    //             passenger_computed_rounded_price
    //         ) VALUES
    //     ";

    //     $insert_criteria_sql_end = " ON DUPLICATE KEY UPDATE
    //     max_detour_distance=VALUES(max_detour_distance),
    //     max_detour_duration=VALUES(max_detour_duration),
    //     direction_driver_id=VALUES(direction_driver_id),
    //     direction_passenger_id=VALUES(direction_passenger_id),
    //     any_route_as_passenger=VALUES(any_route_as_passenger),
    //     strict_date=VALUES(strict_date),
    //     price_km=VALUES(price_km),
    //     strict_punctual=VALUES(strict_punctual),
    //     margin_duration=VALUES(margin_duration),
    //     strict_regular=VALUES(strict_regular),
    //     mon_margin_duration=VALUES(mon_margin_duration),
    //     tue_margin_duration=VALUES(tue_margin_duration),
    //     wed_margin_duration=VALUES(wed_margin_duration),
    //     thu_margin_duration=VALUES(thu_margin_duration),
    //     fri_margin_duration=VALUES(fri_margin_duration),
    //     sat_margin_duration=VALUES(sat_margin_duration),
    //     sun_margin_duration=VALUES(sun_margin_duration),
    //     to_date=VALUES(to_date),
    //     min_time=VALUES(min_time),
    //     max_time=VALUES(max_time),
    //     mon_min_time=VALUES(mon_min_time),
    //     mon_max_time=VALUES(mon_max_time),
    //     tue_min_time=VALUES(tue_min_time),
    //     tue_max_time=VALUES(tue_max_time),
    //     wed_min_time=VALUES(wed_min_time),
    //     wed_max_time=VALUES(wed_max_time),
    //     thu_min_time=VALUES(thu_min_time),
    //     thu_max_time=VALUES(thu_max_time),
    //     fri_min_time=VALUES(fri_min_time),
    //     fri_max_time=VALUES(fri_max_time),
    //     sat_min_time=VALUES(sat_min_time),
    //     sat_max_time=VALUES(sat_max_time),
    //     sun_min_time=VALUES(sun_min_time),
    //     sun_max_time=VALUES(sun_max_time),
    //     driver_computed_price=VALUES(driver_computed_price),
    //     driver_computed_rounded_price=VALUES(driver_computed_rounded_price),
    //     passenger_computed_price=VALUES(passenger_computed_price),
    //     passenger_computed_rounded_price=VALUES(passenger_computed_rounded_price),
    //     frequency = frequency,
    //     driver = driver,
    //     passenger = passenger,
    //     seats_driver = seats_driver,
    //     seats_passenger = seats_passenger,
    //     from_date = from_date,
    //     from_time = from_time,
    //     mon_check = mon_check,
    //     tue_check = tue_check,
    //     wed_check = wed_check,
    //     thu_check = thu_check,
    //     fri_check = fri_check,
    //     sat_check = sat_check,
    //     sun_check = sun_check,
    //     mon_time = mon_time,
    //     tue_time = tue_time,
    //     wed_time = wed_time,
    //     thu_time = thu_time,
    //     fri_time = fri_time,
    //     sat_time = sat_time,
    //     sun_time = sun_time,
    //     multi_transport_mode = multi_transport_mode,
    //     driver_price = driver_price,
    //     passenger_price = passenger_price,
    //     luggage = luggage,
    //     bike = bike,
    //     back_seats = back_seats,
    //     solidary = solidary,
    //     solidary_exclusive = solidary_exclusive,
    //     avoid_motorway = avoid_motorway,
    //     avoid_toll = avoid_toll,
    //     created_date = created_date
    //     ;";

    //     $update_criteria_sqls = [];
 
    //     $this->logger->info('Start treat rows ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //     $id=0; // set here the first direction id
    //     $i=0;
    //     $poolCriteria = 0;
    //     $poolDirection = 0;
    //     foreach ($criterias as $criteria) {
    //         //$this->logger->info('Treat row ' . $i . " " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //         if ($poolCriteria == 0) {
    //             $update_criteria_sql = $insert_criteria_sql_begin;
    //         }
    //         if ($poolDirection == 0) {
    //             $directionString = "";
    //         }
    //         $update_criteria_sql .= "(" . $criteria->getId() . ",";
    //         if (isset($owner[$criteria->getId()]['driver']) && isset($ownerRoutes[$owner[$criteria->getId()]['driver']])) {
    //             $id++;
    //             $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['driver']][0]);
    //             //$direction = $this->zoneManager->createZonesForDirection($direction);
    //             $direction->setId($id);
    //             $direction->setSaveGeoJson(true);
    //             $direction->setAutoGeoJsonBbox();
    //             $direction->setAutoGeoJsonDetail();
    //             $direction->setAutoCreatedDate();
    //             $directions[$criteria->getId()]['driver'] = ['id'=>$id,'distance'=>$direction->getDistance()];
    //             $directionString .= $direction->getDirectionString() . "\r\n";
    //             //fwrite($fpd, $direction->getDirectionString() . "\r\n");
    //             $update_criteria_sql .= $direction->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100 . "," . $direction->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100 . "," . $id . ",";
    //         } else {
    //             $update_criteria_sql .= "null,null,null,";
    //         }
    //         if (isset($owner[$criteria->getId()]['passenger']) && isset($ownerRoutes[$owner[$criteria->getId()]['passenger']])) {
    //             $id++;
    //             $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['passenger']][0]);
    //             //$direction = $this->zoneManager->createZonesForDirection($direction);
    //             $direction->setId($id);
    //             $direction->setSaveGeoJson(true);
    //             $direction->setAutoGeoJsonBbox();
    //             $direction->setAutoGeoJsonDetail();
    //             $direction->setAutoCreatedDate();
    //             $directions[$criteria->getId()]['passenger'] = ['id'=>$id,'distance'=>$direction->getDistance()];
    //             $directionString .= $direction->getDirectionString() . "\r\n";
    //             //fwrite($fpd, $direction->getDirectionString() . "\r\n");
    //             $update_criteria_sql .= $id . ",";
    //         } else {
    //             $update_criteria_sql .= "null,";
    //         }
                
    //         if (is_null($criteria->getAnyRouteAsPassenger())) {
    //             $update_criteria_sql .= ($this->params['defaultAnyRouteAsPassenger'] ? '1' : '0') . ",";
    //         } else {
    //             $update_criteria_sql .= ($criteria->getAnyRouteAsPassenger() ? '1' : '0') . ",";
    //         }
    //         if (is_null($criteria->isStrictDate())) {
    //             $update_criteria_sql .= ($this->params['defaultStrictDate'] ? '1' : '0') . ",";
    //         } else {
    //             $update_criteria_sql .= ($criteria->isStrictDate() ? '1' : '0') . ",";
    //         }
    //         if (is_null($criteria->getPriceKm())) {
    //             $update_criteria_sql .= $this->params['defaultPriceKm'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getPriceKm() . ",";
    //         }
    //         if (is_null($criteria->isStrictPunctual())) {
    //             $update_criteria_sql .= ($this->params['defaultStrictRegular'] ? '1' : '0') . ",";
    //         } else {
    //             $update_criteria_sql .= ($criteria->isStrictPunctual() ? '1' : '0') . ",";
    //         }
    //         if (is_null($criteria->getMarginDuration())) {
    //             $criteria->setMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->isStrictRegular())) {
    //             $update_criteria_sql .= ($this->params['defaultStrictRegular'] ? '1' : '0') . ",";
    //         } else {
    //             $update_criteria_sql .= ($criteria->isStrictRegular() ? '1' : '0') . ",";
    //         }
    //         if (is_null($criteria->getMonMarginDuration())) {
    //             $criteria->setMonMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getMonMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getTueMarginDuration())) {
    //             $criteria->setTueMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getTueMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getWedMarginDuration())) {
    //             $criteria->setWedMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getWedMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getThuMarginDuration())) {
    //             $criteria->setThuMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getThuMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getFriMarginDuration())) {
    //             $criteria->setFriMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getFriMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getSatMarginDuration())) {
    //             $criteria->setSatMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getSatMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getSunMarginDuration())) {
    //             $criteria->setSunMarginDuration($this->params['defaultMarginTime']);
    //             $update_criteria_sql .= $this->params['defaultMarginTime'] . ",";
    //         } else {
    //             $update_criteria_sql .= $criteria->getSunMarginDuration() . ",";
    //         }
    //         if (is_null($criteria->getToDate())) {
    //             // end date is usually null, except when creating a proposal after a matching search
    //             $endDate = clone $criteria->getFromDate();
    //             // the date can be immutable
    //             $toDate = $endDate->add(new \DateInterval('P' . $this->params['defaultRegularLifeTime'] . 'Y'));
    //             $update_criteria_sql .= "'" . $toDate->format('Y-m-d') . "',";
    //         } else {
    //             $update_criteria_sql .= "'" . $criteria->getToDate()->format('Y-m-d') . "',";
    //         }

    //         if ($criteria->getFromTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFromTime(), $criteria->getMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isMonCheck() && $criteria->getMonTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getMonTime(), $criteria->getMonMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isTueCheck() && $criteria->getTueTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getTueTime(), $criteria->getTueMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isWedCheck() && $criteria->getWedTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getWedTime(), $criteria->getWedMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isThuCheck() && $criteria->getThuTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getThuTime(), $criteria->getThuMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isFriCheck() && $criteria->getFriTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFriTime(), $criteria->getFriMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isSatCheck() && $criteria->getSatTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSatTime(), $criteria->getSatMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if ($criteria->isSunCheck() && $criteria->getSunTime()) {
    //             list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSunTime(), $criteria->getSunMarginDuration());
    //             $update_criteria_sql .= "'" . $minTime->format('H:i:s') . "','" . $maxTime->format('H:i:s') . "',";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if (isset($directions[$criteria->getId()]['driver'])) {
    //             $criteria->setDriverComputedPrice((string)((int)$directions[$criteria->getId()]['driver']['distance']*(float)$criteria->getPriceKm()/1000));
    //             $criteria->setDriverComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getDriverComputedPrice(), $criteria->getFrequency()));
    //             $update_criteria_sql .= $criteria->getDriverComputedPrice() . "," . $criteria->getDriverComputedRoundedPrice() . ",";
    //         } else {
    //             $update_criteria_sql .= "null,null,";
    //         }
    //         if (isset($directions[$criteria->getId()]['passenger'])) {
    //             $criteria->setPassengerComputedPrice((string)((int)$directions[$criteria->getId()]['passenger']['distance']*(float)$criteria->getPriceKm()/1000));
    //             $criteria->setPassengerComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$criteria->getPassengerComputedPrice(), $criteria->getFrequency()));
    //             $update_criteria_sql .= $criteria->getPassengerComputedPrice() . "," . $criteria->getPassengerComputedRoundedPrice() . "),";
    //         } else {
    //             $update_criteria_sql .= "null,null),";
    //         }
    //         $poolCriteria++;
    //         if ($poolCriteria >= $batch) {
    //             // delete last comma
    //             $this->logger->info('Batch Criteria ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //             $update_criteria_sql = substr($update_criteria_sql, 0, -1);
    //             $update_criteria_sql .= $insert_criteria_sql_end;
    //             $update_criteria_sqls[] = $update_criteria_sql;
    //             $poolCriteria = 0;
    //         }
    //         $poolDirection++;
    //         if ($poolDirection >= 1000) {
    //             $this->logger->info('Batch Direction ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //             $directionStrings[] = $directionString;
    //             $poolDirection = 0;
    //         }
    //         $i++;
    //     }

    //     // last batch
    //     if ($poolCriteria>0) {
    //         $this->logger->info('Batch Criteria ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //         // delete last comma
    //         $update_criteria_sql = substr($update_criteria_sql, 0, -1);
    //         $update_criteria_sql .= $insert_criteria_sql_end;
    //         $update_criteria_sqls[] = $update_criteria_sql;
    //     }
    //     if ($poolDirection>0) {
    //         $this->logger->info('Batch Direction ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    //         $directionStrings[] = $directionString;
    //     }

    //     $conn = $this->entityManager->getConnection();
    //     // create rows
    //     $filenameDirections = "/var/www/api/public/upload/match/directions.txt";

    //     foreach ($directionStrings as $directionString) {
    //         $fpd = fopen($filenameDirections, 'w');
    //         fwrite($fpd, $directionString);
    //         fclose($fpd);
    //         $sql = "
    //             LOAD DATA LOCAL INFILE '" . $filenameDirections . "' IGNORE INTO TABLE direction FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n'
    //             (@id,@distance,@duration,@ascend,@descend,@bboxMinLon,@bboxMinLat,@bboxMaxLon,@bboxMaxLat,@detail,@format,@snapped,@bearing,@geoJsonBbox,@geoJsonDetail,@createdDate,@updatedDate,@geoJsonSimplified)
    //             set
    //             `id` = @id,
    //             `distance` = @distance,
    //             `duration` = @duration,
    //             `ascend` = @ascend,
    //             `descend` = @descend,
    //             `bbox_min_lon` = @bboxMinLon,
    //             `bbox_min_lat` = @bboxMinLat,
    //             `bbox_max_lon` = @bboxMaxLon,
    //             `bbox_max_lat` = @bboxMaxLat,
    //             `detail` = @detail,
    //             `format` = @format,
    //             `snapped` = @snapped,
    //             `bearing` = @bearing,
    //             `geo_json_bbox` = GeomFromText(@geoJsonBbox),
    //             `geo_json_detail` = GeomFromText(@geoJsonDetail),
    //             `created_date` = @createdDate,
    //             `updated_date` = @updatedDate,
    //             `geo_json_simplified` = GeomFromText(@geoJsonSimplified)
    //             ;
    //         ";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->execute();
    //     }
    //     unlink($filenameDirections);
    //     $this->logger->info('End Direction ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
       
    //     foreach ($update_criteria_sqls as $update) {
    //         $stmt = $conn->prepare($update);
    //         $stmt->execute();
    //     }

    //     $this->logger->info('End Criteria ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    // }

    /**
     * Create matchings for multiple proposals at once
     *
     * @param array $proposals  The proposals to treat
     * @return array            The proposals treated
     */
    public function createMatchingsForProposals(array $proposalIds)
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

        $this->logger->info('Start creating candidates | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $this->proposalMatcher->findPotentialMatchingsForProposals($proposalIds);
        $this->logger->info('End creating candidates | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        return $proposalIds;
    }

    /**
     * Create linked and opposite matchings for multiple proposals at once
     *
     * @param array $proposals  The proposals to treat
     * @return array            The proposals treated
     */
    public function createLinkedAndOppositesForProposals(array $proposals)
    {
        foreach ($proposals as $proposalId) {
            $proposal = $this->proposalRepository->find($proposalId['id']);
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
    // public function getProposalsForRdex(
    //     bool $offer,
    //     bool $request,
    //     float $from_longitude,
    //     float $from_latitude,
    //     float $to_longitude,
    //     float $to_latitude,
    //     string $frequency = null,
    //     \DateTime $outward_mindate = null,
    //     \DateTime $outward_maxdate = null,
    //     string $outward_monday_mintime = null,
    //     string $outward_monday_maxtime = null,
    //     string $outward_tuesday_mintime = null,
    //     string $outward_tuesday_maxtime = null,
    //     string $outward_wednesday_mintime = null,
    //     string $outward_wednesday_maxtime = null,
    //     string $outward_thursday_mintime = null,
    //     string $outward_thursday_maxtime = null,
    //     string $outward_friday_mintime = null,
    //     string $outward_friday_maxtime = null,
    //     string $outward_saturday_mintime = null,
    //     string $outward_saturday_maxtime = null,
    //     string $outward_sunday_mintime = null,
    //     string $outward_sunday_maxtime = null
    // ) {
    //     // test : we return all proposals
    //     // we create a proposal with the parameters
    //     $proposal = new Proposal();
    //     $proposal->setType(Proposal::TYPE_ONE_WAY);
    //     $addressFrom = new Address();
    //     $addressFrom->setLongitude((string)$from_longitude);
    //     $addressFrom->setLatitude((string)$from_latitude);
    //     // for now we don't search with coordinates, we force the localities for testing purpose
    //     // @todo delete the locality search only
    //     $addressFrom->setAddressLocality("Nancy");
    //     $addressTo = new Address();
    //     $addressTo->setLongitude((string)$to_longitude);
    //     $addressTo->setLatitude((string)$to_latitude);
    //     $addressTo->setAddressLocality("Metz");
    //     $waypointFrom = new Waypoint();
    //     $waypointFrom->setAddress($addressFrom);
    //     $waypointFrom->setPosition(0);
    //     $waypointFrom->setDestination(false);
    //     $waypointTo = new Waypoint();
    //     $waypointTo->setAddress($addressTo);
    //     $waypointTo->setPosition(1);
    //     $waypointTo->setDestination(true);
    //     $criteria = new Criteria();
    //     $criteria->setDriver(!$offer);
    //     $criteria->setPassenger(!$request);
    //     if (!is_null($outward_mindate)) {
    //         $criteria->setFromDate($outward_mindate);
    //     } else {
    //         $criteria->setFromDate(new \DateTime());
    //     }
    //     if (!is_null($outward_maxdate)) {
    //         $criteria->setToDate($outward_maxdate);
    //     }
    //     $proposal->setCriteria($criteria);
    //     $proposal->addWaypoint($waypointFrom);
    //     $proposal->addWaypoint($waypointTo);
    //     // for now we don't use the time parameters
    //     // @todo add the time parameters
    //     return $this->proposalRepository->findMatchingProposals($proposal, false);
    // }

    /**
     * @param Proposal $proposal
     * @param array|null $body
     * @return Response
     * @throws \Exception
     */
    public function deleteProposal(Proposal $proposal, ?array $body)
    {
        $asks = $this->askManager->getAsksFromProposal($proposal);
        if (count($asks) > 0) {
            /** @var Ask $ask */
            foreach ($asks as $ask) {
                $deleter = ($body['deleterId'] == $ask->getUser()->getId()) ? $ask->getUser() : $ask->getUserRelated();
                $recipient = ($body['deleterId'] == $ask->getUser()->getId()) ? $ask->getUserRelated() : $ask->getUser();
                if (isset($body["deletionMessage"]) && $body["deletionMessage"] != "") {
                    $message = $this->internalMessageManager->createMessage($deleter, [$recipient], $body["deletionMessage"], null, null);
                    $this->entityManager->persist($message);
                }

                $now = new \DateTime();
                // Ask user is driver
                if (($this->askManager->isAskUserDriver($ask) && ($ask->getUser()->getId() == $deleter->getId())) || ($this->askManager->isAskUserPassenger($ask) && ($ask->getUserRelated()->getId() == $deleter->getId()))) {
                    // TO DO check if the deletion is just before 24h and in that case send an other email
                    // /** @var Criteria $criteria */
                    $criteria = $ask->getMatching()->getProposalOffer()->getCriteria();
                    $askDateTime = $criteria->getFromTime() ?
                        new \DateTime($criteria->getFromDate()->format('Y-m-d') . ' ' . $criteria->getFromTime()->format('H:i:s')) :
                        new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                    // Accepted
                    if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER or $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                        if ($askDateTime->getTimestamp() - $now->getTimestamp() > 24*60*60) {
                            $event = new DriverAskAdDeletedEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new DriverAskAdDeletedUrgentEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedUrgentEvent::NAME, $event);
                        }
                    } elseif ($ask->getStatus() == Ask::STATUS_PENDING_AS_DRIVER or $ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER) {
                        $event = new AskAdDeletedEvent($ask, $deleter->getId());
                        $this->eventDispatcher->dispatch(AskAdDeletedEvent::NAME, $event);
                    }
                    // Ask user is passenger
                } elseif (($this->askManager->isAskUserPassenger($ask) && ($ask->getUser()->getId() == $deleter->getId())) || ($this->askManager->isAskUserDriver($ask) && ($ask->getUserRelated()->getId() == $deleter->getId()))) {

                    // TO DO check if the deletion is just before 24h and in that case send an other email
                    // /** @var Criteria $criteria */
                    $criteria = $ask->getMatching()->getProposalRequest()->getCriteria();
                    $askDateTime = $criteria->getFromTime() ?
                        new \DateTime($criteria->getFromDate()->format('Y-m-d') . ' ' . $criteria->getFromTime()->format('H:i:s')) :
                        new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                    // Accepted
                    if ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER or $ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                        // If ad is in more than 24h
                        if ($askDateTime->getTimestamp() - $now->getTimestamp() > 24*60*60) {
                            $event = new PassengerAskAdDeletedEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new PassengerAskAdDeletedUrgentEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedUrgentEvent::NAME, $event);
                        }
                    } elseif ($ask->getStatus() == Ask::STATUS_PENDING_AS_DRIVER or $ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER) {
                        $event = new AskAdDeletedEvent($ask, $deleter->getId());
                        $this->eventDispatcher->dispatch(AskAdDeletedEvent::NAME, $event);
                    }
                }
            }
        }

        $this->entityManager->remove($proposal);
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
