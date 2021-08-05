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
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
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
use App\User\Entity\User;
use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ProposalManager
{
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_BOTH = 3;

    private $entityManager;
    private $proposalMatcher;
    private $proposalRepository;
    private $matchingRepository;
    private $geoRouter;
    private $zoneManager;
    private $directionRepository;
    private $territoryManager;
    private $userRepository;
    private $logger;
    private $eventDispatcher;
    private $askManager;
    private $resultManager;
    private $formatDataManager;
    private $params;
    private $internalMessageManager;
    private $criteriaRepository;
    private $actionRepository;

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
    public function __construct(
        EntityManagerInterface $entityManager,
        ProposalMatcher $proposalMatcher,
        ProposalRepository $proposalRepository,
        MatchingRepository $matchingRepository,
        DirectionRepository $directionRepository,
        GeoRouter $geoRouter,
        ZoneManager $zoneManager,
        TerritoryManager $territoryManager,
        LoggerInterface $logger,
        UserRepository $userRepository,
        EventDispatcherInterface $dispatcher,
        AskManager $askManager,
        ResultManager $resultManager,
        FormatDataManager $formatDataManager,
        InternalMessageManager $internalMessageManager,
        CriteriaRepository $criteriaRepository,
        ActionRepository $actionRepository,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->proposalRepository = $proposalRepository;
        $this->matchingRepository = $matchingRepository;
        $this->directionRepository = $directionRepository;
        $this->geoRouter = $geoRouter;
        $this->zoneManager = $zoneManager;
        $this->territoryManager = $territoryManager;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $dispatcher;
        $this->askManager = $askManager;
        $this->resultManager = $resultManager;
        $this->resultManager->setParams($params);
        $this->formatDataManager = $formatDataManager;
        $this->params = $params;
        $this->internalMessageManager = $internalMessageManager;
        $this->criteriaRepository = $criteriaRepository;
        $this->actionRepository = $actionRepository;
    }

    /**
     * Get a proposal by its id.
     *
     * @param integer $id       The id
     * @return Proposal|null    The proposal found or null
     */
    public function get(int $id)
    {
        return $this->proposalRepository->find($id);
    }

    /**
     * Get a proposal by its external id.
     *
     * @param string $id        The external id
     * @return Proposal|null    The proposal found or null
     */
    public function getFromExternalId(string $id)
    {
        return $this->proposalRepository->findOneBy(['externalId'=>$id]);
    }

    /**
     * Get the last unfinished dynamic ad for a user.
     *
     * @param User $user        The user
     * @return Proposal|null    The proposal found or null if not found
     */
    public function getLastDynamicUnfinished(User $user)
    {
        if ($lastUnfinishedProposal = $this->proposalRepository->findBy(['user'=>$user,'dynamic'=>true,'finished'=>false], ['createdDate'=>'DESC'], 1)) {
            return $lastUnfinishedProposal[0];
        }
        return null;
    }

    /**
     * Prepare a proposal for persist.
     * Used when posting a proposal to populate default values like proposal validity.
     *
     * @param Proposal $proposal
     * @return Proposal
     */
    public function prepareProposal(Proposal $proposal): Proposal
    {
        return $this->treatProposal($this->setDefaults($proposal), true, true);
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
                $proposal->getCriteria()->setMarginDuration($this->params['defaultMarginDuration']);
            }
        } else {
            if (is_null($proposal->getCriteria()->isStrictRegular())) {
                $proposal->getCriteria()->setStrictRegular($this->params['defaultStrictRegular']);
            }
            if (is_null($proposal->getCriteria()->getMonMarginDuration())) {
                $proposal->getCriteria()->setMonMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getTueMarginDuration())) {
                $proposal->getCriteria()->setTueMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getWedMarginDuration())) {
                $proposal->getCriteria()->setWedMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getThuMarginDuration())) {
                $proposal->getCriteria()->setThuMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getFriMarginDuration())) {
                $proposal->getCriteria()->setFriMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getSatMarginDuration())) {
                $proposal->getCriteria()->setSatMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getSunMarginDuration())) {
                $proposal->getCriteria()->setSunMarginDuration($this->params['defaultMarginDuration']);
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

            //  we dispatch gamification event associated
            if (!$proposal->isPrivate()) {
                $action = $this->actionRepository->findOneBy(['name'=>'carpool_ad_posted']);
                $actionEvent = new ActionEvent($action, $proposal->getUser());
                $actionEvent->setProposal($proposal);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
            }
        }

        // note : we sould not check here for the characteristics of the proposal BEFORE sending the event,
        // it should be the subscriber that determines on reception wether the event is useful or not...
        if (!$proposal->isPrivate() && !$proposal->isPaused()) {
            $matchings = array_merge($proposal->getMatchingOffers(), $proposal->getMatchingRequests());
            if ($persist) {
                foreach ($matchings as $matching) {
                    $event = new MatchingNewEvent($matching, $proposal->getUser(), $proposal->getType());
                    $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
                }
            }
        }

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
            if (!$waypoint->isReached()) {
                $addresses[] = $waypoint->getAddress();
            }
        }
        $routes = null;
        $direction = null;
        if ($proposal->getCriteria()->isDriver()) {
            if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                // for now we only keep the first route !
                // if we ever want alternative routes we should pass the route as parameter of this method
                // (problem : the route has no id, we should pass the whole route to check which route is chosen by the user...
                //      => we would have to think of a way to simplify...)
                $direction = $routes[0];
                // creation of the crossed zones
                //$direction = $this->zoneManager->createZonesForDirection($direction);
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->setDirectionDriver($direction);
                $proposal->getCriteria()->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::getMaxDetourDistancePercent()/100);
                $proposal->getCriteria()->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::getMaxDetourDurationPercent()/100);
            }
        }
        if ($proposal->getCriteria()->isPassenger()) {
            if ($routes && count($addresses)>2) {
                // if the user is passenger we keep only the first and last points
                if ($routes = $this->geoRouter->getRoutes([$addresses[0],$addresses[count($addresses)-1]], false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            } elseif (!$routes) {
                if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            }
            if ($direction) {
                if (is_null($direction->getBboxMinLon()) && is_null($direction->getBboxMinLat()) && is_null($direction->getBboxMaxLon()) && is_null($direction->getBboxMaxLat())) {
                    $direction->setBboxMaxLat($addresses[0]->getLatitude());
                    $direction->setBboxMaxLon($addresses[0]->getLongitude());
                    $direction->setBboxMinLat($addresses[0]->getLatitude());
                    $direction->setBboxMinLon($addresses[0]->getLongitude());
                }
                if ($routes) {
                    // creation of the crossed zones
                    //$direction = $this->zoneManager->createZonesForDirection($direction);
                    $direction->setAutoGeoJsonDetail();
                    $proposal->getCriteria()->setDirectionPassenger($direction);
                }
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
     * @param Proposal $proposal
     * @param array|null $body
     * @return Response
     * @throws \Exception
     */
    public function deleteProposal(Proposal $proposal, ?array $body = null)
    {
        $asks = $this->askManager->getAsksFromProposal($proposal);
        if (count($asks) > 0) {
            /** @var Ask $ask */
            foreach ($asks as $ask) {

                // todo : find why class of $ask can be a proxy of Ask class
                if (get_class($ask) !== Ask::class) {
                    continue;
                }

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




    /************
    *   DYNAMIC *
    *************/

    /**
     * Check if a user has a pending dynamic ad.
     *
     * @param User $user The user
     * @return boolean
     */
    public function hasPendingDynamic(User $user)
    {
        return count($this->proposalRepository->findBy(['user'=>$user,'dynamic'=>true,'active'=>true]))>0;
    }

    /**
     * Update matchings for a proposal
     *
     * @param Proposal  $proposal   The proposal to treat
     * @param Address $address      The current address
     * @return Proposal The treated proposal
     */
    public function updateMatchingsForProposal(Proposal $proposal, Address $address)
    {
        // set the directions
        $proposal = $this->updateDirection($proposal, $address);

        // matching analyze, but exclude the inactive proposals : can happen after an ask from a passenger to a driver
        if ($proposal->isActive()) {
            $proposal = $this->proposalMatcher->updateMatchingsForProposal($proposal);
        }

        return $proposal;
    }

    /**
     * Update the direction of a proposal, using the given address as origin.
     * Used for dynamic carpooling, to compute the remaining direction to the destination.
     * This kind of proposal should only have one role, but we will compute both eventually.
     *
     * @param Proposal $proposal    The proposal
     * @param Address $address      The current address
     * @return Proposal             The proposal with its updated direction
     */
    private function updateDirection(Proposal $proposal, Address $address)
    {
        // the first point is the current address
        $addresses = [$address];
        foreach ($proposal->getWaypoints() as $waypoint) {
            // we take all the waypoints but the first and the reached
            if (!$waypoint->isReached() && $waypoint->getPosition()>0) {
                $addresses[] = $waypoint->getAddress();
            }
        }
        $routes = null;
        if ($proposal->getCriteria()->isDriver()) {
            if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                // we update only some of the properties : distance, duration, ascend, descend, detail, format, snapped
                // bearing and bbox are not updated as they are computed for the whole original direction
                // (the current direction of the driver could not match with the passenger direction, whereas the whole directions could match)
                $direction = $routes[0];
                $direction->setSaveGeoJson(true);
                $direction->setDetailUpdatable(true);
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->getDirectionDriver()->setDistance($direction->getDistance());
                $proposal->getCriteria()->getDirectionDriver()->setDuration($direction->getDuration());
                $proposal->getCriteria()->getDirectionDriver()->setAscend($direction->getAscend());
                $proposal->getCriteria()->getDirectionDriver()->setDescend($direction->getDescend());
                //$proposal->getCriteria()->getDirectionDriver()->setDetail($direction->getDetail());
                $proposal->getCriteria()->getDirectionDriver()->setFormat($direction->getFormat());
                $proposal->getCriteria()->getDirectionDriver()->setSnapped($direction->getSnapped());
                $proposal->getCriteria()->getDirectionDriver()->setGeoJsonDetail($direction->getGeoJsonDetail());
                $proposal->getCriteria()->getDirectionDriver()->setGeoJsonSimplified($direction->getGeoJsonSimplified());
            }
        }
        if ($proposal->getCriteria()->isPassenger()) {
            if ($routes && count($addresses)>2) {
                // if the user is passenger we keep only the first and last points
                if ($routes = $this->geoRouter->getRoutes([$addresses[0],$addresses[count($addresses)-1]], false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            } elseif (!$routes) {
                if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            }
            if ($routes) {
                $direction->setSaveGeoJson(true);
                $direction->setDetailUpdatable(true);
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->getDirectionPassenger()->setDistance($direction->getDistance());
                $proposal->getCriteria()->getDirectionPassenger()->setDuration($direction->getDuration());
                $proposal->getCriteria()->getDirectionPassenger()->setAscend($direction->getAscend());
                $proposal->getCriteria()->getDirectionPassenger()->setDescend($direction->getDescend());
                //$proposal->getCriteria()->getDirectionPassenger()->setDetail($direction->getDetail());
                $proposal->getCriteria()->getDirectionPassenger()->setFormat($direction->getFormat());
                $proposal->getCriteria()->getDirectionPassenger()->setSnapped($direction->getSnapped());
                $proposal->getCriteria()->getDirectionPassenger()->setGeoJsonDetail($direction->getGeoJsonDetail());
                $proposal->getCriteria()->getDirectionPassenger()->setGeoJsonSimplified($direction->getGeoJsonSimplified());
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
        $this->logger->info('Start setDirectionsAndDefaultsForImport | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // we search the criterias that need calculation
        $criteriasFound = $this->criteriaRepository->findByUserImportStatus(UserImport::STATUS_USER_TREATED, new \DateTime());
        $this->setDirectionsAndDefaultsForCriterias($criteriasFound, $batch);
        $this->logger->info('End setDirectionsAndDefaultsForImport | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    /**
     * Set the directions and default values for all criterias.
     * Used for fixtures.
     *
     * @param integer $batch    The batch size
     * @return void
     */
    public function setDirectionsAndDefaultsForAllCriterias(int $batch)
    {
        $this->logger->info('Start setDirectionsAndDefaults | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // we search the criterias that need calculation
        $criteriasFound = $this->criteriaRepository->findAllForDirectionsAndDefault();
        $this->setDirectionsAndDefaultsForCriterias($criteriasFound, $batch);
        $this->logger->info('End setDirectionsAndDefaults | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    /**
     * Set the directions and default values for given criterias
     *
     * @param array $criterias  The criterias to look for
     * @param integer $batch    The batch size
     * @return void
     */
    private function setDirectionsAndDefaultsForCriterias(array $criterias, int $batch)
    {
        gc_enable();
        
        $addressesForRoutes = [];
        $owner = [];
        $ids = [];

        $i=0;

        $this->logger->info('setDirectionsAndDefaultsForCriterias | Start iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $criteriasTreated = [];
        foreach ($criterias as $key=>$criteria) {
            if (!array_key_exists($criteria['cid'], $criteriasTreated)) {
                $criteriasTreated[$criteria['cid']] = [
                    'cid'=>$criteria['cid'],
                    'driver'=>$criteria['driver'],
                    'passenger'=>$criteria['passenger'],
                    'addresses'=>[
                        [
                            'position'=>$criteria['position'],
                            'destination'=>$criteria['destination'],
                            'latitude'=>$criteria['latitude'],
                            'longitude'=>$criteria['longitude']
                        ]
                    ]
                ];
            } else {
                $element = [
                    'position'=>$criteria['position'],
                    'destination'=>$criteria['destination'],
                    'latitude'=>$criteria['latitude'],
                    'longitude'=>$criteria['longitude']
                ];
                if (!in_array($element, $criteriasTreated[$criteria['cid']]['addresses'])) {
                    $criteriasTreated[$criteria['cid']]['addresses'][] = $element;
                }
            }
        }

        foreach ($criteriasTreated as $criteria) {
            $addressesDriver = [];
            $addressesPassenger = [];
            foreach ($criteria['addresses'] as $waypoint) {
                // waypoints are already retrieved ordered by position, no need to check the position here
                if ($criteria['driver']) {
                    $address = new Address();
                    $address->setLatitude($waypoint['latitude']);
                    $address->setLongitude($waypoint['longitude']);
                    $addressesDriver[] = $address;
                }
                if ($criteria['passenger'] && ($waypoint['position'] == 0 || $waypoint['destination'])) {
                    $address = new Address();
                    $address->setLatitude($waypoint['latitude']);
                    $address->setLongitude($waypoint['longitude']);
                    $addressesPassenger[] = $address;
                }
            }
            if (count($addressesDriver)>0) {
                $addressesForRoutes[$i] = [$addressesDriver];
                $owner[$criteria['cid']]['driver'] = $i;
                $i++;
            }
            if (count($addressesPassenger)>0) {
                $addressesForRoutes[$i] = [$addressesPassenger];
                $owner[$criteria['cid']]['passenger'] = $i;
                $i++;
            }
            $ids[] = $criteria['cid'];
        }
        $this->logger->info('setDirectionsAndDefaultsForCriterias | End iterate at ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $this->logger->info('setDirectionsAndDefaultsForCriterias | Start get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_RAW);
        $this->logger->info('setDirectionsAndDefaultsForCriterias | End get routes status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $criteriasTreated = null;
        unset($criteriasTreated);

        if (count($ids)>0) {
            $qCriteria = $this->entityManager->createQuery('SELECT c from App\Carpool\Entity\Criteria c WHERE c.id IN (' . implode(',', $ids) . ')');

            $iterableResult = $qCriteria->iterate();
            $this->logger->info('Start treat rows ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
            $pool = 0;
            foreach ($iterableResult as $row) {
                $criteria = $row[0];
                // foreach ($criterias as $criteria) {
                if (isset($owner[$criteria->getId()]['driver']) && isset($ownerRoutes[$owner[$criteria->getId()]['driver']])) {
                    $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['driver']][0]);
                    //$direction = $this->zoneManager->createZonesForDirection($direction);
                    $direction->setSaveGeoJson(true);
                    $criteria->setDirectionDriver($direction);
                    $criteria->setMaxDetourDistance($direction->getDistance()*$this->proposalMatcher::getMaxDetourDistancePercent()/100);
                    $criteria->setMaxDetourDuration($direction->getDuration()*$this->proposalMatcher::getMaxDetourDurationPercent()/100);
                }
                if (isset($owner[$criteria->getId()]['passenger']) && isset($ownerRoutes[$owner[$criteria->getId()]['passenger']])) {
                    $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['passenger']][0]);
                    //$direction = $this->zoneManager->createZonesForDirection($direction);
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
                        $criteria->setMarginDuration($this->params['defaultMarginDuration']);
                    }
                } else {
                    if (is_null($criteria->isStrictRegular())) {
                        $criteria->setStrictRegular($this->params['defaultStrictRegular']);
                    }
                    if (is_null($criteria->getMonMarginDuration())) {
                        $criteria->setMonMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getTueMarginDuration())) {
                        $criteria->setTueMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getWedMarginDuration())) {
                        $criteria->setWedMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getThuMarginDuration())) {
                        $criteria->setThuMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getFriMarginDuration())) {
                        $criteria->setFriMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getSatMarginDuration())) {
                        $criteria->setSatMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getSunMarginDuration())) {
                        $criteria->setSunMarginDuration($this->params['defaultMarginDuration']);
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
        }

        $this->logger->info('End update status ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    /**
     * Create matchings for all proposals at once
     *
     * @return void
     */
    public function createMatchingsForAllProposals()
    {
        // we create an array of all proposals without matchings to treat
        $proposalIds = $this->proposalRepository->findAllValidWithoutMatchingsProposalIds();
        $this->logger->info('Start creating candidates | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $this->proposalMatcher->findPotentialMatchingsForProposals($proposalIds, false);
        $this->logger->info('End creating candidates | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // treat the return and opposite
        $this->createLinkedAndOppositesForProposals($proposalIds);
    }

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
                $this->matchingRepository->linkRelatedMatchings($proposalId['id']);
            }
            // if the requester can be driver and passenger, we want to link the potential opposite matching results
            if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                // linking for the outward
                $this->matchingRepository->linkOppositeMatchings($proposalId['id']);
                if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
                    // linking for the return
                    $this->matchingRepository->linkOppositeMatchings($proposal->getProposalLinked()->getId());
                }
            }
        }
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
