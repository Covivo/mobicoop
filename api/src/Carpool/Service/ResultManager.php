<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
use App\Carpool\Entity\ResultItem;
use App\Carpool\Entity\ResultRole;
use App\Carpool\Repository\MatchingRepository;
use App\Service\FormatDataManager;

/**
 * Result manager service.
 * Used to create user-friendly results from the matching system.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ResultManager
{
    private $formatDataManager;
    private $proposalMatcher;
    private $matchingRepository;
    private $params;

    /**
     * Constructor.
     *
     * @param FormatDataManager $proposalMatcher
     * @param array $params
     */
    public function __construct(FormatDataManager $formatDataManager, ProposalMatcher $proposalMatcher, MatchingRepository $matchingRepository)
    {
        $this->formatDataManager = $formatDataManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->matchingRepository = $matchingRepository;
    }

    // set the params
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Create "user-friendly" results from the matchings of a proposal
     *
     * @param Proposal $proposal    The proposal with its matchings
     * @return array                The array of results
     */
    public function createResults(Proposal $proposal)
    {
        $results = [];
        // we group the matchings by matching proposalId to merge potential driver and/or passenger candidates
        $matchings = [];
        // we search the matchings as an offer
        foreach ($proposal->getMatchingRequests() as $request) {
            if (is_null($request->getFilters())) {
                $request->setFilters($this->proposalMatcher->getMatchingFilters($request));
            }
            $matchings[$request->getProposalRequest()->getId()]['request'] = $request;
        }
        // we search the matchings as a request
        foreach ($proposal->getMatchingOffers() as $offer) {
            if (is_null($offer->getFilters())) {
                $offer->setFilters($this->proposalMatcher->getMatchingFilters($offer));
            }
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
                // we set the proposalId
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

                // prices

                // we set the prices of the driver (the requester)
                // if the requester price per km is set we use it
                if ($proposal->getCriteria()->getPriceKm()) {
                    $outward->setDriverPriceKm($proposal->getCriteria()->getPriceKm());
                } else {
                    // otherwise we use the common price
                    $outward->setDriverPriceKm($this->params['defaultPriceKm']);
                }
                // if the requester price is set we use it
                if ($proposal->getCriteria()->getDriverPrice()) {
                    $outward->setDriverOriginalPrice($proposal->getCriteria()->getDriverPrice());
                } else {
                    // otherwise we use the common price, rounded
                    $outward->setDriverOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matching['request']->getFilters()['originalDistance']*(float)$outward->getDriverPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
                }
                
                // we set the prices of the passenger (the carpooler)
                $outward->setPassengerPriceKm($matching['request']->getProposalRequest()->getCriteria()->getPriceKm());
                $outward->setPassengerOriginalPrice($matching['request']->getProposalRequest()->getCriteria()->getPassengerPrice());
                
                // the computed price is the price to be paid by the passenger
                // it's ((common distance + detour distance) * driver price by km)
                $outward->setComputedPrice((string)(((int)$matching['request']->getFilters()['commonDistance']+(int)$matching['request']->getFilters()['detourDistance'])*(float)$outward->getDriverPriceKm()/1000));
                $outward->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$outward->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                $resultDriver->setOutward($outward);
                
                // return trip, only for regular trip for now
                if ($matching['request']->getProposalRequest()->getProposalLinked() && $proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching['request']->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $requestProposalLinked = $matching['request']->getProposalRequest()->getProposalLinked();
                    $offerProposalLinked = $matching['request']->getProposalOffer()->getProposalLinked();
                    $matchingRelated = $matching['request']->getMatchingRelated();
                    
                    $return = new ResultItem();
                    // we use the carpooler days as we don't have a matching here
                    $return->setMonCheck($requestProposalLinked->getCriteria()->isMonCheck());
                    $return->setTueCheck($requestProposalLinked->getCriteria()->isTueCheck());
                    $return->setWedCheck($requestProposalLinked->getCriteria()->isWedCheck());
                    $return->setThuCheck($requestProposalLinked->getCriteria()->isThuCheck());
                    $return->setFriCheck($requestProposalLinked->getCriteria()->isFriCheck());
                    $return->setSatCheck($requestProposalLinked->getCriteria()->isSatCheck());
                    $return->setSunCheck($requestProposalLinked->getCriteria()->isSunCheck());
                    $return->setFromDate($requestProposalLinked->getCriteria()->getFromDate());
                    $return->setToDate($requestProposalLinked->getCriteria()->getToDate());

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
                        if ($requestProposalLinked->getCriteria()->isMonCheck()) {
                            $monTime = clone $requestProposalLinked->getCriteria()->getMonTime();
                            if ($pickupDuration) {
                                $monTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setMonTime($monTime);
                            $return->setTime($monTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isTueCheck()) {
                            $tueTime = clone $requestProposalLinked->getCriteria()->getTueTime();
                            if ($pickupDuration) {
                                $tueTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setTueTime($tueTime);
                            $return->setTime($tueTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isWedCheck()) {
                            $wedTime = clone $requestProposalLinked->getCriteria()->getWedTime();
                            if ($pickupDuration) {
                                $wedTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setWedTime($wedTime);
                            $return->setTime($wedTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isThuCheck()) {
                            $thuTime = clone $requestProposalLinked->getCriteria()->getThuTime();
                            if ($pickupDuration) {
                                $thuTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setThuTime($thuTime);
                            $return->setTime($thuTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isFriCheck()) {
                            $friTime = clone $requestProposalLinked->getCriteria()->getFriTime();
                            if ($pickupDuration) {
                                $friTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setFriTime($friTime);
                            $return->setTime($friTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isSatCheck()) {
                            $satTime = clone $requestProposalLinked->getCriteria()->getSatTime();
                            if ($pickupDuration) {
                                $satTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSatTime($satTime);
                            $return->setTime($satTime);
                        }
                        if ($requestProposalLinked->getCriteria()->isSunCheck()) {
                            $sunTime = clone $requestProposalLinked->getCriteria()->getSunTime();
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
                        
                        // prices

                        // we set the prices of the driver (the requester)
                        // if the requester price per km is set we use it
                        if ($offerProposalLinked && $offerProposalLinked->getCriteria()->getPriceKm()) {
                            $return->setDriverPriceKm($offerProposalLinked->getCriteria()->getPriceKm());
                        } else {
                            // otherwise we use the common price
                            $return->setDriverPriceKm($this->params['defaultPriceKm']);
                        }
                        // if the requester price is set we use it
                        if ($offerProposalLinked && $offerProposalLinked->getCriteria()->getPrice()) {
                            $return->setDriverOriginalPrice($offerProposalLinked->getCriteria()->getDriverPrice());
                        } else {
                            // otherwise we use the common price
                            $return->setDriverOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matchingRelated->getFilters()['originalDistance']*(float)$return->getDriverPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
                        }
                        
                        // we set the prices of the passenger (the carpooler)
                        $return->setPassengerPriceKm($requestProposalLinked->getCriteria()->getPriceKm());
                        $return->setPassengerOriginalPrice($requestProposalLinked->getCriteria()->getPassengerPrice());

                        // the computed price is the price to be paid by the passenger
                        // it's ((common distance + detour distance) * driver price by km)
                        $return->setComputedPrice((string)(((int)$matchingRelated->getFilters()['commonDistance']+(int)$matchingRelated->getFilters()['detourDistance'])*(float)$return->getDriverPriceKm()/1000));
                        $return->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$return->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                    }
                    $return->setMultipleTimes();
                    if ($return->hasMultipleTimes()) {
                        $return->setTime(null);
                    }

                    $resultDriver->setReturn($return);
                }

                // seats
                $resultDriver->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger() ? $proposal->getCriteria()->getSeatsPassenger() : 1);
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
                // we set the proposalId
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

                // prices

                // we set the prices of the driver (the carpooler)
                $outward->setDriverPriceKm($matching['offer']->getProposalOffer()->getCriteria()->getPriceKm());
                $outward->setDriverOriginalPrice($matching['offer']->getProposalOffer()->getCriteria()->getDriverPrice());
                
                // we set the prices of the passenger (the requester)
                if ($proposal->getCriteria()->getPriceKm()) {
                    $outward->setPassengerPriceKm($proposal->getCriteria()->getPriceKm());
                } else {
                    // otherwise we use the common price
                    $outward->setPassengerPriceKm($this->params['defaultPriceKm']);
                }
                // if the requester price is set we use it
                if ($proposal->getCriteria()->getPassengerPrice()) {
                    $outward->setPassengerOriginalPrice($proposal->getCriteria()->getPassengerPrice());
                } else {
                    // otherwise we use the common price
                    $outward->setPassengerOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matching['offer']->getFilters()['commonDistance']*(float)$outward->getPassengerPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
                }
                
                // the computed price is the price to be paid by the passenger
                // it's ((common distance + detour distance) * driver price by km)
                $outward->setComputedPrice((string)(((int)$matching['offer']->getFilters()['commonDistance']+(int)$matching['offer']->getFilters()['detourDistance'])*(float)$outward->getDriverPriceKm()/1000));
                $outward->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$outward->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                $resultPassenger->setOutward($outward);

                // return trip, only for regular trip for now
                if ($matching['offer']->getProposalOffer()->getProposalLinked() && $proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching['offer']->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $offerProposalLinked = $matching['offer']->getProposalOffer()->getProposalLinked();
                    $matchingRelated = $matching['offer']->getMatchingRelated();

                    $return = new ResultItem();
                    // we use the carpooler days as we don't have a matching here
                    $return->setMonCheck($offerProposalLinked->getCriteria()->isMonCheck());
                    $return->setTueCheck($offerProposalLinked->getCriteria()->isTueCheck());
                    $return->setWedCheck($offerProposalLinked->getCriteria()->isWedCheck());
                    $return->setThuCheck($offerProposalLinked->getCriteria()->isThuCheck());
                    $return->setFriCheck($offerProposalLinked->getCriteria()->isFriCheck());
                    $return->setSatCheck($offerProposalLinked->getCriteria()->isSatCheck());
                    $return->setSunCheck($offerProposalLinked->getCriteria()->isSunCheck());
                    $return->setFromDate($offerProposalLinked->getCriteria()->getFromDate());
                    $return->setToDate($offerProposalLinked->getCriteria()->getToDate());
                    
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
                        if ($offerProposalLinked->getCriteria()->isMonCheck()) {
                            $monTime = clone $offerProposalLinked->getCriteria()->getMonTime();
                            $driverFromTime = clone $monTime;
                            if ($pickupDuration) {
                                $monTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setMonTime($monTime);
                            $return->setTime($monTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isTueCheck()) {
                            $tueTime = clone $offerProposalLinked->getCriteria()->getTueTime();
                            $driverFromTime = clone $tueTime;
                            if ($pickupDuration) {
                                $tueTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setTueTime($tueTime);
                            $return->setTime($tueTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isWedCheck()) {
                            $wedTime = clone $offerProposalLinked->getCriteria()->getWedTime();
                            $driverFromTime = clone $wedTime;
                            if ($pickupDuration) {
                                $wedTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setWedTime($wedTime);
                            $return->setTime($wedTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isThuCheck()) {
                            $thuTime = clone $offerProposalLinked->getCriteria()->getThuTime();
                            $driverFromTime = clone $thuTime;
                            if ($pickupDuration) {
                                $thuTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setThuTime($thuTime);
                            $return->setTime($thuTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isFriCheck()) {
                            $friTime = clone $offerProposalLinked->getCriteria()->getFriTime();
                            $driverFromTime = clone $friTime;
                            if ($pickupDuration) {
                                $friTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setFriTime($friTime);
                            $return->setTime($friTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isSatCheck()) {
                            $satTime = clone $offerProposalLinked->getCriteria()->getSatTime();
                            $driverFromTime = clone $satTime;
                            if ($pickupDuration) {
                                $satTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            }
                            $return->setSatTime($satTime);
                            $return->setTime($satTime);
                        }
                        if ($offerProposalLinked->getCriteria()->isSunCheck()) {
                            $sunTime = clone $offerProposalLinked->getCriteria()->getSunTime();
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

                        // prices

                        // we set the prices of the driver (the carpooler)
                        // if the requester price per km is set we use it
                        $return->setDriverPriceKm($offerProposalLinked->getCriteria()->getPriceKm());
                        $return->setDriverOriginalPrice($offerProposalLinked->getCriteria()->getDriverPrice());
                        
                        // we set the prices of the passenger (the requester)
                        // we don't have a proposalLinked for the proposal, we use the proposal
                        if ($proposal->getCriteria()->getPriceKm()) {
                            $return->setPassengerPriceKm($proposal->getCriteria()->getPriceKm());
                        } else {
                            // otherwise we use the common price
                            $return->setPassengerPriceKm($this->params['defaultPriceKm']);
                        }
                        // if the requester price is set we use it
                        if ($proposal->getCriteria()->getPassengerPrice()) {
                            $return->setPassengerOriginalPrice($proposal->getCriteria()->getPassengerPrice());
                        } else {
                            // otherwise we use the common price
                            $return->setPassengerOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matchingRelated->getFilters()['commonDistance']*(float)$return->getPassengerPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
                        }

                        // the computed price is the price to be paid by the passenger
                        // it's ((common distance + detour distance) * driver price by km)
                        $return->setComputedPrice((string)(((int)$matchingRelated->getFilters()['commonDistance']+(int)$matchingRelated->getFilters()['detourDistance'])*(float)$return->getDriverPriceKm()/1000));
                        $return->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$return->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
                    }
                    $return->setMultipleTimes();
                    if ($return->hasMultipleTimes()) {
                        $return->setTime(null);
                    }
                    
                    $resultPassenger->setReturn($return);
                }

                // seats
                $resultPassenger->setSeatsDriver($matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() ? $matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() : 1);
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
                $result->setSeatsPassenger($result->getResultDriver()->getSeatsPassenger());
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
                $result->setSeatsDriver($result->getResultPassenger()->getSeatsDriver());
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
     * Create "user-friendly" results from the matchings of an ad proposal
     *
     * @param Proposal $proposal    The proposal with its matchings
     * @return array                The array of results
     */
    public function createAdResults(Proposal $proposal)
    {
        // the outward results are the base results
        $results = $this->createProposalResults($proposal);
        $returnResults = [];
        if ($proposal->getProposalLinked()) {
            $returnResults = $this->createProposalResults($proposal->getProposalLinked(), true);
        }

        // the outward results are the base
        // we will have to check for each return result if it's a return of an outward result

        // we loop through the return results
        foreach ($returnResults as $result) {
            if (!is_null($result->getResultDriver())) {
                // there's a return as a driver
                if ($linkedMatching = $this->matchingRepository->findOneBy(['matchingLinked'=>$result->getResultDriver()->getReturn()->getMatchingId()])) {
                    // there's a linked matching, we check if there's an outwardResult with this matching
                    if (isset($results[$linkedMatching->getProposalRequest()->getId()])) {
                        // the linked matching is in the outward results => we set the return of the outward
                        if (!is_null($results[$linkedMatching->getProposalRequest()->getId()]->getResultDriver())) {
                            // there's an outward as a driver
                            $results[$linkedMatching->getProposalRequest()->getId()]->getResultDriver()->setReturn($result->getResultDriver()->getReturn());
                        } else {
                            // there's no outward as a driver, but a return as a driver => for now we skip
                        }
                    }
                }
            }
            if (!is_null($result->getResultPassenger())) {
                // there's a return as a passenger
                if ($linkedMatching = $this->matchingRepository->findOneBy(['matchingLinked'=>$result->getResultPassenger()->getReturn()->getMatchingId()])) {
                    // there's a linked matching, we check if there's an outwardResult with this matching
                    if (isset($results[$linkedMatching->getProposalOffer()->getId()])) {
                        // the linked matching is in the outward results => we set the return of the outward
                        if (!is_null($results[$linkedMatching->getProposalOffer()->getId()]->getResultPassenger())) {
                            // there's an outward as a driver
                            $results[$linkedMatching->getProposalOffer()->getId()]->getResultPassenger()->setReturn($result->getResultPassenger()->getReturn());
                        } else {
                            // there's no outward as a driver, but a return as a driver => for now we skip
                        }
                    }
                }
            }
        }

        /**********************************************************************
         * global origin / destination / date / time / seats / price / return *
         **********************************************************************/
        $finalResults = [];
        foreach ($results as $originalResult) {
            $result = clone $originalResult;
            $finalResults[] = $this->createGlobalResult($result, $proposal->getWaypoints());
        }
        return $finalResults;
    }

    /**
     * Complete the global result
     *
     * @param Result $result
     * @param array $waypoints
     * @return void
     */
    private function createGlobalResult(Result $result, array $waypoints)
    {
        // origin / destination
        // we display the origin and destination of the passenger for his outward trip
        // if the carpooler can be driver and passenger, we choose to consider him as driver as he's the first to publish
        // we also set the originFirst and destinationLast to indicate if the driver origin / destination are different than the passenger ones

        // we first get the origin and destination of the requester
        $requesterOrigin = null;
        $requesterDestination = null;
        foreach ($waypoints as $waypoint) {
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
            $result->setSeatsDriver($result->getResultDriver()->getSeatsDriver());
            $result->setSeatsPassenger($result->getResultDriver()->getSeatsPassenger());
            $result->setSeats($result->getResultDriver()->getSeatsPassenger());
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
            $result->setSeatsDriver($result->getResultPassenger()->getSeatsDriver());
            $result->setSeatsPassenger($result->getResultPassenger()->getSeatsPassenger());
            $result->setSeats($result->getResultPassenger()->getSeatsDriver());
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

        return $result;
    }

    /**
     * Create results for an outward or a return proposal.
     *
     * @param Proposal $proposal    The proposal
     * @param boolean $return       The result is for the return trip
     * @return array
     */
    private function createProposalResults(Proposal $proposal, bool $return = false)
    {
        $results = [];
        // we group the matchings by matching proposalId to merge potential driver and/or passenger candidates
        $matchings = [];
        // we search the matchings as an offer
        foreach ($proposal->getMatchingRequests() as $request) {
            // we exclude the private proposals
            if ($request->getProposalRequest()->isPrivate()) {
                continue;
            }
            if (is_null($request->getFilters())) {
                $request->setFilters($this->proposalMatcher->getMatchingFilters($request));
            }
            $matchings[$request->getProposalRequest()->getId()]['request'] = $request;
        }
        // we search the matchings as a request
        foreach ($proposal->getMatchingOffers() as $offer) {
            // we exclude the private proposals
            if ($offer->getProposalOffer()->isPrivate()) {
                continue;
            }
            if (is_null($offer->getFilters())) {
                $offer->setFilters($this->proposalMatcher->getMatchingFilters($offer));
            }
            $matchings[$offer->getProposalOffer()->getId()]['offer'] = $offer;
        }
        // we iterate through the matchings to create the results
        foreach ($matchings as $matchingProposalId => $matching) {
            $result = $this->createMatchingResult($proposal, $matchingProposalId, $matching, $return);
            $results[$matchingProposalId] = $result;
        }
        return $results;
    }

    /**
     * Create results for a given matching of a proposal
     *
     * @param Proposal $proposal            The proposal
     * @param integer $matchingProposalId   The proposal that matches
     * @param array $matching               The array of the matchings of the proposal (an array with the matching proposal as offer and/or request)
     * @param boolean $return               The matching concerns a return (=false if it's the outward)
     * @return Result                       The result object
     */
    private function createMatchingResult(Proposal $proposal, int $matchingProposalId, array $matching, bool $return)
    {
        $result = new Result();
        $result->setId($proposal->getId());
        $resultDriver = new ResultRole();
        $resultPassenger = new ResultRole();
        $communities = [];
            
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

            // communities
            foreach ($matching['request']->getProposalRequest()->getCommunities() as $community) {
                $communities[$community->getId()] = $community->getName();
            }
            
            // outward
            $item = new ResultItem();
            // we set the proposalId
            $item->setProposalId($matchingProposalId);
            if ($matching['request']->getId() !== Matching::DEFAULT_ID) {
                $item->setMatchingId($matching['request']->getId());
            }
            if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                // the search/ad proposal is punctual
                // we have to calculate the date and time of the carpool
                // date :
                // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                // - if the matching proposal is regular, it's the date of the search/ad (as the matching proposal "matches", it means that the date is valid => the date is in the range of the regular matching proposal)
                if ($matching['request']->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $item->setDate($matching['request']->getProposalRequest()->getCriteria()->getFromDate());
                } else {
                    $item->setDate($proposal->getCriteria()->getFromDate());
                }
                // time
                // the carpooler is passenger, the proposal owner is driver : we use his time if it's set
                if ($proposal->getCriteria()->getFromTime()) {
                    $item->setTime($proposal->getCriteria()->getFromTime());
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
                    $item->setTime($fromTime);
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
                        $item->setMonCheck(true);
                        $item->setMonTime($proposal->getCriteria()->getMonTime());
                        $item->setTime($proposal->getCriteria()->getMonTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['tueMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['tueMaxPickupTime'])) {
                        $item->setTueCheck(true);
                        $item->setTueTime($proposal->getCriteria()->getTueTime());
                        $item->setTime($proposal->getCriteria()->getTueTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['wedMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['wedMaxPickupTime'])) {
                        $item->setWedCheck(true);
                        $item->setWedTime($proposal->getCriteria()->getWedTime());
                        $item->setTime($proposal->getCriteria()->getWedTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['thuMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['thuMaxPickupTime'])) {
                        $item->setThuCheck(true);
                        $item->setThuTime($proposal->getCriteria()->getThuTime());
                        $item->setTime($proposal->getCriteria()->getThuTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['friMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['friMaxPickupTime'])) {
                        $item->setFriCheck(true);
                        $item->setFriTime($proposal->getCriteria()->getFriTime());
                        $item->setTime($proposal->getCriteria()->getFriTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['satMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['satMaxPickupTime'])) {
                        $item->setSatCheck(true);
                        $item->setSatTime($proposal->getCriteria()->getSatTime());
                        $item->setTime($proposal->getCriteria()->getSatTime());
                    }
                    if (isset($matching['request']->getFilters()['pickup']['sunMinPickupTime']) && isset($matching['request']->getFilters()['pickup']['sunMaxPickupTime'])) {
                        $item->setSunCheck(true);
                        $item->setSunTime($proposal->getCriteria()->getSunTime());
                        $item->setTime($proposal->getCriteria()->getSunTime());
                    }
                } else {
                    // no pick up times, it must be the matching results of a search (and not an ad)
                    // the days are the carpooler days
                    $item->setMonCheck($matching['request']->getProposalRequest()->getCriteria()->isMonCheck());
                    $item->setTueCheck($matching['request']->getProposalRequest()->getCriteria()->isTueCheck());
                    $item->setWedCheck($matching['request']->getProposalRequest()->getCriteria()->isWedCheck());
                    $item->setThuCheck($matching['request']->getProposalRequest()->getCriteria()->isThuCheck());
                    $item->setFriCheck($matching['request']->getProposalRequest()->getCriteria()->isFriCheck());
                    $item->setSatCheck($matching['request']->getProposalRequest()->getCriteria()->isSatCheck());
                    $item->setSunCheck($matching['request']->getProposalRequest()->getCriteria()->isSunCheck());
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
                        $item->setMonTime($monTime);
                        $item->setTime($monTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isTueCheck()) {
                        $tueTime = clone $matching['request']->getProposalRequest()->getCriteria()->getTueTime();
                        if ($pickupDuration) {
                            $tueTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setTueTime($tueTime);
                        $item->setTime($tueTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isWedCheck()) {
                        $wedTime = clone $matching['request']->getProposalRequest()->getCriteria()->getWedTime();
                        if ($pickupDuration) {
                            $wedTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setWedTime($wedTime);
                        $item->setTime($wedTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isThuCheck()) {
                        $thuTime = clone $matching['request']->getProposalRequest()->getCriteria()->getThuTime();
                        if ($pickupDuration) {
                            $thuTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setThuTime($thuTime);
                        $item->setTime($thuTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isFriCheck()) {
                        $friTime = clone $matching['request']->getProposalRequest()->getCriteria()->getFriTime();
                        if ($pickupDuration) {
                            $friTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setFriTime($friTime);
                        $item->setTime($friTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isSatCheck()) {
                        $satTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSatTime();
                        if ($pickupDuration) {
                            $satTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSatTime($satTime);
                        $item->setTime($satTime);
                    }
                    if ($matching['request']->getProposalRequest()->getCriteria()->isSunCheck()) {
                        $sunTime = clone $matching['request']->getProposalRequest()->getCriteria()->getSunTime();
                        if ($pickupDuration) {
                            $sunTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSunTime($sunTime);
                        $item->setTime($sunTime);
                    }
                }
                $item->setMultipleTimes();
                if ($item->hasMultipleTimes()) {
                    $item->setTime(null);
                }
                // fromDate is the max between the search date and the fromDate of the matching proposal
                $item->setFromDate(max(
                    $matching['request']->getProposalRequest()->getCriteria()->getFromDate(),
                    $proposal->getCriteria()->getFromDate()
                ));
                $item->setToDate($matching['request']->getProposalRequest()->getCriteria()->getToDate());
            }
            // waypoints of the item
            $waypoints = [];
            $time = $item->getTime() ? clone $item->getTime() : null;
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
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['carpooler']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                    $item->setOriginDriver($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['requester']) {
                    $item->setDestinationDriver($waypoint['address']);
                }
            }
            $item->setWaypoints($waypoints);
            
            // statistics
            $item->setOriginalDistance($matching['request']->getFilters()['originalDistance']);
            $item->setAcceptedDetourDistance($matching['request']->getFilters()['acceptedDetourDistance']);
            $item->setNewDistance($matching['request']->getFilters()['newDistance']);
            $item->setDetourDistance($matching['request']->getFilters()['detourDistance']);
            $item->setDetourDistancePercent($matching['request']->getFilters()['detourDistancePercent']);
            $item->setOriginalDuration($matching['request']->getFilters()['originalDuration']);
            $item->setAcceptedDetourDuration($matching['request']->getFilters()['acceptedDetourDuration']);
            $item->setNewDuration($matching['request']->getFilters()['newDuration']);
            $item->setDetourDuration($matching['request']->getFilters()['detourDuration']);
            $item->setDetourDurationPercent($matching['request']->getFilters()['detourDurationPercent']);
            $item->setCommonDistance($matching['request']->getFilters()['commonDistance']);

            // prices

            // we set the prices of the driver (the requester)
            // if the requester price per km is set we use it
            if ($proposal->getCriteria()->getPriceKm()) {
                $item->setDriverPriceKm($proposal->getCriteria()->getPriceKm());
            } else {
                // otherwise we use the common price
                $item->setDriverPriceKm($this->params['defaultPriceKm']);
            }
            // if the requester price is set we use it
            if ($proposal->getCriteria()->getDriverPrice()) {
                $item->setDriverOriginalPrice($proposal->getCriteria()->getDriverPrice());
            } else {
                // otherwise we use the common price, rounded
                $item->setDriverOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matching['request']->getFilters()['originalDistance']*(float)$item->getDriverPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
            }
            
            // we set the prices of the passenger (the carpooler)
            $item->setPassengerPriceKm($matching['request']->getProposalRequest()->getCriteria()->getPriceKm());
            $item->setPassengerOriginalPrice($matching['request']->getProposalRequest()->getCriteria()->getPassengerPrice());
            
            // the computed price is the price to be paid by the passenger
            // it's ((common distance + detour distance) * driver price by km)
            $item->setComputedPrice((string)(((int)$matching['request']->getFilters()['commonDistance']+(int)$matching['request']->getFilters()['detourDistance'])*(float)$item->getDriverPriceKm()/1000));
            $item->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$item->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
            if (!$return) {
                $resultDriver->setOutward($item);
            } else {
                $resultDriver->setReturn($item);
            }
            
            // seats
            $resultDriver->setSeatsPassenger($proposal->getCriteria()->getSeatsPassenger() ? $proposal->getCriteria()->getSeatsPassenger() : 1);
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

            // communities
            foreach ($matching['offer']->getProposalOffer()->getCommunities() as $community) {
                $communities[$community->getId()] = $community->getName();
            }
            
            // outward
            $item = new ResultItem();
            // we set the proposalId
            $item->setProposalId($matchingProposalId);
            if ($matching['offer']->getId() !== Matching::DEFAULT_ID) {
                $item->setMatchingId($matching['offer']->getId());
            }
            $driverFromTime = null;
            if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                // the search/ad proposal is punctual
                // we have to calculate the date and time of the carpool
                // date :
                // - if the matching proposal is also punctual, it's the date of the matching proposal (as the date of the matching proposal could be the same or after the date of the search/ad)
                // - if the matching proposal is regular, it's the date of the search/ad (as the matching proposal "matches", it means that the date is valid => the date is in the range of the regular matching proposal)
                if ($matching['offer']->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $item->setDate($matching['offer']->getProposalOffer()->getCriteria()->getFromDate());
                } else {
                    $item->setDate($proposal->getCriteria()->getFromDate());
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
                $item->setTime($fromTime);
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
                        $item->setMonCheck(true);
                        $item->setMonTime($proposal->getCriteria()->getMonTime());
                        $item->setTime($proposal->getCriteria()->getMonTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['tueMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['tueMaxPickupTime'])) {
                        $item->setTueCheck(true);
                        $item->setTueTime($proposal->getCriteria()->getTueTime());
                        $item->setTime($proposal->getCriteria()->getTueTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['wedMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['wedMaxPickupTime'])) {
                        $item->setWedCheck(true);
                        $item->setWedTime($proposal->getCriteria()->getWedTime());
                        $item->setTime($proposal->getCriteria()->getWedTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['thuMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['thuMaxPickupTime'])) {
                        $item->setThuCheck(true);
                        $item->setThuTime($proposal->getCriteria()->getThuTime());
                        $item->setTime($proposal->getCriteria()->getThuTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['friMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['friMaxPickupTime'])) {
                        $item->setFriCheck(true);
                        $item->setFriTime($proposal->getCriteria()->getFriTime());
                        $item->setTime($proposal->getCriteria()->getFriTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['satMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['satMaxPickupTime'])) {
                        $item->setSatCheck(true);
                        $item->setSatTime($proposal->getCriteria()->getSatTime());
                        $item->setTime($proposal->getCriteria()->getSatTime());
                    }
                    if (isset($matching['offer']->getFilters()['pickup']['sunMinPickupTime']) && isset($matching['offer']->getFilters()['pickup']['sunMaxPickupTime'])) {
                        $item->setSunCheck(true);
                        $item->setSunTime($proposal->getCriteria()->getSunTime());
                        $item->setTime($proposal->getCriteria()->getSunTime());
                    }
                    $driverFromTime = $item->getTime();
                } else {
                    // no pick up times, it must be the matching results of a search (and not an ad)
                    // the days are the carpooler days
                    $item->setMonCheck($matching['offer']->getProposalOffer()->getCriteria()->isMonCheck());
                    $item->setTueCheck($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck());
                    $item->setWedCheck($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck());
                    $item->setThuCheck($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck());
                    $item->setFriCheck($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck());
                    $item->setSatCheck($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck());
                    $item->setSunCheck($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck());
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
                        $item->setMonTime($monTime);
                        $item->setTime($monTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isTueCheck()) {
                        $tueTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getTueTime();
                        $driverFromTime = clone $tueTime;
                        if ($pickupDuration) {
                            $tueTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setTueTime($tueTime);
                        $item->setTime($tueTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isWedCheck()) {
                        $wedTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getWedTime();
                        $driverFromTime = clone $wedTime;
                        if ($pickupDuration) {
                            $wedTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setWedTime($wedTime);
                        $item->setTime($wedTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isThuCheck()) {
                        $thuTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getThuTime();
                        $driverFromTime = clone $thuTime;
                        if ($pickupDuration) {
                            $thuTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setThuTime($thuTime);
                        $item->setTime($thuTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isFriCheck()) {
                        $friTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getFriTime();
                        $driverFromTime = clone $friTime;
                        if ($pickupDuration) {
                            $friTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setFriTime($friTime);
                        $item->setTime($friTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isSatCheck()) {
                        $satTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSatTime();
                        $driverFromTime = clone $satTime;
                        if ($pickupDuration) {
                            $satTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSatTime($satTime);
                        $item->setTime($satTime);
                    }
                    if ($matching['offer']->getProposalOffer()->getCriteria()->isSunCheck()) {
                        $sunTime = clone $matching['offer']->getProposalOffer()->getCriteria()->getSunTime();
                        $driverFromTime = clone $sunTime;
                        if ($pickupDuration) {
                            $sunTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSunTime($sunTime);
                        $item->setTime($sunTime);
                    }
                }
                $item->setMultipleTimes();
                if ($item->hasMultipleTimes()) {
                    $item->setTime(null);
                    $driverFromTime = null;
                }
                // fromDate is the max between the search date and the fromDate of the matching proposal
                $item->setFromDate(max(
                    $matching['offer']->getProposalOffer()->getCriteria()->getFromDate(),
                    $proposal->getCriteria()->getFromDate()
                ));
                $item->setToDate($matching['offer']->getProposalOffer()->getCriteria()->getToDate());
            }
            // waypoints of the item
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
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginDriver($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['carpooler']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationDriver($waypoint['address']);
                } elseif ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                    $item->setOriginPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['requester']) {
                    $item->setDestinationPassenger($waypoint['address']);
                }
            }
            $item->setWaypoints($waypoints);
            
            // statistics
            $item->setOriginalDistance($matching['offer']->getFilters()['originalDistance']);
            $item->setAcceptedDetourDistance($matching['offer']->getFilters()['acceptedDetourDistance']);
            $item->setNewDistance($matching['offer']->getFilters()['newDistance']);
            $item->setDetourDistance($matching['offer']->getFilters()['detourDistance']);
            $item->setDetourDistancePercent($matching['offer']->getFilters()['detourDistancePercent']);
            $item->setOriginalDuration($matching['offer']->getFilters()['originalDuration']);
            $item->setAcceptedDetourDuration($matching['offer']->getFilters()['acceptedDetourDuration']);
            $item->setNewDuration($matching['offer']->getFilters()['newDuration']);
            $item->setDetourDuration($matching['offer']->getFilters()['detourDuration']);
            $item->setDetourDurationPercent($matching['offer']->getFilters()['detourDurationPercent']);
            $item->setCommonDistance($matching['offer']->getFilters()['commonDistance']);

            // prices

            // we set the prices of the driver (the carpooler)
            $item->setDriverPriceKm($matching['offer']->getProposalOffer()->getCriteria()->getPriceKm());
            $item->setDriverOriginalPrice($matching['offer']->getProposalOffer()->getCriteria()->getDriverPrice());
            
            // we set the prices of the passenger (the requester)
            if ($proposal->getCriteria()->getPriceKm()) {
                $item->setPassengerPriceKm($proposal->getCriteria()->getPriceKm());
            } else {
                // otherwise we use the common price
                $item->setPassengerPriceKm($this->params['defaultPriceKm']);
            }
            // if the requester price is set we use it
            if ($proposal->getCriteria()->getPassengerPrice()) {
                $item->setPassengerOriginalPrice($proposal->getCriteria()->getPassengerPrice());
            } else {
                // otherwise we use the common price
                $item->setPassengerOriginalPrice((string)$this->formatDataManager->roundPrice((int)$matching['offer']->getFilters()['commonDistance']*(float)$item->getPassengerPriceKm()/1000, $proposal->getCriteria()->getFrequency()));
            }
            
            // the computed price is the price to be paid by the passenger
            // it's ((common distance + detour distance) * driver price by km)
            $item->setComputedPrice((string)(((int)$matching['offer']->getFilters()['commonDistance']+(int)$matching['offer']->getFilters()['detourDistance'])*(float)$item->getDriverPriceKm()/1000));
            $item->setComputedRoundedPrice((string)$this->formatDataManager->roundPrice((float)$item->getComputedPrice(), $proposal->getCriteria()->getFrequency()));
            if (!$return) {
                $resultPassenger->setOutward($item);
            } else {
                $resultPassenger->setReturn($item);
            }

            // seats
            $resultPassenger->setSeatsDriver($matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() ? $matching['offer']->getProposalOffer()->getCriteria()->getSeatsDriver() : 1);
            $result->setResultPassenger($resultPassenger);
        }

        $result->setCommunities($communities);

        return $result;
    }

    /**
     * Order the results
     *
     * @param array $results    The array of results to order
     * @param array|null $order The order criteria
     * @return array    The results ordered
     */
    public function orderResults(array $results, ?array $order=null)
    {
        $criteria = null;
        $value = null;
        if (is_array($order) && isset($order['order']) && is_array($order['order']) && isset($order['order']['criteria'])) {
            $criteria = $order['order']['criteria'];
        }
        if (is_array($order) && isset($order['order']) && is_array($order['order']) && isset($order['order']['value'])) {
            $value = $order['order']['value'];
        }
        usort($results, function ($a, $b) use ($criteria,$value) {
            $return = -1;
            switch ($criteria) {
                case "date":
                    ($value=="ASC") ? $return = $a->getDate() <=> $b->getDate() : $return = $b->getDate() <=> $a->getDate();
                break;
            }
            return $return;
        });

        return $results;
    }

    /**
     * Filter the results
     *
     * @param array $results        The array of results to filter
     * @param array|null $filters   The array of filters to apply (applied successively in the order of the array)
     * @return array    The results filtered
     */
    public function filterResults(array $results, ?array $filters=null)
    {
        if ($filters !== null && isset($filters['filters']) && $filters['filters']!==null) {
            foreach ($filters['filters'] as $field => $value) {
                if (is_null($value)) {
                    continue;
                }
                $results = array_filter($results, function ($a) use ($field,$value) {
                    $return = true;
                    switch ($field) {
                        // Filter on Time (the hour)
                        case "time":
                            $value = new \DateTime(str_replace("h", ":", $value));
                            $return = $a->getTime()->format("H") === $value->format("H");
                            break;
                        // Filter on Role (driver, passenger, both)
                        case "role":
                            $return = self::filterByRole($a, $value);
                            break;
                        // Filter on Gender
                        case "gender":
                            $return = $a->getCarpooler()->getGender() == $value;
                            break;
                        // Filter on a Community
                        case "community":
                            $return = array_key_exists($value, $a->getCommunities());
                            break;
                    }
                    return $return;
                });
            }
        }

        return $results;
    }

    /**
     * Check if the given result complies with the given role
     *
     * @param Result $result    The result to test
     * @param integer $role     The role
     * @return bool
     */
    private static function filterByRole(Result $result, int $role)
    {
        switch ($role) {
            case Ad::ROLE_DRIVER: return !is_null($result->getResultPassenger()) && is_null($result->getResultDriver());
            case Ad::ROLE_PASSENGER: return !is_null($result->getResultDriver()) && is_null($result->getResultPassenger());
            case Ad::ROLE_DRIVER_OR_PASSENGER: return !is_null($result->getResultDriver()) && !is_null($result->getResultPassenger());
        }
        return false;
    }

    /**
     * Create "user-friendly" results for the asks of an ad
     * An Ad can have multiple asks, all linked (as a driver, as a passenger, each for outward and return)
     * The results are different if they are computed for the driver or the passenger
     *
     * @param Ask $ask      The master ask
     * @param int $userId   The id of the user that makes the request
     * @return array        The array of results
     */
    public function createAskResults(Ask $ask, int $userId)
    {
        $result = new Result();
        $result->setId($ask->getId());

        $resultDriver = null;
        $resultPassenger = null;

        $role = Ad::ROLE_DRIVER;

        // get the requester role, it depends on the status
        switch ($ask->getStatus()) {
            case Ask::STATUS_INITIATED:
                if ($ask->getMatching()->getProposalOffer()->getUser()->getId() == $userId) {
                    // the requester is the driver
                    $role = Ad::ROLE_DRIVER;
                } else {
                    // the requester is the passenger
                    $role = Ad::ROLE_PASSENGER;
                }
                break;
            case Ask::STATUS_PENDING_AS_DRIVER:
            case Ask::STATUS_ACCEPTED_AS_DRIVER:
            case Ask::STATUS_DECLINED_AS_DRIVER:
                // the requester is the driver
                $role = Ad::ROLE_DRIVER;
                break;
            case Ask::STATUS_PENDING_AS_PASSENGER:
            case Ask::STATUS_ACCEPTED_AS_PASSENGER:
            case Ask::STATUS_DECLINED_AS_PASSENGER:
                // the requester is the passenger
                $role = Ad::ROLE_PASSENGER;
                break;
        }

        // we create the ResultRole for the ask
        if ($role == Ad::ROLE_DRIVER) {
            $resultDriver = $this->createAskResultRole($ask, $role);
        } else {
            $resultPassenger = $this->createAskResultRole($ask, $role);
        }

        // we check if there's an opposite
        if ($ask->getAskOpposite()) {
            // we create the opposite ResultRole for the ask
            if ($role == Ad::ROLE_DRIVER) {
                $resultPassenger = $this->createAskResultRole($ask->getAskOpposite(), Ad::ROLE_PASSENGER);
            } else {
                $resultDriver = $this->createAskResultRole($ask->getAskOpposite(), Ad::ROLE_DRIVER);
            }
        }
        
        $result->setResultDriver($resultDriver);
        $result->setResultPassenger($resultPassenger);

        // create the global result
        $result->setCarpooler($ask->getUser()->getId() == $userId ? $ask->getUserRelated() : $ask->getUser());
        $result->setFrequency($ask->getCriteria()->getFrequency());
        $result->setFrequencyResult($ask->getCriteria()->getFrequency());
        $result = $this->createGlobalResult($result, $ask->getWaypoints());

        // return the result
        return $result;
    }

    /**
     * Create a ResultRole for a given Ask
     *
     * @param Ask $ask      The ask
     * @param int $role     The role of the requester
     * @return ResultRole   The resultRole
     */
    private function createAskResultRole(Ask $ask, int $role)
    {
        $resultRole = new ResultRole();
        $resultRole->setSeatsDriver($ask->getCriteria()->getSeatsDriver());
        $resultRole->setSeatsPassenger($ask->getCriteria()->getSeatsPassenger());

        $outward = null;
        $return = null;

        // we create the results for the outward
        $outward = $this->createAskResultItem($ask, $role);

        // we create the results for the return
        if ($ask->getAskLinked()) {
            $return = $this->createAskResultItem($ask->getAskLinked(), $role);
        }
        
        // we return the result
        $resultRole->setOutward($outward);
        $resultRole->setReturn($return);
        return $resultRole;
    }

    /**
     * Create a ResultItem for a given Ask
     *
     * @param Ask $ask      The ask
     * @param int $role     The role of the requester
     * @return ResultItem   The resultItem
     */
    private function createAskResultItem(Ask $ask, int $role)
    {
        // we compute the filters
        if (is_null($ask->getFilters())) {
            $ask->setFilters($this->proposalMatcher->getAskFilters($ask));
        }

        $item = new ResultItem();

        if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            // the ask is punctual; for now the time are the same
            // todo : use the real requester time if it has only been copied from the carpooler time
            $item->setDate($ask->getCriteria()->getFromDate());
            $item->setTime($ask->getCriteria()->getFromTime());
        } else {
            // the ask is regular, the days depends on the ask status
            $item->setMonCheck($ask->getCriteria()->isMonCheck());
            $item->setTueCheck($ask->getCriteria()->isTueCheck());
            $item->setWedCheck($ask->getCriteria()->isWedCheck());
            $item->setThuCheck($ask->getCriteria()->isThuCheck());
            $item->setFriCheck($ask->getCriteria()->isFriCheck());
            $item->setSatCheck($ask->getCriteria()->isSatCheck());
            $item->setSunCheck($ask->getCriteria()->isSunCheck());
            $hasTime = false;
            $driverFromTime = null;
            if ($ask->getCriteria()->getMonTime()) {
                $item->setMonTime($ask->getCriteria()->getMonTime());
                $item->setTime($item->getMonTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getTueTime()) {
                $item->setTueTime($ask->getCriteria()->getTueTime());
                $item->setTime($item->getTueTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getWedTime()) {
                $item->setWedTime($ask->getCriteria()->getWedTime());
                $item->setTime($item->getWedTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getThuTime()) {
                $item->setThuTime($ask->getCriteria()->getThuTime());
                $item->setTime($item->getThuTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getFriTime()) {
                $item->setFriTime($ask->getCriteria()->getFriTime());
                $item->setTime($item->getFriTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getSatTime()) {
                $item->setSatTime($ask->getCriteria()->getSatTime());
                $item->setTime($item->getSatTime());
                $hasTime = true;
            }
            if ($ask->getCriteria()->getSunTime()) {
                $item->setSunTime($ask->getCriteria()->getSunTime());
                $item->setTime($item->getSunTime());
                $hasTime = true;
            }
            if (!$hasTime) {
                // no time has been set, we have to compute them
                // it can be the case after a regular search, as the times are not asked
                if ($role == Ad::ROLE_DRIVER) {
                    // we calculate the starting time so that the driver will get the carpooler on the carpooler time
                    $filters = $ask->getFilters();
                    $pickupDuration = null;
                    foreach ($filters['route'] as $value) {
                        if ($value['candidate'] == 2 && $value['position'] == 0) {
                            $pickupDuration = (int)round($value['duration']);
                            break;
                        }
                    }
                    // we init the time to the one of the carpooler
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isMonCheck()) {
                        $monTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getMonTime();
                        if ($pickupDuration) {
                            $monTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setMonTime($monTime);
                        $item->setTime($monTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isTueCheck()) {
                        $tueTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getTueTime();
                        if ($pickupDuration) {
                            $tueTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setTueTime($tueTime);
                        $item->setTime($tueTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isWedCheck()) {
                        $wedTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getWedTime();
                        if ($pickupDuration) {
                            $wedTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setWedTime($wedTime);
                        $item->setTime($wedTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isThuCheck()) {
                        $thuTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getThuTime();
                        if ($pickupDuration) {
                            $thuTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setThuTime($thuTime);
                        $item->setTime($thuTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isFriCheck()) {
                        $friTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getFriTime();
                        if ($pickupDuration) {
                            $friTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setFriTime($friTime);
                        $item->setTime($friTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSatCheck()) {
                        $satTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSatTime();
                        if ($pickupDuration) {
                            $satTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSatTime($satTime);
                        $item->setTime($satTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSunCheck()) {
                        $sunTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSunTime();
                        if ($pickupDuration) {
                            $sunTime->sub(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSunTime($sunTime);
                        $item->setTime($sunTime);
                    }
                } else {
                    $filters = $ask->getFilters();
                    $pickupDuration = null;
                    foreach ($filters['route'] as $value) {
                        if ($value['candidate'] == 2 && $value['position'] == 0) {
                            $pickupDuration = (int)round($value['duration']);
                            break;
                        }
                    }
                    // we init the time to the one of the carpooler
                    // as the times are not set, it means the offer times are not set, that's why we use the request times !
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isMonCheck()) {
                        $monTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getMonTime();
                        $driverFromTime = clone $monTime;
                        if ($pickupDuration) {
                            $monTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setMonTime($monTime);
                        $item->setTime($monTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isTueCheck()) {
                        $tueTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getTueTime();
                        $driverFromTime = clone $tueTime;
                        if ($pickupDuration) {
                            $tueTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setTueTime($tueTime);
                        $item->setTime($tueTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isWedCheck()) {
                        $wedTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getWedTime();
                        $driverFromTime = clone $wedTime;
                        if ($pickupDuration) {
                            $wedTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setWedTime($wedTime);
                        $item->setTime($wedTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isThuCheck()) {
                        $thuTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getThuTime();
                        $driverFromTime = clone $thuTime;
                        if ($pickupDuration) {
                            $thuTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setThuTime($thuTime);
                        $item->setTime($thuTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isFriCheck()) {
                        $friTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getFriTime();
                        $driverFromTime = clone $friTime;
                        if ($pickupDuration) {
                            $friTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setFriTime($friTime);
                        $item->setTime($friTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSatCheck()) {
                        $satTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSatTime();
                        $driverFromTime = clone $satTime;
                        if ($pickupDuration) {
                            $satTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSatTime($satTime);
                        $item->setTime($satTime);
                    }
                    if ($ask->getMatching()->getProposalRequest()->getCriteria()->isSunCheck()) {
                        $sunTime = clone $ask->getMatching()->getProposalRequest()->getCriteria()->getSunTime();
                        $driverFromTime = clone $sunTime;
                        if ($pickupDuration) {
                            $sunTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                        }
                        $item->setSunTime($sunTime);
                        $item->setTime($sunTime);
                    }
                }
            }
            $item->setMultipleTimes($hasTime);
            if ($item->hasMultipleTimes()) {
                $item->setTime(null);
                $driverFromTime = null;
            }
            $item->setFromDate($ask->getCriteria()->getFromDate());
            $item->setToDate($ask->getCriteria()->getToDate());
        }
        // waypoints of the outward
        $waypoints = [];
        if ($role == Ad::ROLE_DRIVER) {
            $time = $item->getTime() ? clone $item->getTime() : null;
        } else {
            $time = $driverFromTime ? clone $driverFromTime : null;
        }
        
        // we will have to compute the number of steps for each candidate
        $steps = [
            'requester' => 0,
            'carpooler' => 0
        ];
        // first pass to get the maximum position fo each candidate
        foreach ($ask->getFilters()['route'] as $key=>$waypoint) {
            if ($role == Ad::ROLE_DRIVER) {
                if ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['requester']) {
                    $steps['requester'] = (int)$waypoint['position'];
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['carpooler']) {
                    $steps['carpooler'] = (int)$waypoint['position'];
                }
            } else {
                if ($waypoint['candidate'] == 1 && (int)$waypoint['position']>$steps['carpooler']) {
                    $steps['carpooler'] = (int)$waypoint['position'];
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position']>$steps['requester']) {
                    $steps['requester'] = (int)$waypoint['position'];
                }
            }
        }
        // second pass to fill the waypoints array
        foreach ($ask->getFilters()['route'] as $key=>$waypoint) {
            $curTime = null;
            if ($time) {
                $curTime = clone $time;
            }
            if ($curTime) {
                $curTime->add(new \DateInterval('PT' . (int)round($waypoint['duration']) . 'S'));
            }
            if ($role == Ad::ROLE_DRIVER) {
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
            } else {
                $waypoints[$key] = [
                    'id' => $key,
                    'person' => $waypoint['candidate'] == 1 ? 'carpooler' : 'requester',
                    'role' => $waypoint['candidate'] == 1 ? 'driver' : 'passenger',
                    'time' =>  $curTime,
                    'address' => $waypoint['address'],
                    'type' => $waypoint['position'] == '0' ? 'origin' :
                        (
                            ($waypoint['candidate'] == 1) ? ((int)$waypoint['position'] == $steps['carpooler'] ? 'destination' : 'step') :
                            ((int)$waypoint['position'] == $steps['requester'] ? 'destination' : 'step')
                        )
                ];
            }
            // origin and destination guess
            if ($role == Ad::ROLE_DRIVER) {
                if ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['carpooler']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                    $item->setOriginDriver($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['requester']) {
                    $item->setDestinationDriver($waypoint['address']);
                }
            } else {
                if ($waypoint['candidate'] == 2 && $waypoint['position'] == '0') {
                    $item->setOrigin($waypoint['address']);
                    $item->setOriginPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 2 && (int)$waypoint['position'] == $steps['requester']) {
                    $item->setDestination($waypoint['address']);
                    $item->setDestinationPassenger($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && $waypoint['position'] == '0') {
                    $item->setOriginDriver($waypoint['address']);
                } elseif ($waypoint['candidate'] == 1 && (int)$waypoint['position'] == $steps['carpooler']) {
                    $item->setDestinationDriver($waypoint['address']);
                }
            }
        }
        $item->setWaypoints($waypoints);
        
        // statistics
        $item->setOriginalDistance($ask->getFilters()['originalDistance']);
        $item->setAcceptedDetourDistance($ask->getFilters()['acceptedDetourDistance']);
        $item->setNewDistance($ask->getFilters()['newDistance']);
        $item->setDetourDistance($ask->getFilters()['detourDistance']);
        $item->setDetourDistancePercent($ask->getFilters()['detourDistancePercent']);
        $item->setOriginalDuration($ask->getFilters()['originalDuration']);
        $item->setAcceptedDetourDuration($ask->getFilters()['acceptedDetourDuration']);
        $item->setNewDuration($ask->getFilters()['newDuration']);
        $item->setDetourDuration($ask->getFilters()['detourDuration']);
        $item->setDetourDurationPercent($ask->getFilters()['detourDurationPercent']);
        $item->setCommonDistance($ask->getFilters()['commonDistance']);

        // prices
        $item->setDriverPriceKm($ask->getMatching()->getProposalOffer()->getCriteria()->getPriceKm());
        $item->setDriverOriginalPrice($ask->getMatching()->getProposalOffer()->getCriteria()->getDriverPrice());
        $item->setPassengerPriceKm($ask->getMatching()->getProposalRequest()->getCriteria()->getPriceKm());
        $item->setPassengerOriginalPrice($ask->getMatching()->getProposalRequest()->getCriteria()->getPassengerPrice());
        // to check...
        $item->setComputedPrice($ask->getCriteria()->getPassengerComputedPrice());
        $item->setComputedRoundedPrice($ask->getCriteria()->getPassengerComputedRoundedPrice());

        return $item;
    }
}
