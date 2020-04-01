<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Event\AdMajorUpdatedEvent;
use App\Carpool\Event\AdMinorUpdatedEvent;
use App\Community\Exception\CommunityNotFoundException;
use App\Community\Service\CommunityManager;
use App\Event\Exception\EventNotFoundException;
use App\Event\Service\EventManager;
use App\Geography\Entity\Address;
use App\Carpool\Exception\AdException;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Repository\CriteriaRepository;
use App\User\Exception\UserNotFoundException;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Rdex\Entity\RdexError;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Ad manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class AdManager
{
    private $entityManager;
    private $proposalManager;
    private $userManager;
    private $communityManager;
    private $eventManager;
    private $resultManager;
    private $params;
    private $logger;
    private $proposalRepository;
    private $criteriaRepository;
    private $proposalMatcher;
    private $askManager;
    private $eventDispatcher;
    private $security;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalManager $proposalManager, UserManager $userManager, CommunityManager $communityManager, EventManager $eventManager, ResultManager $resultManager, LoggerInterface $logger, array $params, ProposalRepository $proposalRepository, CriteriaRepository $criteriaRepository, ProposalMatcher $proposalMatcher, AskManager $askManager, EventDispatcherInterface $eventDispatcher, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->resultManager = $resultManager;
        $this->logger = $logger;
        $this->params = $params;
        $this->proposalRepository = $proposalRepository;
        $this->criteriaRepository = $criteriaRepository;
        $this->proposalMatcher = $proposalMatcher;
        $this->askManager = $askManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
    }

    /**
     * Create an ad.
     * This method creates a proposal, and its linked proposal for a return trip.
     * It returns the ad created, with its outward and return results.
     *
     * @param Ad $ad The ad to create
     * @param bool $fromUpdate - When we create an Ad in update case, waypoints are Object and not Array
     * @return Ad
     * @throws \Exception
     */
    public function createAd(Ad $ad, bool $fromUpdate = false)
    {
        $this->logger->info("AdManager : start " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        $outwardProposal = new Proposal();
        $outwardCriteria = new Criteria();

        // validation

        // try for an anonymous post ?
        if (!$ad->isSearch() && !$ad->getUserId()) {
            throw new AdException('Anonymous users can\'t post an ad');
        }

        // we set the user of the proposal
        if ($ad->getUserId()) {
            if ($user = $this->userManager->getUser($ad->getUserId())) {
                $outwardProposal->setUser($user);
            } else {
                throw new UserNotFoundException('User ' . $ad->getUserId() . ' not found');
            }
        }
        
        // we check if the ad is posted for another user (delegation)
        if ($ad->getPosterId()) {
            if ($poster = $this->userManager->getUser($ad->getPosterId())) {
                $outwardProposal->setUserDelegate($poster);
            } else {
                throw new UserNotFoundException('Poster ' . $ad->getPosterId() . ' not found');
            }
        }

        // the proposal is private if it's a search only ad
        $outwardProposal->setPrivate($ad->isSearch() ? true : false);

        // If the proposal is external (i.e Rdex request...) we set it
        $outwardProposal->setExternal($ad->getExternal());

        // we check if it's a round trip
        if ($ad->isOneWay()) {
            // the ad has explicitly been set to one way
            $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
        } elseif (is_null($ad->isOneWay())) {
            // the ad type has not been set, we assume it's a round trip for a regular trip and a one way for a punctual trip
            if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $ad->setOneWay(false);
                $outwardProposal->setType(Proposal::TYPE_OUTWARD);
            } else {
                $ad->setOneWay(true);
                $outwardProposal->setType(Proposal::TYPE_ONE_WAY);
            }
        } else {
            $outwardProposal->setType(Proposal::TYPE_OUTWARD);
        }

        // comment
        $outwardProposal->setComment($ad->getComment());

        // communities
        if ($ad->getCommunities()) {
            // todo : check if the user can post/search in each community
            foreach ($ad->getCommunities() as $communityId) {
                if ($community = $this->communityManager->getCommunity($communityId)) {
                    $outwardProposal->addCommunity($community);
                } else {
                    throw new CommunityNotFoundException('Community ' . $communityId . ' not found');
                }
            }
        }

        // event
        if ($ad->getEventId()) {
            if ($event = $this->eventManager->getEvent($ad->getEventId())) {
                $outwardProposal->setEvent($event);
            } else {
                throw new EventNotFoundException('Event ' . $ad->getEventId() . ' not found');
            }
        }
        
        // criteria

        // driver / passenger / seats
        $outwardCriteria->setDriver($ad->getRole() == Ad::ROLE_DRIVER || $ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER);
        $outwardCriteria->setPassenger($ad->getRole() == Ad::ROLE_PASSENGER || $ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER);
        $outwardCriteria->setSeatsDriver($ad->getSeatsDriver() ? $ad->getSeatsDriver() : $this->params['defaultSeatsDriver']);
        $outwardCriteria->setSeatsPassenger($ad->getSeatsPassenger() ? $ad->getSeatsPassenger() : $this->params['defaultSeatsPassenger']);

        // solidary
        $outwardCriteria->setSolidary($ad->isSolidary());
        $outwardCriteria->setSolidaryExclusive($ad->isSolidaryExclusive());

        // prices
        $outwardCriteria->setPriceKm($ad->getPriceKm());
        $outwardCriteria->setDriverPrice($ad->getOutwardDriverPrice());
        $outwardCriteria->setPassengerPrice($ad->getOutwardPassengerPrice());

        // strict
        $outwardCriteria->setStrictDate($ad->isStrictDate());
        $outwardCriteria->setStrictPunctual($ad->isStrictPunctual());
        $outwardCriteria->setStrictRegular($ad->isStrictRegular());

        // misc
        $outwardCriteria->setLuggage($ad->hasLuggage());
        $outwardCriteria->setBike($ad->hasBike());
        $outwardCriteria->setBackSeats($ad->hasBackSeats());

        // dates and times

        // if the date is not set we use the current date
        $outwardCriteria->setFromDate($ad->getOutwardDate() ? $ad->getOutwardDate() : new \DateTime());
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $outwardCriteria->setToDate($ad->getOutwardLimitDate() ? $ad->getOutwardLimitDate() : null);
            $outwardCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $outwardCriteria, $fromUpdate);
            $hasSchedule = $outwardCriteria->isMonCheck() || $outwardCriteria->isTueCheck()
                || $outwardCriteria->isWedCheck() || $outwardCriteria->isFriCheck() || $outwardCriteria->isThuCheck()
                || $outwardCriteria->isSatCheck() || $outwardCriteria->isSunCheck();
            if (!$hasSchedule && !$ad->isSearch()) {
                // for a post, we need aschedule !
                throw new AdException('At least one day should be selected for a regular trip');
            } elseif (!$hasSchedule) {
                // for a search we set the schedule to every day
                $outwardCriteria->setMonCheck(true);
                $outwardCriteria->setMonMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setTueCheck(true);
                $outwardCriteria->setTueMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setWedCheck(true);
                $outwardCriteria->setWedMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setThuCheck(true);
                $outwardCriteria->setThuMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setFriCheck(true);
                $outwardCriteria->setFriMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setSatCheck(true);
                $outwardCriteria->setSatMarginDuration($this->params['defaultMarginTime']);
                $outwardCriteria->setSunCheck(true);
                $outwardCriteria->setSunMarginDuration($this->params['defaultMarginTime']);
            }
        } else {
            // punctual
            $outwardCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            // if the time is not set we use the current time for an ad post, and null for a search
            $format = $fromUpdate ? 'Y-m-d H:i:s' : 'H:i';
            $outwardCriteria->setFromTime($ad->getOutwardTime() ? \DateTime::createFromFormat($format, $ad->getOutwardTime()) : (!$ad->isSearch() ? new \DateTime() : null));
            $outwardCriteria->setMarginDuration($this->params['defaultMarginTime']);
        }

        // waypoints
        foreach ($ad->getOutwardWaypoints() as $position => $point) {
            $waypoint = new Waypoint();
            $waypoint->setAddress($this->createAddressFromPoint($point, $fromUpdate));
            $waypoint->setPosition($position);
            $waypoint->setDestination($position == count($ad->getOutwardWaypoints())-1);
            $outwardProposal->addWaypoint($waypoint);
        }

        $outwardProposal->setCriteria($outwardCriteria);
        $outwardProposal = $this->proposalManager->prepareProposal($outwardProposal);

        $this->logger->info("AdManager : end creating outward " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        //$this->entityManager->persist($outwardProposal);

        $this->logger->info("AdManager : end persisting outward " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // return trip ?
        if (!$ad->isOneWay()) {
            // we clone the outward proposal
            $returnProposal = clone $outwardProposal;
            $returnProposal->setType(Proposal::TYPE_RETURN);
            
            // we link the outward and the return
            $outwardProposal->setProposalLinked($returnProposal);

            // criteria
            $returnCriteria = new Criteria();

            // driver / passenger / seats
            $returnCriteria->setDriver($outwardCriteria->isDriver());
            $returnCriteria->setPassenger($outwardCriteria->isPassenger());
            $returnCriteria->setSeatsDriver($outwardCriteria->getSeatsDriver());
            $returnCriteria->setSeatsPassenger($outwardCriteria->getSeatsPassenger());

            // solidary
            $returnCriteria->setSolidary($outwardCriteria->isSolidary());
            $returnCriteria->setSolidaryExclusive($outwardCriteria->isSolidaryExclusive());

            // prices
            $returnCriteria->setPriceKm($outwardCriteria->getPriceKm());
            $returnCriteria->setDriverPrice($ad->getReturnDriverPrice());
            $returnCriteria->setPassengerPrice($ad->getReturnPassengerPrice());

            // strict
            $returnCriteria->setStrictDate($outwardCriteria->isStrictDate());
            $returnCriteria->setStrictPunctual($outwardCriteria->isStrictPunctual());
            $returnCriteria->setStrictRegular($outwardCriteria->isStrictRegular());

            // misc
            $returnCriteria->setLuggage($outwardCriteria->hasLuggage());
            $returnCriteria->setBike($outwardCriteria->hasBike());
            $returnCriteria->setBackSeats($outwardCriteria->hasBackSeats());

            // dates and times
            // if no return date is specified, we use the outward date to be sure the return date is not before the outward date
            $returnCriteria->setFromDate($ad->getReturnDate() ? $ad->getReturnDate() : $outwardCriteria->getFromDate());
            if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $returnCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
                $returnCriteria->setToDate($ad->getReturnLimitDate() ? $ad->getReturnLimitDate() : null);
                $returnCriteria = $this->createTimesFromSchedule($ad->getSchedule(), $outwardCriteria, $fromUpdate);
                $hasSchedule = $returnCriteria->isMonCheck() || $returnCriteria->isTueCheck()
                    || $returnCriteria->isWedCheck() || $returnCriteria->isFriCheck() || $returnCriteria->isThuCheck()
                    || $returnCriteria->isSatCheck() || $returnCriteria->isSunCheck();
                if (!$hasSchedule && !$ad->isSearch()) {
                    // for a post, we need a schedule !
                    throw new AdException('At least one day should be selected for a regular trip');
                } elseif (!$hasSchedule) {
                    // for a search we set the schedule to every day
                    $returnCriteria->setMonCheck(true);
                    $returnCriteria->setMonMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setTueCheck(true);
                    $returnCriteria->setTueMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setWedCheck(true);
                    $returnCriteria->setWedMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setThuCheck(true);
                    $returnCriteria->setThuMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setFriCheck(true);
                    $returnCriteria->setFriMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setSatCheck(true);
                    $returnCriteria->setSatMarginDuration($this->params['defaultMarginTime']);
                    $returnCriteria->setSunCheck(true);
                    $returnCriteria->setSunMarginDuration($this->params['defaultMarginTime']);
                }
            } else {
                // punctual
                $returnCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                // if no return time is specified, we use the outward time to be sure the return date is not before the outward date, and null for a search
                $returnCriteria->setFromTime($ad->getReturnTime() ? \DateTime::createFromFormat('H:i', $ad->getReturnTime()) : (!$ad->isSearch() ? $outwardCriteria->getFromTime() : null));
                $returnCriteria->setMarginDuration($this->params['defaultMarginTime']);
            }

            // waypoints
            if (count($ad->getReturnWaypoints())==0) {
                // return waypoints are not set : we use the outward waypoints in reverse order
                $ad->setReturnWaypoints(array_reverse($ad->getOutwardWaypoints()));
            }
            foreach ($ad->getReturnWaypoints() as $position => $point) {
                $waypoint = new Waypoint();
                $waypoint->setAddress($this->createAddressFromPoint($point, $fromUpdate));
                $waypoint->setPosition($position);
                $waypoint->setDestination($position == count($ad->getReturnWaypoints())-1);
                $returnProposal->addWaypoint($waypoint);
            }

            $returnProposal->setCriteria($returnCriteria);
            $returnProposal = $this->proposalManager->prepareProposal($returnProposal, false);
            $this->entityManager->persist($returnProposal);
        }
        // we persist the proposals
        $this->logger->info("AdManager : start flush proposal " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $this->entityManager->flush();
        $this->logger->info("AdManager : end flush proposal " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        // if the ad is a round trip, we want to link the potential matching results
        if (!$ad->isOneWay()) {
            $outwardProposal = $this->proposalManager->linkRelatedMatchings($outwardProposal);
            $this->entityManager->persist($outwardProposal);
            $this->entityManager->flush();
        }
        // if the requester can be driver and passenger, we want to link the potential opposite matching results
        if ($ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER) {
            // linking for the outward
            $outwardProposal = $this->proposalManager->linkOppositeMatchings($outwardProposal);
            $this->entityManager->persist($outwardProposal);
            if (!$ad->isOneWay()) {
                // linking for the return
                $returnProposal = $this->proposalManager->linkOppositeMatchings($returnProposal);
                $this->entityManager->persist($returnProposal);
            }
            $this->entityManager->flush();
        }

        // we compute the results
        
        // default order
        $ad->setFilters([
                'order'=>[
                    'criteria'=>'date',
                    'value'=>'ASC'
                ]
            
        ]);

        $this->logger->info("AdManager : start set results " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ad->setResults(
            $this->resultManager->orderResults(
                $this->resultManager->filterResults(
                    $this->resultManager->createAdResults($outwardProposal),
                    $ad->getFilters()
                ),
                $ad->getFilters()
            )
        );
        $this->logger->info("AdManager : end set results " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        
        // we set the ad id to the outward proposal id
        $ad->setId($outwardProposal->getId());
        return $ad;
    }

    /**
     * Map address
     *
     * @param Waypoint|array $point
     * @param bool $isObject
     * @return Address
     */
    public function createAddressFromPoint($point, bool $isObject = false)
    {
        $address = new Address();

        if (!$isObject) {
            if (isset($point['houseNumber'])) {
                $address->setHouseNumber($point['houseNumber']);
            }
            if (isset($point['street'])) {
                $address->setStreet($point['street']);
            }
            if (isset($point['streetAddress'])) {
                $address->setStreetAddress($point['streetAddress']);
            }
            if (isset($point['postalCode'])) {
                $address->setPostalCode($point['postalCode']);
            }
            if (isset($point['subLocality'])) {
                $address->setSubLocality($point['subLocality']);
            }
            if (isset($point['addressLocality'])) {
                $address->setAddressLocality($point['addressLocality']);
            }
            if (isset($point['localAdmin'])) {
                $address->setLocalAdmin($point['localAdmin']);
            }
            if (isset($point['county'])) {
                $address->setCounty($point['county']);
            }
            if (isset($point['macroCounty'])) {
                $address->setMacroCounty($point['macroCounty']);
            }
            if (isset($point['region'])) {
                $address->setRegion($point['region']);
            }
            if (isset($point['macroRegion'])) {
                $address->setMacroRegion($point['macroRegion']);
            }
            if (isset($point['addressCountry'])) {
                $address->setAddressCountry($point['addressCountry']);
            }
            if (isset($point['countryCode'])) {
                $address->setCountryCode($point['countryCode']);
            }
            if (isset($point['latitude'])) {
                $address->setLatitude($point['latitude']);
            }
            if (isset($point['longitude'])) {
                $address->setLongitude($point['longitude']);
            }
            if (isset($point['elevation'])) {
                $address->setElevation($point['elevation']);
            }
            if (isset($point['name'])) {
                $address->setName($point['name']);
            }
            if (isset($point['home'])) {
                $address->setHome($point['home']);
            }
//            foreach ($point as $key => $value) {
//                $setter = Deserializer::SETTER_PREFIX.ucwords($key);
//                if (isset($point[$key])) {
//                    $address->$setter($value);
//                }
//            }
        } else {
            $address = clone $point->getAddress();
        }

        return $address;
    }

    /**
     * @param $schedule
     * @param bool $isObject
     */
    public function createTimesFromSchedule($schedules, Criteria $criteria, bool $isObject)
    {
//        if ($fromUpdate) {
//
//        }
        foreach ($schedules as $schedule) {
            if ($schedule['outwardTime'] != '') {
                if (isset($schedule['mon']) && $schedule['mon']) {
                    $hasSchedule = true;
                    $criteria->setMonCheck(true);
                    $criteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setMonMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['tue']) && $schedule['tue']) {
                    $hasSchedule = true;
                    $criteria->setTueCheck(true);
                    $criteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setTueMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['wed']) && $schedule['wed']) {
                    $hasSchedule = true;
                    $criteria->setWedCheck(true);
                    $criteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setWedMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['thu']) && $schedule['thu']) {
                    $hasSchedule = true;
                    $criteria->setThuCheck(true);
                    $criteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setThuMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['fri']) && $schedule['fri']) {
                    $hasSchedule = true;
                    $criteria->setFriCheck(true);
                    $criteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setFriMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['sat']) && $schedule['sat']) {
                    $hasSchedule = true;
                    $criteria->setSatCheck(true);
                    $criteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setSatMarginDuration($this->params['defaultMarginTime']);
                }
                if (isset($schedule['sun']) && $schedule['sun']) {
                    $hasSchedule = true;
                    $criteria->setSunCheck(true);
                    $criteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                    $criteria->setSunMarginDuration($this->params['defaultMarginTime']);
                }
            }
        }

        return $criteria;
    }

    /**
     * Get an ad.
     * Returns the ad, with its outward and return results.
     *
     * @param int $id       The ad id to get
     * @param array|null    The filters to apply to the results
     * @param array|null    The order to apply to the results
     * @return Ad
     */
    public function getAd(int $id, ?array $filters = null, ?array $order = null)
    {
        $ad = new Ad();
        $proposal = $this->proposalManager->get($id);
        if (is_null($proposal)) {
            return null;
        }

        $ad->setId($id);
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ?  ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setPaused($proposal->isPaused());
        if (!is_null($proposal->getUser())) {
            $ad->setUserId($proposal->getUser()->getId());
        }
        $aFilters = [];
        if (!is_null($filters)) {
            $aFilters['filters']=$filters;
        }
        if (!is_null($order)) {
            $aFilters['order']=$order;
        }
        $ad->setFilters($aFilters);
        $this->logger->info("AdManager : start set results " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ad->setResults(
            $this->resultManager->orderResults(
                $this->resultManager->filterResults(
                    $this->resultManager->createAdResults($proposal),
                    $ad->getFilters()
                ),
                $ad->getFilters()
            )
        );
        $this->logger->info("AdManager : end set results " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        return $ad;
    }

    /**
     * Get an ad with full data.
     * Returns the ad, with its outward and return results.
     *
     * @param int $id       The ad id to get
     * @return Ad
     */
    public function getFullAd(int $id)
    {
        $proposal = $this->proposalManager->get($id);
        return $this->makeAd($proposal, $proposal->getUser()->getId());
    }

    /**
     * Get an ad for permission check.
     * Returns the ad based on the proposal without results.
     *
     * @param int $id       The ad id to get
     * @return Ad|null
     */
    public function getAdForPermission(int $id)
    {
        $ad = new Ad();
        if ($proposal = $this->proposalManager->get($id)) {
            $ad->setId($id);
            $ad->setFrequency($proposal->getCriteria()->getFrequency());
            $ad->setRole($proposal->getCriteria()->isDriver() ?  ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
            $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
            $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
            if (!is_null($proposal->getUser())) {
                $ad->setUserId($proposal->getUser()->getId());
            }
            return $ad;
        }
        return null;
    }

    /**
     * Get all ads of a user
     *
     * @param integer $userId
     * @return array
     */
    public function getAds(int $userId)
    {
        $ads = [];
        $user = $this->userManager->getUser($userId);
        $proposals = $this->proposalRepository->findBy(['user'=>$user, 'private'=>false]);
        
        $refIdProposals = [];
        foreach ($proposals as $proposal) {
            if (!in_array($proposal->getId(), $refIdProposals)) {
                $ads[] = $this->makeAd($proposal, $userId);
                if (!is_null($proposal->getProposalLinked())) {
                    $refIdProposals[$proposal->getId()] = $proposal->getProposalLinked()->getId();
                }
            }
        }
        return $ads;
    }


    /**
     * Get all ads of a Community
     *
     * @param integer $communityId Id of the Community
     * @return void
     */
    public function getAdsOfCommunity(int $communityId)
    {
        $ads = [];
        $community = $this->communityManager->getCommunity($communityId);
        
        
        $refIdProposals = [];
        foreach ($community->getProposals() as $proposal) {
            if (!in_array($proposal->getId(), $refIdProposals) && !$proposal->isPrivate()) {
                // we check if the proposal is still valid if yes we retrieve the proposal
                $LimitDate = $proposal->getCriteria()->getToDate() ? $proposal->getCriteria()->getToDate() : $proposal->getCriteria()->getFromDate();
                if ($LimitDate >= new \DateTime()) {
                    $ads[] = $this->makeAdForCommunityOrEvent($proposal);
                    if (!is_null($proposal->getProposalLinked())) {
                        $refIdProposals[$proposal->getId()] = $proposal->getProposalLinked()->getId();
                    }
                }
            }
        }
        return $ads;
    }

    /**
     * Get all ads of an Event
     *
     * @param integer $eventId Id of the Event
     * @return void
     */
    public function getAdsOfEvent(int $eventId)
    {
        $ads = [];
        $event = $this->eventManager->getEvent($eventId);
        
        
        $refIdProposals = [];
        foreach ($event->getProposals() as $proposal) {
            if (!in_array($proposal->getId(), $refIdProposals) && !$proposal->isPrivate()) {
                $ads[] = $this->makeAdForCommunityOrEvent($proposal);
                if (!is_null($proposal->getProposalLinked())) {
                    $refIdProposals[$proposal->getId()] = $proposal->getProposalLinked()->getId();
                }
            }
        }
        return $ads;
    }


    /**
     * Make an ad from a proposal
     *
     * @param Proposal $proposal The base proposal of the ad
     * @param integer $userId The userId who made the proposal
     * @param bool $hasAsks - if the ad has ask we do not return results since we return the ask with the ad
     * @param Ad $askLinked - the linked ask if proposal is private and get the correct data for Ad (like time and day checks)
     * @param Matching $matching - the corresponding Matching
     * @return Ad
     */
    private function makeAd($proposal, $userId, $hasAsks = false, ?Ad $askLinked = null, ?Matching $matching = null)
    {
        $ad = new Ad();
        $ad->setId($proposal->getId());
        $ad->setProposalId($proposal->getId());
        $ad->setProposalLinkedId(!is_null($proposal->getProposalLinked()) ? $proposal->getProposalLinked()->getId() : null);
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ?  ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setUserId($userId);
        $ad->setOutwardWaypoints($proposal->getWaypoints());
        $ad->setPaused($proposal->isPaused());
        $ad->setOutwardDriverPrice($proposal->getCriteria()->getDriverComputedRoundedPrice());
        $ad->setBike($proposal->getCriteria()->hasBike());
        $ad->setLuggage($proposal->getCriteria()->hasLuggage());
        $ad->setBackSeats($proposal->getCriteria()->hasBackSeats());
        $ad->setComment($proposal->getComment());
        $ad->setPriceKm($proposal->getCriteria()->getPriceKm());

        if ($matching && $matching->getProposalOffer()->getCriteria()->getFromTime()) {
            $date = $matching->getProposalOffer()->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$matching->getProposalOffer()->getCriteria()->getFromTime()->format('H:i:s'));
        } elseif ($matching && $matching->getProposalRequest()->getCriteria()->getFromTime()) {
            $date = $matching->getProposalRequest()->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$matching->getProposalRequest()->getCriteria()->getFromTime()->format('H:i:s'));
        } elseif ($proposal->getCriteria()->getFromTime()) {
            $date = $proposal->getCriteria()->getFromDate();
            $ad->setOutwardDate($date);
            $ad->setOutwardTime($date->format('Y-m-d').' '.$proposal->getCriteria()->getFromTime()->format('H:i:s'));
        } else {
            $ad->setOutwardDate($proposal->getCriteria()->getFromDate());
            $ad->setOutwardTime(null);
        }

        $ad->setOutwardLimitDate($askLinked ? $askLinked->getOutwardLimitDate() : $proposal->getCriteria()->getToDate());
        $ad->setOneWay(true);
        $ad->setSolidary($proposal->getCriteria()->isSolidary());
        $ad->setSolidaryExclusive($proposal->getCriteria()->isSolidaryExclusive());

        
        // set return if twoWays ad
        if ($proposal->getProposalLinked()) {
            $ad->setReturnWaypoints($proposal->getProposalLinked()->getWaypoints());
            $ad->setReturnDate($proposal->getProposalLinked()->getCriteria()->getFromDate());
            
            if ($proposal->getProposalLinked()->getCriteria()->getFromTime()) {
                $ad->setReturnTime($proposal->getProposalLinked()->getCriteria()->getFromTime()->format('H:i'));
            } else {
                $ad->setReturnTime(null);
            }



            $ad->setReturnLimitDate($proposal->getProposalLinked()->getCriteria()->getToDate());
            $ad->setOneWay(false);
        }

        // set schedule if regular
        $schedule = [];
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            // schedule needs data in asks results when the user that display the Ad is not the owner
            $schedule = $askLinked
               ? $this->getScheduleFromResults($askLinked->getResults()[0], $proposal)
               : $this->getScheduleFromCriteria($proposal->getCriteria(), $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria() : null);
            // if schedule is based on results, we do not need to update pickup times because it's already done in results
            if ($ad->getRole() === Ad::ROLE_PASSENGER && !is_null($matching) && $matching->getPickUpDuration() && !$askLinked) {
                $schedule = $this->updateScheduleTimesWithPickUpDurations($schedule, $matching->getPickUpDuration(), $matching->getMatchingLinked() ? $matching->getMatchingLinked()->getPickUpDuration() : null);
            }
        }
        $ad->setSchedule($schedule);
        $results = $this->resultManager->createAdResults($proposal);
        $ad->setPotentialCarpoolers(count($results));

        if (!$hasAsks) {
            $ad->setResults($results);
        }
        return $ad;
    }

    public function getScheduleFromCriteria(Criteria $criteria, ?Criteria $returnCriteria = null)
    {
        // we clean up every days based on isDayCheck
        $schedule['mon'] = $criteria->isMonCheck() || ($returnCriteria ? $returnCriteria->isMonCheck() : false);
        $schedule['monOutwardTime'] = $criteria->isMonCheck() ? $criteria->getMonTime() : null;
        $schedule['monReturnTime'] = $returnCriteria && $returnCriteria->isMonCheck() ? $returnCriteria->getMonTime() : null;

        $schedule['tue'] = $criteria->isTueCheck() || ($returnCriteria ? $returnCriteria->isTueCheck() : false);
        $schedule['tueOutwardTime'] = $criteria->isTueCheck() ? $criteria->getTueTime() : null;
        $schedule['tueReturnTime'] = $returnCriteria && $returnCriteria->isTueCheck() ? $returnCriteria->getTueTime() : null;

        $schedule['wed'] = $criteria->isWedCheck() || ($returnCriteria ? $returnCriteria->isWedCheck() : false);
        $schedule['wedOutwardTime'] = $criteria->isWedCheck() ? $criteria->getWedTime() : null;
        $schedule['wedReturnTime'] = $returnCriteria && $returnCriteria->isWedCheck() ? $returnCriteria->getWedTime() : null;

        $schedule['thu'] = $criteria->isThuCheck() || ($returnCriteria ? $returnCriteria->isThuCheck() : false);
        $schedule['thuOutwardTime'] = $criteria->isThuCheck() ? $criteria->getThuTime() : null;
        $schedule['thuReturnTime'] = $returnCriteria && $returnCriteria->isThuCheck() ? $returnCriteria->getThuTime() : null;

        $schedule['fri'] = $criteria->isFriCheck() || ($returnCriteria ? $returnCriteria->isFriCheck() : false);
        $schedule['friOutwardTime'] = $criteria->isFriCheck() ? $criteria->getFriTime() : null;
        $schedule['friReturnTime'] = $returnCriteria && $returnCriteria->isFriCheck() ? $returnCriteria->getFriTime() : null;

        $schedule['sat'] = $criteria->isSatCheck() || ($returnCriteria ? $returnCriteria->isSatCheck() : false);
        $schedule['satOutwardTime'] = $criteria->isSatCheck() ? $criteria->getSatTime() : null;
        $schedule['satReturnTime'] = $returnCriteria && $returnCriteria->isSatCheck() ? $returnCriteria->getSatTime() : null;

        $schedule['sun'] = $criteria->isSunCheck() || ($returnCriteria ? $returnCriteria->isSunCheck() : false);
        $schedule['sunOutwardTime'] = $criteria->isSunCheck() ? $criteria->getSunTime() : null;
        $schedule['sunReturnTime'] = $returnCriteria && $returnCriteria->isSunCheck() ? $returnCriteria->getSunTime() : null;

        return $schedule;
    }

    public function getScheduleFromResults(Result $results, Proposal $proposal)
    {
        if (!$proposal->getCriteria()->isDriver() && $results->getResultDriver()) {
            $outward = $results->getResultDriver()->getOutward();
            $return = $results->getResultDriver()->getReturn();
        } elseif (!$proposal->getCriteria()->isPassenger() && $results->getResultPassenger()) {
            $outward = $results->getResultPassenger()->getOutward();
            $return = $results->getResultPassenger()->getReturn();
        } else {
            return [];
        }

        // we clean up every days based on isDayCheck
        $schedule['mon'] = $outward->isMonCheck() || ($return ? $return->isMonCheck() : null);
        $schedule['monOutwardTime'] = $outward->isMonCheck() ? $outward->getMonTime() : null;
        $schedule['monReturnTime'] = $return && $return->isMonCheck() ? $return->getMonTime() : null;

        $schedule['tue'] = $outward->isTueCheck() || ($return ? $return->isTueCheck() : null);
        $schedule['tueOutwardTime'] = $outward->isTueCheck() ? $outward->getTueTime() : null;
        $schedule['tueReturnTime'] = $return && $return->isTueCheck() ? $return->getTueTime() : null;

        $schedule['wed'] = $outward->isWedCheck() || ($return ? $return->isWedCheck() : null);
        $schedule['wedOutwardTime'] = $outward->isWedCheck() ? $outward->getWedTime() : null;
        $schedule['wedReturnTime'] = $return && $return->isWedCheck() ? $return->getWedTime() : null;

        $schedule['thu'] = $outward->isThuCheck() || ($return ? $return->isThuCheck() : null);
        $schedule['thuOutwardTime'] = $outward->isThuCheck() ? $outward->getThuTime() : null;
        $schedule['thuReturnTime'] = $return && $return->isThuCheck() ? $return->getThuTime() : null;

        $schedule['fri'] = $outward->isFriCheck() || ($return ? $return->isFriCheck() : null);
        $schedule['friOutwardTime'] = $outward->isFriCheck() ? $outward->getFriTime() : null;
        $schedule['friReturnTime'] = $return && $return->isFriCheck() ? $return->getFriTime() : null;

        $schedule['sat'] = $outward->isSatCheck() || ($return ? $return->isSatCheck() : null);
        $schedule['satOutwardTime'] = $outward->isSatCheck() ? $outward->getSatTime() : null;
        $schedule['satReturnTime'] = $return && $return->isSatCheck() ? $return->getSatTime() : null;

        $schedule['sun'] = $outward->isSunCheck() || ($return ? $return->isSunCheck() : null);
        $schedule['sunOutwardTime'] = $outward->isSunCheck() ? $outward->getSunTime() : null;
        $schedule['sunReturnTime'] = $return && $return->isSunCheck() ? $return->getSunTime() : null;

        return $schedule;
    }

    /**
     * Update a Schedule with pick up durations from a Matching
     * Used when the Ad role is passenger
     * @param array $schedule
     * @param string $outwardPickUpDuration
     * @param string|null $returnPickUpDuration
     * @return array
     * @throws \Exception
     */
    public function updateScheduleTimesWithPickUpDurations(array $schedule, string $outwardPickUpDuration, ?string $returnPickUpDuration = null)
    {
        $days = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];

        foreach ($days as $day) {
            if ($schedule[$day . "OutwardTime"]) {
                $schedule[$day . "OutwardTime"] =  $schedule[$day . "OutwardTime"]->add(new \DateInterval('PT' . $outwardPickUpDuration . 'S'));
            }
            if ($schedule[$day . "ReturnTime"] && $returnPickUpDuration) {
                $schedule[$day . "ReturnTime"] = $schedule[$day . "ReturnTime"]->add(new \DateInterval('PT' . $returnPickUpDuration . 'S'));
            }
        }

        return $schedule;
    }

    /**
     * make an ad from a proposal
     *
     * @param Proposal $proposal Base Proposal of the Ad
     * @return Ad
     */
    private function makeAdForCommunityOrEvent(Proposal $proposal)
    {
        $ad = new Ad();
        $ad->setId($proposal->getId());
        $ad->setUser($proposal->getUser());
        $ad->setFrequency($proposal->getCriteria()->getFrequency());
        $ad->setRole($proposal->getCriteria()->isDriver() ?  ($proposal->getCriteria()->isPassenger() ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);
        $ad->setSeatsDriver($proposal->getCriteria()->getSeatsDriver());
        $ad->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger());
        $ad->setOutwardWaypoints($proposal->getWaypoints());
        $ad->setOutwardDate($proposal->getCriteria()->getFromDate());
        $ad->setPaused($proposal->isPaused());
        if ($proposal->getCriteria()->getFromTime()) {
            $ad->setOutwardTime($ad->getOutwardDate()->format('Y-m-d').' '.$proposal->getCriteria()->getFromTime()->format('H:i:s'));
        } else {
            $ad->setOutwardTime(null);
        }
        $ad->setOutwardLimitDate($proposal->getCriteria()->getToDate());
        $ad->setOneWay(true);
        $ad->setSolidary($proposal->getCriteria()->isSolidary());
        $ad->setSolidaryExclusive($proposal->getCriteria()->isSolidaryExclusive());
        // set return if twoWays ad
        if ($proposal->getProposalLinked()) {
            $ad->setReturnWaypoints($proposal->getProposalLinked()->getWaypoints());
            $ad->setReturnDate($proposal->getProposalLinked()->getCriteria()->getFromDate());
            if ($proposal->getProposalLinked()->getCriteria()->getFromTime()) {
                $ad->setReturnTime($ad->getReturnDate()->format('Y-m-d').' '.$proposal->getProposalLinked()->getCriteria()->getFromTime()->format('H:i:s'));
            } else {
                $ad->setReturnTime(null);
            }
            $ad->setReturnLimitDate($proposal->getProposalLinked()->getCriteria()->getToDate());
            $ad->setOneWay(false);
        }
        // set schedule if regular
        $schedule = [];
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            $schedule = $this->getScheduleFromCriteria($proposal->getCriteria(), $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria() : null);
        }
        $ad->setSchedule($schedule);

        return $ad;
    }

    /**
     * Update an ad.
     *  /!\ Only minor data can be updated
     * Otherwise we delete and create new Ad
     * @param Ad $ad
     * @param string|null $mailSearchLink
     * @return Ad
     * @throws \Exception
     */
    public function updateAd(Ad $ad, ?string $mailSearchLink = null)
    {
        $proposal = $this->proposalRepository->find($ad->getId());
        $oldAd = $this->makeAd($proposal, $ad->getUserId());
        $oldProposal = clone $proposal;
        $oldProposal->setCriteria(clone $proposal->getCriteria());

        $proposalAsks = $this->askManager->getAsksFromProposal($proposal);

        // Pause is apart and do not needs notifications by now
        if ($ad->isPaused() !== $oldAd->isPaused()) {
            $proposal->setPaused($ad->isPaused());
            if ($proposal->getProposalLinked()) {
                $proposal->getProposalLinked()->setPaused($ad->isPaused());
            }
            $this->entityManager->persist($proposal);
        } // major update
        elseif ($this->checkForMajorUpdate($oldAd, $ad)) {
            // We use event to send notifications if Ad has asks
            if (count($proposalAsks) > 0) {
                $event = new AdMajorUpdatedEvent($oldAd, $ad, $proposalAsks, $this->security->getUser(), $mailSearchLink);
                $this->eventDispatcher->dispatch(AdMajorUpdatedEvent::NAME, $event);
            }

//            $this->proposalManager->deleteProposal($proposal);
            $ad = $this->createAd($ad, true);

        // minor update
        } elseif ($oldAd->hasBike() !== $ad->hasBike()
            || $oldAd->hasBackSeats() !== $ad->hasBackSeats()
            || $oldAd->hasLuggage() !== $ad->hasLuggage()
            || $oldAd->getSeatsDriver() !== $ad->getSeatsDriver()
            || $oldAd->getComment() !== $ad->getComment()
        ) {
            $proposal->getCriteria()->setBike($ad->hasBike());
            $proposal->getCriteria()->setBackSeats($ad->hasBackSeats());
            $proposal->getCriteria()->setLuggage($ad->hasLuggage());
            $proposal->getCriteria()->setSeatsDriver($ad->getSeatsDriver());
            $proposal->setComment($ad->getComment());

            if ($proposal->getProposalLinked()) {
                // same if there is linked proposal
                $linkedProposal = $proposal->getProposalLinked();

                $linkedProposal->setPaused($ad->isPaused());
                $linkedProposal->getCriteria()->setBike($ad->hasBike());
                $linkedProposal->getCriteria()->setBackSeats($ad->hasBackSeats());
                $linkedProposal->getCriteria()->setLuggage($ad->hasLuggage());
                $linkedProposal->getCriteria()->setSeatsDriver($ad->getSeatsDriver());
                $linkedProposal->setComment($ad->getComment());

                $this->entityManager->persist($linkedProposal);
            }


            if (count($proposalAsks) > 0) {
                $event = new AdMinorUpdatedEvent($oldAd, $ad, $proposalAsks, $this->security->getUser());
                $this->eventDispatcher->dispatch(AdMinorUpdatedEvent::NAME, $event);
            }
            $this->entityManager->persist($proposal);
            $ad = $this->makeAd($proposal, $proposal->getUser()->getId());
        }

        $this->entityManager->flush();


        $schedule['mon'] = $proposal->getCriteria()->isMonCheck();
        $schedule['monOutwardTime'] = $proposal->getCriteria()->getMonTime();
        $schedule['monReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getMonTime() : null;
        $schedule['tue'] = $proposal->getCriteria()->isTueCheck();
        $schedule['tueOutwardTime'] = $proposal->getCriteria()->getTueTime();
        $schedule['tueReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getTueTime() : null;
        $schedule['wed'] = $proposal->getCriteria()->isWedCheck();
        $schedule['wedOutwardTime'] = $proposal->getCriteria()->getWedTime();
        $schedule['wedReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getWedTime() : null;
        $schedule['thu'] = $proposal->getCriteria()->isThuCheck();
        $schedule['thuOutwardTime'] = $proposal->getCriteria()->getThuTime();
        $schedule['thuReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getThuTime() : null;
        $schedule['fri'] = $proposal->getCriteria()->isFriCheck();
        $schedule['friOutwardTime'] = $proposal->getCriteria()->getFriTime();
        $schedule['friReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getFriTime() : null;
        $schedule['sat'] = $proposal->getCriteria()->isSatCheck();
        $schedule['satOutwardTime'] = $proposal->getCriteria()->getSatTime();
        $schedule['satReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getSatTime() : null;
        $schedule['sun'] = $proposal->getCriteria()->isSunCheck();
        $schedule['sunOutwardTime'] = $proposal->getCriteria()->getSunTime();
        $schedule['sunReturnTime'] = $proposal->getProposalLinked() ? $proposal->getProposalLinked()->getCriteria()->getSunTime() : null;

        $ad->setSchedule($schedule);

        return $ad;
    }

    /**
     * Check if Ad update needs a major update and so, deleting then creating a new one
     * @param Ad $oldAd
     * @param Ad $newAd
     * @return bool
     * @throws \Exception
     */
    public function checkForMajorUpdate(Ad $oldAd, Ad $newAd)
    {


        // checks for regular and punctual
        if ($oldAd->getPriceKm() !== $newAd->getPriceKm()
            || $oldAd->getFrequency() !== $newAd->getFrequency()
            || $oldAd->getRole() !== $newAd->getRole()
            || !$this->compareWaypoints($oldAd->getOutwardWaypoints(), $newAd->getOutwardWaypoints())) {
            return true;
        }

        // checks for regular only
        if ($newAd->getFrequency() === Criteria::FREQUENCY_REGULAR
            && !$this->compareSchedules($oldAd->getSchedule(), $newAd->getSchedule())) {
            return true;
        }

        // checks for regular only
        if ($newAd->getFrequency() === Criteria::FREQUENCY_PUNCTUAL
            && !$this->compareDateTimes($oldAd, $newAd)) {
            return true;
        }

        return false;
    }


    /**
     * Compare Schedules
     * array_diff, array_udiff etc provide strange behavior probably due to datetime, even with callback function
     * @param $old
     * @param $new
     * @return bool
     * @throws \Exception
     */
    public function compareSchedules($old, $new)
    {
        $adSchedule = [];
        // we create temporary schedule cause we need to keep new Ad clean to be able to create a new proposal easily if needed
        foreach ($new as $schedule) {
            if (isset($schedule['mon']) && $schedule['mon']) {
                $adSchedule['mon'] = true;
                $adSchedule['monOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['monReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['mon'])) {
                $adSchedule['mon'] = false;
                $adSchedule['monOutwardTime'] = null;
                $adSchedule['monReturnTime'] = null;
            }
            if (isset($schedule['tue']) && $schedule['tue']) {
                $adSchedule['tue'] = true;
                $adSchedule['tueOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['tueReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['tue'])) {
                $adSchedule['tue'] = false;
                $adSchedule['tueOutwardTime'] = null;
                $adSchedule['tueReturnTime'] = null;
            }
            if (isset($schedule['wed']) && $schedule['wed']) {
                $adSchedule['wed'] = true;
                $adSchedule['wedOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['wedReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['wed'])) {
                $adSchedule['wed'] = false;
                $adSchedule['wedOutwardTime'] = null;
                $adSchedule['wedReturnTime'] = null;
            }
            if (isset($schedule['thu']) && $schedule['thu']) {
                $adSchedule['thu'] = true;
                $adSchedule['thuOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['thuReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['thu'])) {
                $adSchedule['thu'] = false;
                $adSchedule['thuOutwardTime'] = null;
                $adSchedule['thuReturnTime'] = null;
            }
            if (isset($schedule['fri']) && $schedule['fri']) {
                $adSchedule['fri'] = true;
                $adSchedule['friOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['friReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['fri'])) {
                $adSchedule['fri'] = false;
                $adSchedule['friOutwardTime'] = null;
                $adSchedule['friReturnTime'] = null;
            }
            if (isset($schedule['sat']) && $schedule['sat']) {
                $adSchedule['sat'] = true;
                $adSchedule['satOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['satReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['sat'])) {
                $adSchedule['sat'] = false;
                $adSchedule['satOutwardTime'] = null;
                $adSchedule['satReturnTime'] = null;
            }
            if (isset($schedule['sun']) && $schedule['sun']) {
                $adSchedule['sun'] = true;
                $adSchedule['sunOutwardTime'] = !empty($schedule['outwardTime']) ? \DateTime::createFromFormat('H:i', $schedule['outwardTime']) : null;
                $adSchedule['sunReturnTime'] = !empty($schedule['returnTime']) ? \DateTime::createFromFormat('H:i', $schedule['returnTime']) : null;
            } elseif (!isset($adSchedule['sun'])) {
                $adSchedule['sun'] = false;
                $adSchedule['sunOutwardTime'] = null;
                $adSchedule['sunReturnTime'] = null;
            }
        }

        if (!is_array($old) || !is_array($adSchedule) || count($old) !== count($adSchedule)) {
            return false;
        }

        $old = array_values($old);
        $new = array_values($adSchedule);

        for ($i = 0; $i < count($old); $i++) {
            if (is_bool($old[$i]) && is_bool($new[$i]) && $old[$i] !== $new[$i]) {
                return false;
            } elseif (is_a($old[$i], \DateTime::class) && is_null($new[$i])
                || is_a($new[$i], \DateTime::class) && is_null($old[$i])) {
                return false;
            } elseif (is_a($old[$i], \DateTime::class) && is_a($new[$i], \DateTime::class)) {
                if ($old[$i]->format('Y-m-d H:i:s') !== $new[$i]->format('Y-m-d H:i:s')) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Compare if new Waypoints are different from the old ones
     * @param $old - Waypoints object from a Proposal
     * @param $new - waypoint|address object from front
     * @return bool
     */
    public function compareWaypoints($old, $new)
    {
        if (!is_array($old) || !is_array($new) || count($old) !== count($new)) {
            return false;
        }

        for ($i = 0; $i < count($old); $i++) {
            if (!$old[$i] && !$new[$i]) {
                continue;
            } elseif (!isset($old[$i]) || !isset($new[$i]) || $old[$i]->getAddress()->getId() !== $new[$i]->getId()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compare Date and time for Outward and Returns
     * @param Ad $old
     * @param Ad $new
     * @return bool
     * @throws \Exception
     */
    public function compareDateTimes(Ad $old, Ad $new)
    {
        $oldOutwardTime = new \DateTime($old->getOutwardTime());

        // formats are different when Ad is returned by api makeAd or from front serialization
        if ($oldOutwardTime->format('H:i') !== $new->getOutwardTime()) {
            return false;
        }

        if ($old->getOutwardDate()->format('Y-m-d') !== $new->getOutwardDate()->format('Y-m-d')) {
            return false;
        }

        if (!is_null($old->getReturnTime()) && is_null($new->getReturnTime())
            || !is_null($new->getReturnTime()) && is_null($old->getReturnTime())) {
            return false;
        } elseif ($old->getReturnTime()) {
            $oldReturnTime = new \DateTime($old->getReturnTime());
            if ($oldReturnTime->format('H:i') !== $new->getReturnTime()) {
                return false;
            }
        }

        if (!is_null($old->getReturnDate()) && is_null($new->getReturnDate())
            || !is_null($new->getReturnDate()) && is_null($old->getReturnDate())) {
            return false;
        } elseif ($old->getReturnDate()) {
            if ($old->getReturnDate()->format('Y-m-d') !== $new->getReturnDate()->format('Y-m-d')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update all carpools limits (i.e max_detour_duration, max_detour_distance...)
     */
    public function updateCarpoolsLimits()
    {
        set_time_limit(7200);
        $criteria = $this->criteriaRepository->findDrivers();
        /**
         * @var Criteria $criterion
         */
        foreach ($criteria as $criterion) {
            $criterion->setMaxDetourDistance($criterion->getDirectionDriver()->getDistance()*$this->proposalMatcher::MAX_DETOUR_DISTANCE_PERCENT/100);
            $criterion->setMaxDetourDuration($criterion->getDirectionDriver()->getDuration()*$this->proposalMatcher::MAX_DETOUR_DURATION_PERCENT/100);
            $this->entityManager->persist($criterion);
        }
        $this->entityManager->flush();

        return ['yay!'];
    }


    /**
     * Returns an ad and its results matching the parameters.
     * Used for RDEX export.
     *
     * @param bool $offer
     * @param bool $request
     * @param float $from_longitude
     * @param float $from_latitude
     * @param float $to_longitude
     * @param float $to_latitude
     * @param string $frequency
     * @param array $days
     * @param array $outward
     * @param string $external                  The external client
     */
    public function getAdForRdex(
        ?string $external,
        bool $offer,
        bool $request,
        float $from_longitude,
        float $from_latitude,
        float $to_longitude,
        float $to_latitude,
        string $frequency = null,
        ?array $days = null,
        ?array $outward = null
    ) {
        $ad = new Ad();
        $ad->setExternal($external);
        $ad->setSearch(true); // Only a search. This Ad won't be publish.

        // Role
        if ($offer && $request) {
            $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        } elseif ($request) {
            $ad->setRole(Ad::ROLE_DRIVER);
        } else {
            $ad->setRole(Ad::ROLE_PASSENGER);
        }

        // Origin/Destination
        $ad->setOutwardWaypoints([
            [
                "latitude" => $from_latitude,
                "longitude" => $from_longitude
            ],
            [
                "latitude" => $to_latitude,
                "longitude" => $to_longitude
            ],
        ]);

        // Create a schedule and set frequency
        // RDEX has always a explicit day (monday...) even for puntual
        // So we always create a schedule then we make a deduction if it's punctual or regular based on it.
        // If the frequency parameter is given it overides the deduction

        // if outward is null, we make an array using now with 1 hour margin on $day bases
        if (is_null($outward)) {
            $time = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
            $mintime = $time->format("H:i:s");
            $maxtime = $time->add(new \DateInterval("PT1H"))->format("H:i:s");
        
            // if days is null, we are using today
            if (is_null($days)) {
                $today = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
                $days = [strtolower($today->format('l'))=>1];
                $outward = ["mindate"=>$time->format("Y-m-d")];
                $outward[strtolower($today->format('l'))] = [
                    "mintime" => $mintime,
                    "maxtime" => $maxtime
                ];
            } else {
                // We don't have any date so i'm looking for the first date corresponding to the first day
                $dateFound = "";
                $currentTestDate = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
                $cpt = 0; // it's a failsafe to avoid infinit loop
                while ($dateFound === "" && $cpt < 7) {
                    if (isset($days[strtolower($currentTestDate->format('l'))])) {
                        $dateFound = $currentTestDate;
                    } else {
                        $currentTestDate = $currentTestDate->add(new \DateInterval("P1D"));
                    }
                    $cpt++;
                }
                $outward = ["mindate"=>$dateFound->format("Y-m-d")];
                foreach ($days as $day => $value) {
                    $outward[$day] = [
                        "mintime" => $mintime,
                        "maxtime" => $maxtime
                    ];
                }
            }
        }
        // var_dump($outward);

        // if days is null, we make an array using outward
        if (is_null($days)) {
            $today = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
            foreach ($outward as $day => $times) {
                $days = [$day=>1];
            }
        }

        // var_dump($days);
        // die;

        $schedules = $this->buildSchedule($days, $outward);
        if (count($schedules)>0) {
            if ($frequency=="punctual") {
                // Punctual journey
                $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $ad->setOutwardDate(\DateTime::createFromFormat("Y-m-d", $outward["mindate"]));
                (isset($outward["maxdate"])) ? $ad->setOutwardLimitDate(\DateTime::createFromFormat("Y-m-d", $outward["maxdate"])) : '';

                $ad->setOutwardTime($schedules[0]["outwardTime"]);
            } elseif ($frequency=="regular") {
                // Regular journey
                $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                $ad->setSchedule($schedules);
            } else {
                // If only one schedule with one day punctual. Else it's regular.
                if (count($schedules)>1 || count($schedules[0])>2) {
                    $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                    $ad->setSchedule($schedules);
                } else {
                    $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                    $ad->setOutwardDate(\DateTime::createFromFormat("Y-m-d", $outward["mindate"]));
                    (isset($outward["maxdate"])) ? $ad->setOutwardLimitDate(\DateTime::createFromFormat("Y-m-d", $outward["maxdate"])) : '';
    
                    $ad->setOutwardTime($schedules[0]["outwardTime"]);
                }
            }
        } else {
            return new RdexError("apikey", RdexError::ERROR_MISSING_MANDATORY_FIELD, "Invalid outward");
        }
        
        return $this->createAd($ad);
    }

    /**
     * Compute the average hour between two hours.
     *
     * @param string $heureMin          Minimum hour
     * @param string $heureMax          Maximum hour
     * @param string $dateMin        Minimum date
     * @param string|null $dateMax   Maximum date
     * @return \Datetime
     */
    private function middleHour(string $heureMin, string $heureMax, string $dateMin, ?string $dateMax=null)
    {
        (is_null($dateMax)) ? $dateMax = $dateMin : '';
        
        $min = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMin . " " . $heureMin, new \DateTimeZone('UTC'));
        $mintime = $min->getTimestamp();
        $max = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMax . " " . $heureMax, new \DateTimeZone('UTC'));
        $maxtime = $max->getTimestamp();
        $marge = ($maxtime - $mintime) / 2;
        $middleHour = $mintime + $marge;
        $returnHour = new \DateTime();
        $returnHour->setTimestamp($middleHour);
        return $returnHour;
    }

    /**
     * Build an Ad Schedule
     * @var array $day      Array of the selected days
     * @var array $outward  Array of the time for each days
     * @return array
     */
    private function buildSchedule(?array $days, ?array $outward)
    {
        $schedules = []; // We set a subschdeul because a real Ad can have multiple schedule. Only one in RDEX though.
        $refTimes = [];
        foreach ($days as $day => $value) {
            $shortDay = substr($day, 0, 3);
            if (isset($outward[$day]['mintime']) && isset($outward[$day]['maxtime'])) {
                $outward_mindate = $outward['mindate'];
                (!isset($outward['maxdate'])) ? $outward_maxdate = $outward_mindate : $outward['maxdate'];
                $middleHour = $this->middleHour($outward[$day]['mintime'], $outward[$day]['maxtime'], $outward_mindate, $outward_maxdate);
                
                $previousKey = array_search($middleHour, $refTimes);

                if (is_null($previousKey) || !is_numeric($previousKey)) {
                    $refTimes[] = $middleHour;
                    $previousKey = array_search($middleHour, $refTimes);
                    $schedules[$previousKey] = [
                        'outwardTime' => $middleHour->format("H:i")
                    ];
                }

                $schedules[$previousKey][$shortDay] = 1;
            }
        }
        return $schedules;
    }

    /**
    * Get ads with accepted asks of a user
    *
    * @param integer $userId
    * @return array
    */
    public function getUserAcceptedCarpools(int $userId) : array
    {
        // array of ads
        $ads = [];
        // temporary array
        $temp=[];
        // array of ads from asks
        $askAds = [];
        $user = $this->userManager->getUser($userId);
        // We retrieve all the proposals of the user
        $proposals = $this->proposalRepository->findBy(['user'=>$user]);

        // We check for each proposal if he have matching
        /** @var Proposal $proposal */
        foreach ($proposals as $proposal) {
            $askAdLinked = null;
            $matchingLinked = null;
            /** @var Matching $matching */
            foreach ($proposal->getMatchingRequests() as $matching) {
                // We check if the matching have an ask
                /** @var Ask $ask */
                foreach ($matching->getAsks() as $ask) {
                    // We check if the ask is accepted if yes we put the ask in the tab
                    if ($ask->getStatus() === Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() === Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                        // this ask is the ask with data we want to fill the Ad
                        if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                            $askAd = $askAdLinked = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalOffer());
                            $matchingLinked = $matching;
                        } else {
                            $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                        }
                        $askAds[] = $askAd;
                    }
                }
            }
            // We check for each proposal if he have matching
            foreach ($proposal->getMatchingOffers() as $matching) {
                // We check if the matching have an ask
                foreach ($matching->getAsks() as $ask) {
                    // We check if the ask is accepted if yes we put the ask in the tab
                    if ($ask->getStatus() === Ask::STATUS_ACCEPTED_AS_DRIVER || $ask->getStatus() === Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                        // this ask is the ask with data we want to fill the Ad
                        if ($ask->getUser()->getId() && $ask->getUser()->getId() === $userId) {
                            $askAd = $askAdLinked = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId, $matching->getProposalRequest());
                            $matchingLinked = $matching;
                        } else {
                            $askAd = $this->askManager->getSimpleAskFromAd($ask->getId(), $userId);
                        }
                        $askAds[] = $askAd;
                    }
                }
            }
            // we check if the proposal have accepted asks
            if (count($askAds) > 0) {
                // if yes we create an ad with the associated asks
                // we pass the askLinked (Ad) to fill Ad with data from the ask if not null
                $ad = $this->makeAd($proposal, $userId, true, $askAdLinked, $matchingLinked);
                $ad->setAsks($askAds);
                // we put the id of the proposals linked in the temporary array
                $temp[] = $ad->getProposalLinkedId();
                // We reset the asks array for the next proposal
                $askAds = [];
                // We check if the proposal is not a proposal linked of an other proposal and regular
                if (in_array($ad->getProposalId(), $temp) && $ad->getFrequency() === Criteria::FREQUENCY_REGULAR) {
                    //  If yes we continue
                    continue;
                } else {
                    // If not we add it to the ads array
                    $ads[] = $ad;
                }
            }
        }
        // We return the ads array with only the ads with accepted asks associated
        return $ads;
    }
}
