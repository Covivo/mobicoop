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

use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Criteria;
use App\Carpool\Repository\ProposalRepository;
use App\Match\Service\GeoMatcher;
use App\Match\Entity\Candidate;
use App\Carpool\Entity\Waypoint;
use App\Geography\Service\GeoRouter;
use App\User\Entity\User;
use Psr\Log\LoggerInterface;

/**
 * Matching analyzer service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalMatcher
{
    // max default detour distance
    // TODO : should depend on the total distance : total distance => max detour allowed
    public const MAX_DETOUR_DISTANCE_PERCENT = 33;
    public const MAX_DETOUR_DURATION_PERCENT = 33;

    // minimum distance to check the common distance
    public const MIN_COMMON_DISTANCE_CHECK = 100;
    // minimum common distance accepted
    public const MIN_COMMON_DISTANCE_PERCENT = 30;

    // behaviour in case of multiple matches for the same candidates
    // 1 = keep fastest route
    // 2 = keep shortest route
    // 3 = keep all routes
    public const MULTI_MATCHES_FOR_SAME_CANDIDATES = self::MULTI_MATCHES_FOR_SAME_CANDIDATES_FASTEST;
    public const MULTI_MATCHES_FOR_SAME_CANDIDATES_FASTEST = 1;
    public const MULTI_MATCHES_FOR_SAME_CANDIDATES_SHORTEST = 2;
    public const MULTI_MATCHES_FOR_SAME_CANDIDATES_ALL = 3;

    private $entityManager;
    private $proposalRepository;
    private $geoMatcher;
    private $geoRouter;
    private $logger;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalRepository $proposalRepository
     * @param GeoMatcher $geoMatcher
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalRepository $proposalRepository, GeoMatcher $geoMatcher, GeoRouter $geoRouter, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->proposalRepository = $proposalRepository;
        $this->geoRouter = $geoRouter;
        $this->geoMatcher = $geoMatcher;
        $this->logger = $logger;
    }

    /**
     * Create Matching proposal entities for a proposal.
     *
     * @param Proposal $proposal    The proposal for which we want the matchings
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return Proposal The proposal with the matchings
     */
    public function createMatchingsForProposal(Proposal $proposal, bool $excludeProposalUser=true)
    {
        $date = new \DateTime("UTC");
        $this->logger->info('Proposal matcher | Start create matchings for proposal ' . $proposal->getId() . ' | ' . $date->format("Ymd H:i:s.u"));

        // we search the matchings
        $matchings = $this->findMatchingProposals($proposal, $excludeProposalUser);
        
        // we assign the matchings to the proposal
        foreach ($matchings as $matching) {
            if ($matching->getProposalOffer() === $proposal) {
                $proposal->addMatchingRequest($matching);
            } else {
                $proposal->addMatchingOffer($matching);
            }
        }
        return $proposal;
    }
    
    /**
     * Get the return matching filters for a proposal.
     * Used to compute the reverse direction of a matching.
     *
     * @param Matching  $matching   The matching
     * @return array The matching return filters
     */
    public function getMatchingReturnFilters(Matching $matching)
    {
        $filters = [];
        $candidateDriver = new Candidate();
        $candidateDriver->setId(!is_null($matching->getProposalOffer()->getUser()) ? $matching->getProposalOffer()->getUser()->getId() : User::DEFAULT_ID);
        $addresses = [];
        foreach ($matching->getProposalOffer()->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        $candidateDriver->setAddresses($addresses);
        // we compute the driver's direction
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            $direction = $routes[0];
            $candidateDriver->setDirection($direction);
            $candidateDriver->setMaxDetourDistance($direction->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100);
            $candidateDriver->setMaxDetourDuration($direction->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100);
        }

        $candidatePassenger = new Candidate();
        $candidatePassenger->setId(!is_null($matching->getProposalRequest()->getUser()) ? $matching->getProposalRequest()->getUser()->getId() : User::DEFAULT_ID);
        $addressesCandidate = [];
        foreach ($matching->getProposalRequest()->getWaypoints() as $waypoint) {
            $addressesCandidate[] = $waypoint->getAddress();
        }
        $candidatePassenger->setAddresses($addressesCandidate);
        if ($routes = $this->geoRouter->getRoutes([$addressesCandidate[0],$addressesCandidate[count($addressesCandidate)-1]])) {
            $candidatePassenger->setDirection($routes[0]);
        }
        if ($matches = $this->geoMatcher->forceMatch($candidateDriver, $candidatePassenger)) {
            // many matches can be found for 2 candidates : if multiple routes satisfy the criteria
            if (is_array($matches) && count($matches)>0) {
                switch (self::MULTI_MATCHES_FOR_SAME_CANDIDATES) {
                    case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_FASTEST:
                        usort($matches, self::build_sorter('newDuration'));
                        $filters = $matches[0];
                        break;
                    case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_SHORTEST:
                        usort($matches, self::build_sorter('newDistance'));
                        $filters = $matches[0];
                        break;
                    default:
                        $filters = $matches[0];
                        break;
                }
            }
        }
          
        return $filters;
    }

    /**
     * Find matching proposals for a proposal.
     * Returns an array of Matching objects.
     *
     * @param Proposal $proposal
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return array|NULL
     */
    public function findMatchingProposals(Proposal $proposal, bool $excludeProposalUser=true)
    {
        $date = new \DateTime("UTC");
        if (is_null($proposal->getMatchingProposal())) {
            $this->logger->info('Proposal matcher | Start find matchings for proposal ' . $proposal->getId() . ' without forced matching | ' . $date->format("Ymd H:i:s.u"));
            // we search matching proposals in the database
            // if no proposals are found we return an empty array
            if (!$proposalsFound = $this->proposalRepository->findMatchingProposals($proposal, $excludeProposalUser)) {
                return [];
            }
        } else {
            $this->logger->info('Proposal matcher | Start find matchings for proposal ' . $proposal->getId() . ' with forced matching ' . $proposal->getMatchingProposal()->getId() . ' | ' . $date->format("Ymd H:i:s.u"));
            // we have to force the matching with the given proposal
            // we first check if it's a return trip : if there's a matchingLinked in the proposal, we need to use the proposalLinked of the matchingProposal
            if (!is_null($proposal->getMatchingLinked())) {
                $this->logger->info('Proposal matcher | Matching linked ' . $proposal->getMatchingLinked()->getId() . ' | ' . $date->format("Ymd H:i:s.u"));
                $proposalsFound[] = $proposal->getMatchingProposal()->getProposalLinked();
            } else {
                $this->logger->info('Proposal matcher | No matching linked | ' . $date->format("Ymd H:i:s.u"));
                $proposalsFound[] = $proposal->getMatchingProposal();
            }
        }
        
        $matchings = [];

        // we filter with geomatcher
        $candidateProposal = new Candidate();
        if ($proposal->getUser()) {
            $candidateProposal->setId($proposal->getUser()->getId());
        } else {
            $candidateProposal->setId(User::DEFAULT_ID);
        }
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        $candidateProposal->setAddresses($addresses);
        if ($proposal->getCriteria()->isDriver()) {
            $candidateProposal->setMaxDetourDistance($proposal->getCriteria()->getMaxDetourDistance() ? $proposal->getCriteria()->getMaxDetourDistance() : ($proposal->getCriteria()->getDirectionDriver()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
            $candidateProposal->setMaxDetourDuration($proposal->getCriteria()->getMaxDetourDuration() ? $proposal->getCriteria()->getMaxDetourDuration() : ($proposal->getCriteria()->getDirectionDriver()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
            $candidateProposal->setDirection($proposal->getCriteria()->getDirectionDriver());
            foreach ($proposalsFound as $proposalToMatch) {
                // if the candidate is not passenger we skip (the 2 candidates could be driver AND passenger, and the second one match only as a driver)
                if (!$proposalToMatch->getCriteria()->isPassenger()) {
                    continue;
                }
                $candidate = new Candidate();
                $candidate->setId($proposalToMatch->getUser()->getId());
                $addressesCandidate = [];
                foreach ($proposalToMatch->getWaypoints() as $waypoint) {
                    $addressesCandidate[] = $waypoint->getAddress();
                }
                $candidate->setAddresses($addressesCandidate);
                $candidate->setDirection($proposalToMatch->getCriteria()->getDirectionPassenger());
                // the 2 following are not taken in account right now as only the driver detour matters
                $candidate->setMaxDetourDistance($proposalToMatch->getCriteria()->getMaxDetourDistance() ? $proposalToMatch->getCriteria()->getMaxDetourDistance() : ($proposalToMatch->getCriteria()->getDirectionPassenger()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
                $candidate->setMaxDetourDuration($proposalToMatch->getCriteria()->getMaxDetourDuration() ? $proposalToMatch->getCriteria()->getMaxDetourDuration() : ($proposalToMatch->getCriteria()->getDirectionPassenger()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
                if ($matches = $this->geoMatcher->singleMatch($candidateProposal, [$candidate], true)) {
                    // many matches can be found for 2 candidates : if multiple routes satisfy the criteria
                    if (is_array($matches) && count($matches)>0) {
                        switch (self::MULTI_MATCHES_FOR_SAME_CANDIDATES) {
                            case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_FASTEST:
                                usort($matches, self::build_sorter('newDuration'));
                                $matching = new Matching();
                                $matching->setProposalOffer($proposal);
                                $matching->setProposalRequest($proposalToMatch);
                                $matching->setFilters($matches[0]);
                                $matchings[] = $matching;
                                break;
                            case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_SHORTEST:
                                usort($matches, self::build_sorter('newDistance'));
                                $matching = new Matching();
                                $matching->setProposalOffer($proposal);
                                $matching->setProposalRequest($proposalToMatch);
                                $matching->setFilters($matches[0]);
                                $matchings[] = $matching;
                                break;
                            default:
                                foreach ($matches as $match) {
                                    $matching = new Matching();
                                    $matching->setProposalOffer($proposal);
                                    $matching->setProposalRequest($proposalToMatch);
                                    $matching->setFilters($match);
                                    $matchings[] = $matching;
                                }
                                break;
                        }
                    }
                }
            }
        }

        if ($proposal->getCriteria()->isPassenger()) {
            $candidateProposal->setDirection($proposal->getCriteria()->getDirectionPassenger());
            // the 2 following are not taken in account right now as only the driver detour matters
            $candidateProposal->setMaxDetourDistance($proposal->getCriteria()->getMaxDetourDistance() ? $proposal->getCriteria()->getMaxDetourDistance() : ($proposal->getCriteria()->getDirectionPassenger()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
            $candidateProposal->setMaxDetourDuration($proposal->getCriteria()->getMaxDetourDuration() ? $proposal->getCriteria()->getMaxDetourDuration() : ($proposal->getCriteria()->getDirectionPassenger()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
            foreach ($proposalsFound as $proposalToMatch) {
                // if the candidate is not driver we skip (the 2 candidates could be driver AND passenger, and the second one match only as a passenger)
                if (!$proposalToMatch->getCriteria()->isDriver()) {
                    continue;
                }
                $candidate = new Candidate();
                $candidate->setId($proposalToMatch->getUser()->getId());
                $addressesCandidate = [];
                foreach ($proposalToMatch->getWaypoints() as $waypoint) {
                    $addressesCandidate[] = $waypoint->getAddress();
                }
                $candidate->setAddresses($addressesCandidate);
                $candidate->setDirection($proposalToMatch->getCriteria()->getDirectionDriver());
                $candidate->setMaxDetourDistance($proposalToMatch->getCriteria()->getMaxDetourDistance() ? $proposalToMatch->getCriteria()->getMaxDetourDistance() : ($proposalToMatch->getCriteria()->getDirectionDriver()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
                $candidate->setMaxDetourDuration($proposalToMatch->getCriteria()->getMaxDetourDuration() ? $proposalToMatch->getCriteria()->getMaxDetourDuration() : ($proposalToMatch->getCriteria()->getDirectionDriver()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
                //echo $proposalToMatch->getCriteria()->getDirectionDriver()->getDistance() . "/" . $candidate->getMaxDetourDistance() . " " . $proposalToMatch->getCriteria()->getDirectionDriver()->getDuration() . "/" . $candidate->getMaxDetourDuration() . "\n";
                if ($matches = $this->geoMatcher->singleMatch($candidateProposal, [$candidate], false)) {
                    // many matches can be found for 2 candidates : if multiple routes satisfy the criteria
                    if (is_array($matches) && count($matches)>0) {
                        switch (self::MULTI_MATCHES_FOR_SAME_CANDIDATES) {
                            case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_FASTEST:
                                usort($matches, self::build_sorter('newDuration'));
                                $matching = new Matching();
                                $matching->setProposalOffer($proposalToMatch);
                                $matching->setProposalRequest($proposal);
                                $matching->setFilters($matches[0]);
                                $matchings[] = $matching;
                                break;
                            case self::MULTI_MATCHES_FOR_SAME_CANDIDATES_SHORTEST:
                                usort($matches, self::build_sorter('newDistance'));
                                $matching = new Matching();
                                $matching->setProposalOffer($proposalToMatch);
                                $matching->setProposalRequest($proposal);
                                $matching->setFilters($matches[0]);
                                $matchings[] = $matching;
                                break;
                            default:
                                foreach ($matches as $match) {
                                    $matching = new Matching();
                                    $matching->setProposalOffer($proposalToMatch);
                                    $matching->setProposalRequest($proposal);
                                    $matching->setFilters($match);
                                    $matchings[] = $matching;
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        // if we use times, we check if the pickup times match
        if (
            ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL && $proposal->getCriteria()->getFromTime()) ||
            ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && (
                ($proposal->getCriteria()->isMonCheck() && $proposal->getCriteria()->getMonTime()) ||
                ($proposal->getCriteria()->isTueCheck() && $proposal->getCriteria()->getTueTime()) ||
                ($proposal->getCriteria()->isWedCheck() && $proposal->getCriteria()->getWedTime()) ||
                ($proposal->getCriteria()->isThuCheck() && $proposal->getCriteria()->getThuTime()) ||
                ($proposal->getCriteria()->isFriCheck() && $proposal->getCriteria()->getFriTime()) ||
                ($proposal->getCriteria()->isSatCheck() && $proposal->getCriteria()->getSatTime()) ||
                ($proposal->getCriteria()->isSunCheck() && $proposal->getCriteria()->getSunTime())
            ))
        ) {
            $matchings = $this->checkPickUp($matchings);
        }
        
        // we complete the matchings with the waypoints and criteria
        foreach ($matchings as $matching) {

            // if there's a linked matching (for return trip) we set it here
            $matching->setMatchingLinked($proposal->getMatchingLinked());
            
            // waypoints
            foreach ($matching->getFilters()['route'] as $key=>$point) {
                $waypoint = new Waypoint();
                $waypoint->setPosition($key);
                $waypoint->setDestination(false);
                if ($key == (count($matching->getFilters()['route'])-1)) {
                    $waypoint->setDestination(true);
                }
                $waypoint->setAddress(clone $point['address']);
                $matching->addWaypoint($waypoint);
            }

            // criteria
            $matchingCriteria = new Criteria();
            $matchingCriteria->setDriver(true);
            $matchingCriteria->setDirectionDriver($matching->getFilters()['direction']);
            $matchingCriteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            $matchingCriteria->setStrictDate($matching->getProposalOffer()->getCriteria()->isStrictDate());
            $matchingCriteria->setAnyRouteAsPassenger(true);
            
            // prices
            // we use the driver's priceKm
            $matchingCriteria->setPriceKm($matching->getProposalOffer()->getCriteria()->getPriceKm());
            // we use the passenger's computed prices
            $matchingCriteria->setComputedPrice($matching->getProposalRequest()->getCriteria()->getComputedPrice());
            $matchingCriteria->setComputedRoundedPrice($matching->getProposalRequest()->getCriteria()->getComputedRoundedPrice());

            // we're using the driver price if there's no "forced" matching
            // if (is_null($proposal->getMatchingProposal())) {
            //     $matchingCriteria->setPriceKm($matching->getProposalOffer()->getCriteria()->getPriceKm());
            // } else {
            //   $matchingCriteria->setPriceKm($matching->getProposalOffer()->getCriteria()->getPriceKm());
            // }
            
            if ($matching->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching->getProposalRequest()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $matchingCriteria->setFrequency(Criteria::FREQUENCY_REGULAR);
                $matchingCriteria->setFromDate(max($matching->getProposalOffer()->getCriteria()->getFromDate(), $matching->getProposalRequest()->getCriteria()->getFromDate()));
                $matchingCriteria->setToDate(min($matching->getProposalOffer()->getCriteria()->getToDate(), $matching->getProposalRequest()->getCriteria()->getToDate()));
            } elseif ($matching->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                $matchingCriteria->setFromDate($matching->getProposalOffer()->getCriteria()->getFromDate());
            } else {
                $matchingCriteria->setFromDate($matching->getProposalRequest()->getCriteria()->getFromDate());
            }
            $matchingCriteria->setSeats(1);
            if (isset($matching->getFilters()['pickup']['minPickupTime']) && isset($matching->getFilters()['pickup']['maxPickupTime'])) {
                if ($matching->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getMinTime());
                    $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getMaxTime());
                    $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getMarginDuration());
                    $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getFromTime());
                } else {
                    switch ($matchingCriteria->getFromDate()->format('w')) {
                        case 0:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getSunMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getSunMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getSunMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getSunTime());
                            break;
                        case 1:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getMonMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getMonMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getMonMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getMonTime());
                            break;
                        case 2:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getTueMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getTueMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getTueMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getTueTime());
                            break;
                        case 3:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getWedMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getWedMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getWedMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getWedTime());
                            break;
                        case 4:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getThuMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getThuMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getThuMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getThuTime());
                            break;
                        case 5:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getFriMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getFriMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getFriMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getFriTime());
                            break;
                        case 6:
                            $matchingCriteria->setMinTime($matching->getProposalOffer()->getCriteria()->getSatMinTime());
                            $matchingCriteria->setMaxTime($matching->getProposalOffer()->getCriteria()->getSatMaxTime());
                            $matchingCriteria->setMarginDuration($matching->getProposalOffer()->getCriteria()->getSatMarginDuration());
                            $matchingCriteria->setFromTime($matching->getProposalOffer()->getCriteria()->getSatTime());
                            break;
                    }
                }
            }
            if (isset($matching->getFilters()['pickup']['monMinPickupTime']) && isset($matching->getFilters()['pickup']['monMaxPickupTime'])) {
                $matchingCriteria->setMonCheck(true);
                $matchingCriteria->setMonMinTime($matching->getProposalOffer()->getCriteria()->getMonMinTime());
                $matchingCriteria->setMonMaxTime($matching->getProposalOffer()->getCriteria()->getMonMaxTime());
                $matchingCriteria->setMonMarginDuration($matching->getProposalOffer()->getCriteria()->getMonMarginDuration());
                $matchingCriteria->setMonTime($matching->getProposalOffer()->getCriteria()->getMonTime());
            }
            if (isset($matching->getFilters()['pickup']['tueMinPickupTime']) && isset($matching->getFilters()['pickup']['tueMaxPickupTime'])) {
                $matchingCriteria->setTueCheck(true);
                $matchingCriteria->setTueMinTime($matching->getProposalOffer()->getCriteria()->getTueMinTime());
                $matchingCriteria->setTueMaxTime($matching->getProposalOffer()->getCriteria()->getTueMaxTime());
                $matchingCriteria->setTueMarginDuration($matching->getProposalOffer()->getCriteria()->getTueMarginDuration());
                $matchingCriteria->setTueTime($matching->getProposalOffer()->getCriteria()->getTueTime());
            }
            if (isset($matching->getFilters()['pickup']['wedMinPickupTime']) && isset($matching->getFilters()['pickup']['wedMaxPickupTime'])) {
                $matchingCriteria->setWedCheck(true);
                $matchingCriteria->setWedMinTime($matching->getProposalOffer()->getCriteria()->getWedMinTime());
                $matchingCriteria->setWedMaxTime($matching->getProposalOffer()->getCriteria()->getWedMaxTime());
                $matchingCriteria->setWedMarginDuration($matching->getProposalOffer()->getCriteria()->getWedMarginDuration());
                $matchingCriteria->setWedTime($matching->getProposalOffer()->getCriteria()->getWedTime());
            }
            if (isset($matching->getFilters()['pickup']['thuMinPickupTime']) && isset($matching->getFilters()['pickup']['thuMaxPickupTime'])) {
                $matchingCriteria->setThuCheck(true);
                $matchingCriteria->setThuMinTime($matching->getProposalOffer()->getCriteria()->getThuMinTime());
                $matchingCriteria->setThuMaxTime($matching->getProposalOffer()->getCriteria()->getThuMaxTime());
                $matchingCriteria->setThuMarginDuration($matching->getProposalOffer()->getCriteria()->getThuMarginDuration());
                $matchingCriteria->setThuTime($matching->getProposalOffer()->getCriteria()->getThuTime());
            }
            if (isset($matching->getFilters()['pickup']['friMinPickupTime']) && isset($matching->getFilters()['pickup']['friMaxPickupTime'])) {
                $matchingCriteria->setFriCheck(true);
                $matchingCriteria->setFriMinTime($matching->getProposalOffer()->getCriteria()->getFriMinTime());
                $matchingCriteria->setFriMaxTime($matching->getProposalOffer()->getCriteria()->getFriMaxTime());
                $matchingCriteria->setFriMarginDuration($matching->getProposalOffer()->getCriteria()->getFriMarginDuration());
                $matchingCriteria->setFriTime($matching->getProposalOffer()->getCriteria()->getFriTime());
            }
            if (isset($matching->getFilters()['pickup']['satMinPickupTime']) && isset($matching->getFilters()['pickup']['satMaxPickupTime'])) {
                $matchingCriteria->setSatCheck(true);
                $matchingCriteria->setSatMinTime($matching->getProposalOffer()->getCriteria()->getSatMinTime());
                $matchingCriteria->setSatMaxTime($matching->getProposalOffer()->getCriteria()->getSatMaxTime());
                $matchingCriteria->setSatMarginDuration($matching->getProposalOffer()->getCriteria()->getSatMarginDuration());
                $matchingCriteria->setSatTime($matching->getProposalOffer()->getCriteria()->getSatTime());
            }
            if (isset($matching->getFilters()['pickup']['sunMinPickupTime']) && isset($matching->getFilters()['pickup']['sunMaxPickupTime'])) {
                $matchingCriteria->setSunCheck(true);
                $matchingCriteria->setSunMinTime($matching->getProposalOffer()->getCriteria()->getSunMinTime());
                $matchingCriteria->setSunMaxTime($matching->getProposalOffer()->getCriteria()->getSunMaxTime());
                $matchingCriteria->setSunMarginDuration($matching->getProposalOffer()->getCriteria()->getSunMarginDuration());
                $matchingCriteria->setSunTime($matching->getProposalOffer()->getCriteria()->getSunTime());
            }
            $matching->setCriteria($matchingCriteria);

            // if the search is regular, we compute the potential return trip to get the pickup times
            // (if it hasn't been already computed)
            if (
                is_null($proposal->getMatchingLinked()) &&
                (($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching->getProposalOffer() === $proposal && $matching->getProposalRequest()->getType() != Proposal::TYPE_ONE_WAY) ||
                ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching->getProposalRequest() === $proposal && $matching->getProposalOffer()->getType() != Proposal::TYPE_ONE_WAY))
                ) {
                $matching->setMatchingRelated($this->createRelatedMatching($matching));
            }
            
            // we remove the direction from the filter to reduce the size of the returned object
            // (it is already affected to the driver direction)
            $filters = $matching->getFilters();
            unset($filters['direction']);
            $matching->setFilters($filters);
        }
        return $matchings;
    }

    /**
     * Create a related matching (return matching for a matching proposal with outward/return)
     */
    private function createRelatedMatching(Matching $matching)
    {
        // we compute the return trip
        $matchingRelated = new Matching();
        // we clone the original offer and request proposal, basically we don't need this information...
        // we will just need the waypoints and filters
        $matchingRelated->setProposalOffer(clone $matching->getProposalOffer());
        $matchingRelated->setProposalRequest(clone $matching->getProposalRequest());

        $criteriaLinked = new Criteria();
        $criteriaLinked->setDriver($matching->getCriteria()->isDriver() ? true : false);
        $criteriaLinked->setPassenger($matching->getCriteria()->isPassenger() ? true : false);
        $criteriaLinked->setPriceKm($matching->getCriteria()->getPriceKm());
        $criteriaLinked->setSeats($matching->getCriteria()->getSeats());
        $matchingRelated->setCriteria($criteriaLinked);

        // We use the outward waypoints in reverse order
        $nbDriverWaypoints = count($matching->getProposalOffer()->getWaypoints());
        $reversedDriverWaypoints = $this->getReverseWaypoints($matching->getProposalOffer()->getWaypoints());
        foreach ($reversedDriverWaypoints as $pos=>$matchingWaypoint) {
            $waypoint = clone $matchingWaypoint;
            $waypoint->setPosition($pos);
            $waypoint->setDestination(false);
            // address
            $waypoint->setAddress(clone $matchingWaypoint->getAddress());
            if ($pos == ($nbDriverWaypoints-1)) {
                $waypoint->setDestination(true);
            }
            $matchingRelated->getProposalOffer()->addWaypoint($waypoint);
        }
        $nbPassengerWaypoints = count($matching->getProposalRequest()->getWaypoints());
        $reversedPassengerWaypoints = $this->getReverseWaypoints($matching->getProposalRequest()->getWaypoints());
        foreach ($reversedPassengerWaypoints as $pos=>$matchingWaypoint) {
            $waypoint = clone $matchingWaypoint;
            $waypoint->setPosition($pos);
            $waypoint->setDestination(false);
            // address
            $waypoint->setAddress(clone $matchingWaypoint->getAddress());
            if ($pos == ($nbPassengerWaypoints-1)) {
                $waypoint->setDestination(true);
            }
            $matchingRelated->getProposalRequest()->addWaypoint($waypoint);
        }

        // we compute the directions
        $addresses = [];
        foreach ($matchingRelated->getProposalOffer()->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            $direction = $routes[0];
            // creation of the crossed zones
            $matchingRelated->getCriteria()->setDirectionDriver($direction);
        }
        $addresses = [];
        foreach ($matchingRelated->getProposalRequest()->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            // if the user is passenger we keep only the first and last points
            $routes = $this->geoRouter->getRoutes([$addresses[0],$addresses[count($addresses)-1]]);
            $direction = $routes[0];
            $matchingRelated->getCriteria()->setDirectionPassenger($direction);
        }
        $matchingRelated->setFilters($this->getMatchingReturnFilters($matchingRelated));
        return $matchingRelated;
    }

    /**
     * Create the reversed array of waypoints
     */
    private function getReverseWaypoints(array $waypoints)
    {
        // we need to get the waypoints in reverse order
        // we will read the waypoints a first time to create an array with the position as index
        $aWaypoints = [];
        foreach ($waypoints as $waypoint) {
            $aWaypoints[$waypoint->getPosition()] = $waypoint;
        }
        // we sort the array by key
        ksort($aWaypoints);
        // our array is ordered by position, we read it backwards
        return array_reverse($aWaypoints);
    }

    /**
     * Callback function for array sort
     */
    private static function build_sorter($key)
    {
        return function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? -1 : 1;
        };
    }

    /**
     * Check that pickup times are valid against the given proposals.
     *
     * @param array $matchings  The candidates
     * @return void
     */
    private function checkPickUp(array $matchings)
    {
        $validMatchings = [];
        foreach ($matchings as $matching) {
            $pickupDuration = null;
            $pickupTimes = [];
            $filters = $matching->getFilters();
            foreach ($filters['route'] as $value) {
                if ($value['candidate'] == 2 && $value['position'] == 0) {
                    $pickupDuration = (int)round($value['duration']);
                    break;
                }
            }
            $validPickupTimes = $this->getValidPickupTimes($matching->getProposalOffer(), $matching->getProposalRequest(), $pickupDuration);
            if (count($validPickupTimes)>0) {
                $filters['pickup'] = $validPickupTimes;
                $matching->setFilters($filters);
                $validMatchings[] = $matching;
            }
        }
        return $validMatchings;
    }

    /**
     * Get the valid pickup times for the given proposals
     * Valid = we check the times of both proposals to be sure that they match
     *
     * @param Proposal $proposal1   The driver proposal
     * @param Proposal $proposal2   The passenger proposal
     * @param integer $pickupDuration   The duration from the origin to the pickup point
     * @return void
     */
    private function getValidPickupTimes(Proposal $proposal1, Proposal $proposal2, int $pickupDuration)
    {
        $pickupTime = $minPickupTime = $maxPickupTime = null;
        $monPickupTime = $monMinPickupTime = $monMaxPickupTime = null;
        $tuePickupTime = $tueMinPickupTime = $tueMaxPickupTime = null;
        $wedPickupTime = $wedMinPickupTime = $wedMaxPickupTime = null;
        $thuPickupTime = $thuMinPickupTime = $thuMaxPickupTime = null;
        $friPickupTime = $friMinPickupTime = $friMaxPickupTime = null;
        $satPickupTime = $satMinPickupTime = $satMaxPickupTime = null;
        $sunPickupTime = $sunMinPickupTime = $sunMaxPickupTime = null;
        
        switch ($proposal1->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL: {
                $pickupTime = clone $proposal1->getCriteria()->getFromTime();
                $minPickupTime = clone $proposal1->getCriteria()->getMinTime();
                $maxPickupTime = clone $proposal1->getCriteria()->getMaxTime();
                $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                switch ($proposal2->getCriteria()->getFrequency()) {
                    case Criteria::FREQUENCY_PUNCTUAL: {
                        if (!(
                            ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                            ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                        )) {
                            // not in range
                            $pickupTime = null;
                            $minPickupTime = null;
                            $maxPickupTime = null;
                        }
                        break;
                    }
                    case Criteria::FREQUENCY_REGULAR: {
                        switch ($proposal1->getCriteria()->getFromDate()->format('w')) {
                            case 0: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $minPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getSunMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 1: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMonMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 2: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $minPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getTueMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 3: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $minPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getWedMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 4: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $minPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getThuMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 5: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $minPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getFriMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 6: {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $minPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) ||
                                    ($maxPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getSatMaxTime())
                                )) {
                                    // not in range
                                    $pickupTime = null;
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
                break;
            }
            case Criteria::FREQUENCY_REGULAR: {
                switch ($proposal2->getCriteria()->getFrequency()) {
                    case Criteria::FREQUENCY_PUNCTUAL: {
                        if ($proposal1->getCriteria()->isMonCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 1) {
                            $pickupTime = clone $proposal1->getCriteria()->getMonTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getMonMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getMonMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isTueCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 2) {
                            $pickupTime = clone $proposal1->getCriteria()->getTueTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getTueMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getTueMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isWedCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 3) {
                            $pickupTime = clone $proposal1->getCriteria()->getWedTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getWedMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getWedMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isThuCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 4) {
                            $pickupTime = clone $proposal1->getCriteria()->getThuTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getThuMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getThuMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isFriCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 5) {
                            $pickupTime = clone $proposal1->getCriteria()->getFriTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getFriMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getFriMaxTime();
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isSatCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 6) {
                            $pickupTime = clone $proposal1->getCriteria()->getSatTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getSatMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getSatMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isSunCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 0) {
                            $pickupTime = clone $proposal1->getCriteria()->getSunTime();
                            $minPickupTime = clone $proposal1->getCriteria()->getSunMinTime();
                            $maxPickupTime = clone $proposal1->getCriteria()->getSunMaxTime();
                            $pickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) ||
                                ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime())
                            )) {
                                // not in range
                                $pickupTime = null;
                                $minPickupTime = null;
                                $maxPickupTime = null;
                            }
                        }
                        break;
                    }
                    case Criteria::FREQUENCY_REGULAR: {
                        if ($proposal1->getCriteria()->isMonCheck() && $proposal2->getCriteria()->isMonCheck()) {
                            $monPickupTime = clone $proposal1->getCriteria()->getMonTime();
                            $monMinPickupTime = clone $proposal1->getCriteria()->getMonMinTime();
                            $monMaxPickupTime = clone $proposal1->getCriteria()->getMonMaxTime();
                            $monPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $monMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $monMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($monMinPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $monMinPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) ||
                                ($monMaxPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $monMaxPickupTime<=$proposal2->getCriteria()->getMonMaxTime())
                            )) {
                                // not in range
                                $monPickupTime = null;
                                $monMinPickupTime = null;
                                $monMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isTueCheck() && $proposal2->getCriteria()->isTueCheck()) {
                            $tuePickupTime = clone $proposal1->getCriteria()->getTueTime();
                            $tueMinPickupTime = clone $proposal1->getCriteria()->getTueMinTime();
                            $tueMaxPickupTime = clone $proposal1->getCriteria()->getTueMaxTime();
                            $tuePickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $tueMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $tueMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($tueMinPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $tueMinPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) ||
                                ($tueMaxPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $tueMaxPickupTime<=$proposal2->getCriteria()->getTueMaxTime())
                            )) {
                                // not in range
                                $tuePickupTime = null;
                                $tueMinPickupTime = null;
                                $tueMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isWedCheck() && $proposal2->getCriteria()->isWedCheck()) {
                            $wedPickupTime = clone $proposal1->getCriteria()->getWedTime();
                            $wedMinPickupTime = clone $proposal1->getCriteria()->getWedMinTime();
                            $wedMaxPickupTime = clone $proposal1->getCriteria()->getWedMaxTime();
                            $wedPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $wedMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $wedMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($wedMinPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $wedMinPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) ||
                                ($wedMaxPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $wedMaxPickupTime<=$proposal2->getCriteria()->getWedMaxTime())
                            )) {
                                // not in range
                                $wedPickupTime = null;
                                $wedMinPickupTime = null;
                                $wedMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isThuCheck() && $proposal2->getCriteria()->isThuCheck()) {
                            $thuPickupTime = clone $proposal1->getCriteria()->getThuTime();
                            $thuMinPickupTime = clone $proposal1->getCriteria()->getThuMinTime();
                            $thuMaxPickupTime = clone $proposal1->getCriteria()->getThuMaxTime();
                            $thuPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $thuMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $thuMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($thuMinPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $thuMinPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) ||
                                ($thuMaxPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $thuMaxPickupTime<=$proposal2->getCriteria()->getThuMaxTime())
                            )) {
                                // not in range
                                $thuPickupTime = null;
                                $thuMinPickupTime = null;
                                $thuMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isFriCheck() && $proposal2->getCriteria()->isFriCheck()) {
                            $friPickupTime = clone $proposal1->getCriteria()->getFriTime();
                            $friMinPickupTime = clone $proposal1->getCriteria()->getFriMinTime();
                            $friMaxPickupTime = clone $proposal1->getCriteria()->getFriMaxTime();
                            $friPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $friMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $friMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($friMinPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $friMinPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) ||
                                ($friMaxPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $friMaxPickupTime<=$proposal2->getCriteria()->getFriMaxTime())
                            )) {
                                // not in range
                                $friPickupTime = null;
                                $friMinPickupTime = null;
                                $friMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isSatCheck() && $proposal2->getCriteria()->isSatCheck()) {
                            $satPickupTime = clone $proposal1->getCriteria()->getSatTime();
                            $satMinPickupTime = clone $proposal1->getCriteria()->getSatMinTime();
                            $satMaxPickupTime = clone $proposal1->getCriteria()->getSatMaxTime();
                            $satPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $satMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $satMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($satMinPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $satMinPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) ||
                                ($satMaxPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $satMaxPickupTime<=$proposal2->getCriteria()->getSatMaxTime())
                            )) {
                                // not in range
                                $satPickupTime = null;
                                $satMinPickupTime = null;
                                $satMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->isSunCheck() && $proposal2->getCriteria()->isSunCheck()) {
                            $sunPickupTime = clone $proposal1->getCriteria()->getSunTime();
                            $sunMinPickupTime = clone $proposal1->getCriteria()->getSunMinTime();
                            $sunMaxPickupTime = clone $proposal1->getCriteria()->getSunMaxTime();
                            $sunPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $sunMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $sunMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($sunMinPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $sunMinPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) ||
                                ($sunMaxPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $sunMaxPickupTime<=$proposal2->getCriteria()->getSunMaxTime())
                            )) {
                                // not in range
                                $sunPickupTime = null;
                                $sunMinPickupTime = null;
                                $sunMaxPickupTime = null;
                            }
                        }
                        break;
                    }
                }
                break;
            }
        }
        $return = [];
        if ($pickupTime) {
            $return['pickupTime'] = $pickupTime;
        }
        if ($minPickupTime) {
            $return['minPickupTime'] = $minPickupTime;
        }
        if ($maxPickupTime) {
            $return['maxPickupTime'] = $maxPickupTime;
        }
        if ($monPickupTime) {
            $return['monPickupTime'] = $monPickupTime;
        }
        if ($monMinPickupTime) {
            $return['monMinPickupTime'] = $monMinPickupTime;
        }
        if ($monMaxPickupTime) {
            $return['monMaxPickupTime'] = $monMaxPickupTime;
        }
        if ($tuePickupTime) {
            $return['tuePickupTime'] = $tuePickupTime;
        }
        if ($tueMinPickupTime) {
            $return['tueMinPickupTime'] = $tueMinPickupTime;
        }
        if ($tueMaxPickupTime) {
            $return['tueMaxPickupTime'] = $tueMaxPickupTime;
        }
        if ($wedPickupTime) {
            $return['wedPickupTime'] = $wedPickupTime;
        }
        if ($wedMinPickupTime) {
            $return['wedMinPickupTime'] = $wedMinPickupTime;
        }
        if ($wedMaxPickupTime) {
            $return['wedMaxPickupTime'] = $wedMaxPickupTime;
        }
        if ($thuPickupTime) {
            $return['thuPickupTime'] = $thuPickupTime;
        }
        if ($thuMinPickupTime) {
            $return['thuMinPickupTime'] = $thuMinPickupTime;
        }
        if ($thuMaxPickupTime) {
            $return['thuMaxPickupTime'] = $thuMaxPickupTime;
        }
        if ($friPickupTime) {
            $return['friPickupTime'] = $friPickupTime;
        }
        if ($friMinPickupTime) {
            $return['friMinPickupTime'] = $friMinPickupTime;
        }
        if ($friMaxPickupTime) {
            $return['friMaxPickupTime'] = $friMaxPickupTime;
        }
        if ($satPickupTime) {
            $return['satPickupTime'] = $satPickupTime;
        }
        if ($satMinPickupTime) {
            $return['satMinPickupTime'] = $satMinPickupTime;
        }
        if ($satMaxPickupTime) {
            $return['satMaxPickupTime'] = $satMaxPickupTime;
        }
        if ($sunPickupTime) {
            $return['sunPickupTime'] = $sunPickupTime;
        }
        if ($sunMinPickupTime) {
            $return['sunMinPickupTime'] = $sunMinPickupTime;
        }
        if ($sunMaxPickupTime) {
            $return['sunMaxPickupTime'] = $sunMaxPickupTime;
        }
        return $return;
    }
}
