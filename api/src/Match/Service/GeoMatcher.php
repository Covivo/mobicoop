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

namespace App\Match\Service;

use App\Match\Entity\Candidate;
use App\Geography\Service\GeoRouter;
use App\Carpool\Service\ProposalMatcher;
use App\Geography\Service\ZoneManager;
use Psr\Log\LoggerInterface;

/**
 * Geographical Matching service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoMatcher
{
    private $geoRouter;
    private $zoneManager;
    private $logger;


    /**
     * Constructor.
     *
     * @param GeoRouter $geoRouter
     * @param LoggerInterface $logger
     */
    public function __construct(GeoRouter $geoRouter, ZoneManager $zoneManager, LoggerInterface $logger)
    {
        $this->geoRouter = $geoRouter;
        $this->zoneManager = $zoneManager;
        $this->logger = $logger;
    }

    /**
     * Search for matchings between a candidate and an array of candidates
     *
     * @param Candidate $candidate      The candidate for which we want to find matchings
     * @param Candidate[] $candidates   The array of candidates to match
     * @param bool $master              The candidate is the master side (meaning that we have to take account of its maximum detour)
     * @param bool $async               Launch the requests in async
     * @return array|null               The array of results
     */
    public function singleMatch(Candidate $candidate, array $candidates, bool $master=true, bool $async=false): ?array
    {
        $matchesReturned = [];
        if (!$async) {
            // SYNC
            $this->logger->debug('Single Match | Sync');
            foreach ($candidates as $candidateToMatch) {
                if ($master && $matches = $this->match($candidate, $candidateToMatch)) {
                    // if we stream, we should return the result here !
                    foreach ($matches as $match) {
                        $matchesReturned[] = $match;
                    }
                } elseif (!$master && $matches = $this->match($candidateToMatch, $candidate)) {
                    // if we stream, we should return the result here !
                    foreach ($matches as $match) {
                        $matchesReturned[] = $match;
                    }
                }
            }
        } else {
            // ASYNC
            $this->logger->debug('Single Match | Async');
            // we create the points for the routes alternatives for each candidate
            $addressesForRoutes = [];
            $candidatesById = [];
            foreach ($candidates as $candidateToMatch) {
                if ($master && $pointsArray = $this->generatePointsArray($candidate, $candidateToMatch)) {
                    $candidatesById[$candidateToMatch->getId()] = $candidateToMatch;
                    $addressesForRoutes[$candidateToMatch->getId()] = $pointsArray;
                } elseif (!$master && $pointsArray = $this->generatePointsArray($candidateToMatch, $candidate)) {
                    $candidatesById[$candidateToMatch->getId()] = $candidateToMatch;
                    $addressesForRoutes[$candidateToMatch->getId()] = $pointsArray;
                }
            }
            // we get the routes for all candidates
            $ownerRoutes = $this->geoRouter->getAsyncRoutes($addressesForRoutes, true);
            // we treat the routes to check if they match
            foreach ($ownerRoutes as $ownerId=>$routes) {
                if ($matches = $this->checkMatch($candidate, $candidatesById[$ownerId], $routes, $addressesForRoutes[$ownerId])) {
                    foreach ($matches as $match) {
                        $matchesReturned[] = $match;
                    }
                }
            }
        }
        $this->logger->debug('Single Match | End');
        return $matchesReturned;
    }

    /**
     * Search for matchings between candidates
     *
     * @param array $candidates  The array of candidates to match in the form :
     * [
     *      0 => [
     *          'driver'      => [$driver],
     *          'passengers'  => [$passenger1,$passenger2...]
     *      ],
     *      1 => [
     *          'driver'      => [$driver],
     *          'passengers'  => [$passenger1,$passenger2...]
     *      ],
     *      ...
     * ]
     * @return array|null               The array of results
     */
    public function multiMatch(array $candidates): ?array
    {
        $this->logger->debug('Multi Match');
            
        $matchesReturned = [];

        // we create the points for the routes alternatives for each candidate
        $addressesForRoutes = [];
        $routesOwner = [];
        
        $this->logger->debug('Multi Match | Generate points start');
        $i=0;
        foreach ($candidates as $keyActors=>$actors) {
            foreach ($actors['passengers'] as $keyPassenger=>$candidateToMatch) {
                if ($pointsArray = $this->generatePointsArray($actors['driver'], $candidateToMatch)) {
                    $addressesForRoutes[$i] = $pointsArray;
                    $routesOwner[$i] = [
                        'actors' => $keyActors,
                        'passenger' => $keyPassenger
                    ];
                    $i++;
                }
            }
        }
        $this->logger->debug('Multi Match | Generate points end');

        $this->logger->debug('Multi Match | Get routes start');
        $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes);
        $this->logger->debug('Multi Match | Get routes end');

        // we treat the routes to check if they match
        $this->logger->debug('Multi Match | Check matches start');
        foreach ($ownerRoutes as $ownerId=>$routes) {
            $this->logger->debug('Multi Match | Check matches for id #'.$ownerId);
            if ($matches = $this->checkMultiMatch(
                $candidates[$routesOwner[$ownerId]['actors']]['driver'],
                $candidates[$routesOwner[$ownerId]['actors']]['passengers'][$routesOwner[$ownerId]['passenger']],
                $routes,
                $addressesForRoutes[$ownerId]
            )) {
                $matchesReturned[] = [
                        'driver' => $candidates[$routesOwner[$ownerId]['actors']]['driver'],
                        'passenger' => $candidates[$routesOwner[$ownerId]['actors']]['passengers'][$routesOwner[$ownerId]['passenger']],
                        'matches' => $matches
                    ];
            }
        }
        $this->logger->debug('Multi Match | Check matches end');
        
        return $matchesReturned;
    }


    /**
     * Matching function between 2 candidates.
     * The first candidate handles the matching parameters :
     * - detour distance (in meters) or detour distance percent (of the original distance), the second is only used if the first is null
     * - detour duration (in milliseconds) or detour duration percent (of the original duration), the second is only used if the first is null
     *
     * @param Candidate $candidate1     The first candidate
     * @param Candidate $candidate2     The second candidate
     * @return array|null               The array containing the result or null if candidates don't match
     */
    private function match(Candidate $candidate1, Candidate $candidate2): ?array
    {
        $result = [];
        
        $pointsArray = $this->generatePointsArray($candidate1, $candidate2);

        // for each possible route, we check if it's acceptable
        foreach ($pointsArray as $points) {
            if ($routes = $this->geoRouter->getRoutes(array_values($points), true)) {
                if ($match = $this->checkMatch($candidate1, $candidate2, $routes, $points)) {
                    $result[] = $match;
                }
            }
        }
        return $result;
        $this->logger->debug('Match | Check - between 2 candidates ');
    }

    private function checkMatch(Candidate $candidate1, Candidate $candidate2, array $routes, ?array $points): ?array
    {
        $result = null;

        $detourDistance = false;
        $detourDuration = false;
        $commonDistance = false;

        // we check the detour distance
        if ($candidate1->getMaxDetourDistance()) {
            // in meters
            if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()+$candidate1->getMaxDetourDistance())) {
                $detourDistance = true;
                $this->logger->debug('Detour Distance | Check - in meters ');
            }
        } elseif ($candidate1->getMaxDetourDistancePercent()) {
            // in percentage
            if ($routes[0]->getDistance()<=(($candidate1->getDirection()->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getDirection()->getDistance())) {
                $detourDistance = true;
                $this->logger->debug('Detour Distance | Check - in percentage ');
            }
        }
        // we check the detour duration
        if ($candidate1->getMaxDetourDuration()) {
            // in seconds
            if ($routes[0]->getDuration()<=($candidate1->getDirection()->getDuration()+$candidate1->getMaxDetourDuration())) {
                $detourDuration = true;
                $this->logger->debug('Detour Duration | Check in seconds ');
            }
        } elseif ($candidate1->getMaxDetourDurationPercent()) {
            // in percentage
            if ($routes[0]->getDuration()<=(($candidate1->getDirection()->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getDirection()->getDuration())) {
                $detourDuration = true;
                $this->logger->debug('Detour Duration | Check in percentage ');
            }
        }
        // we check the common distance
        if (($candidate1->getDirection()->getDistance()<ProposalMatcher::MIN_COMMON_DISTANCE_CHECK) ||
            (($candidate2->getDirection()->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::MIN_COMMON_DISTANCE_PERCENT)) {
            $commonDistance = true;
            $this->logger->debug('Common Distance | Check ');
        }
        
        // if the detour is acceptable we keep the candidate
        if ($detourDistance && $detourDuration && $commonDistance) {
            // we add the zones to the direction
            $direction = $this->zoneManager->createZonesForDirection($routes[0]);
            $result = [
                'order' => is_array($points) ? $this->generateOrder($points, $routes[0]->getDurations()) : null,
                'originalDistance' => $candidate1->getDirection()->getDistance(),
                'acceptedDetourDistance' => $candidate1->getMaxDetourDistance(),
                'newDistance' => $routes[0]->getDistance(),
                'detourDistance' => ($routes[0]->getDistance()-$candidate1->getDirection()->getDistance()),
                'detourDistancePercent' => round($routes[0]->getDistance()*100/$candidate1->getDirection()->getDistance()-100, 2),
                'originalDuration' => $candidate1->getDirection()->getDuration(),
                'acceptedDetourDuration' => $candidate1->getMaxDetourDuration(),
                'newDuration' => $routes[0]->getDuration(),
                'detourDuration' => ($routes[0]->getDuration()-$candidate1->getDirection()->getDuration()),
                'detourDurationPercent' => round($routes[0]->getDuration()*100/$candidate1->getDirection()->getDuration()-100, 2),
                'commonDistance' => $candidate2->getDirection()->getDistance(),
                'direction' => $direction,
                'id' => $candidate2->getId()
            ];
            $this->logger->debug('Detour | detour is acceptable ');
        }
        $this->logger->debug('Detour | No match ');
        return $result;
    }

    private function checkMultiMatch(Candidate $candidate1, Candidate $candidate2, array $routes, ?array $points): ?array
    {
        $result = null;

        $detourDistance = false;
        $detourDuration = false;
        $commonDistance = false;

        // we check the detour distance
        if ($candidate1->getMaxDetourDistance()) {
            // in meters
            if ($routes[0]['distance']<=($candidate1->getMassPerson()->getDistance()+$candidate1->getMaxDetourDistance())) {
                $detourDistance = true;
                $this->logger->debug('Detour Distance | Check - in meters ');
            }
        } elseif ($candidate1->getMaxDetourDistancePercent()) {
            // in percentage
            if ($routes[0]['distance']<=(($candidate1->getMassPerson()->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getMassPerson()->getDistance())) {
                $detourDistance = true;
                $this->logger->debug('Detour Distance | Check - in percentage ');
            }
        }
        // we check the detour duration
        if ($candidate1->getMaxDetourDuration()) {
            // in seconds
            if ($routes[0]['duration']<=($candidate1->getMassPerson()->getDuration()+$candidate1->getMaxDetourDuration())) {
                $detourDuration = true;
                $this->logger->debug('Detour Duration | Check in seconds ');
            }
        } elseif ($candidate1->getMaxDetourDurationPercent()) {
            // in percentage
            if ($routes[0]['duration']<=(($candidate1->getMassPerson()->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getMassPerson()->getDuration())) {
                $detourDuration = true;
                $this->logger->debug('Detour Duration | Check in percentage ');
            }
        }
        // we check the common distance
        if (($candidate1->getMassPerson()->getDistance()<ProposalMatcher::MIN_COMMON_DISTANCE_CHECK) ||
            (($candidate2->getMassPerson()->getDistance()*100/$candidate1->getMassPerson()->getDistance()) > ProposalMatcher::MIN_COMMON_DISTANCE_PERCENT)) {
            $commonDistance = true;
            $this->logger->debug('Common Distance | Check ');
        }
        
        // if the detour is acceptable we keep the candidate
        if ($detourDistance && $detourDuration && $commonDistance) {
            $result[] = [
                'order' => is_array($points) ? $this->generateOrder($points, null) : null,
                'originalDistance' => $candidate1->getMassPerson()->getDistance(),
                'acceptedDetourDistance' => $candidate1->getMaxDetourDistance(),
                'newDistance' => $routes[0]['distance'],
                'detourDistance' => ($routes[0]['distance']-$candidate1->getMassPerson()->getDistance()),
                'detourDistancePercent' => round($routes[0]['distance']*100/$candidate1->getMassPerson()->getDistance()-100, 2),
                'originalDuration' => $candidate1->getMassPerson()->getDuration(),
                'acceptedDetourDuration' => $candidate1->getMaxDetourDuration(),
                'newDuration' => $routes[0]['duration'],
                'detourDuration' => ($routes[0]['duration']-$candidate1->getMassPerson()->getDuration()),
                'detourDurationPercent' => round($routes[0]['duration']*100/$candidate1->getMassPerson()->getDuration()-100, 2),
                'commonDistance' => $candidate2->getMassPerson()->getDistance(),
                'id' => $candidate2->getId()
            ];
            $this->logger->debug('Detour | detour is acceptable ');
        }
        $this->logger->debug('Detour | No match ');
        return $result;
    }

    private function generatePointsArray(Candidate $candidate1, Candidate $candidate2): ?array
    {
        $pointsArray = [];

        // The first candidate has 2 or more waypoints, the first and last will always be the first and last
        // The second candidate has 2 or more waypoints
        // It's a Travel salesman problem (TSP) : https://en.wikipedia.org/wiki/Travelling_salesman_problem

        // we will write Axx for first candidate points, Bxx for second candidate points

        // for now, we use the routing engine and :
        // - we just try the classic ACDB path if we have only 2 points for the first candidate
        // - or we try the combinations of :
        //   - inner points of the first candidate (=> we exclude the start and end point as they must stay start and end)
        //   - the start and end point of the second candidate
        //   - note : we keep only the combinations that respect the order of the points for the 2 candidates (B0 will always be before B1, A1 will always be before A2 etc...)
        //   - TODO : maybe we should also try to remove one or more inner points of the first candidate and see if the whole route is faster
        //     (it could be the case for example if an AXX inner point and a B0/B1 point are close)

        // TODO : solve the TSP using the optimization engine

        if (count($candidate1->getAddresses()) == 2) {
            $pointsArray[] = [
                'A0' => $candidate1->getAddresses()[0],                                       // A
                'B0' => $candidate2->getAddresses()[0],                                       // C
                'B1' => $candidate2->getAddresses()[count($candidate2->getAddresses())-1],    // D
                'A1' => $candidate1->getAddresses()[count($candidate1->getAddresses())-1],    // B
            ];
        } else {
            // innerAddresses will contain the first candidate addresses without the start and end point
            $innerAddresses = [];
            $addresses1 = $candidate1->getAddresses();
            array_pop($addresses1);     // get rid of the last point
            array_shift($addresses1);   // get rid of the first point
            for ($i=1;$i<=count($addresses1);$i++) {
                $innerAddresses['A'.$i] = $addresses1[$i-1];
            }
            // we add the second candidate start and end point
            $innerAddresses['B0'] = $candidate2->getAddresses()[0];
            $innerAddresses['B1'] = $candidate2->getAddresses()[count($candidate2->getAddresses())-1];
            $generator = new CombinationsGenerator();
            foreach ($generator->generate($innerAddresses) as $combination) {
                $address = [];
                $address['A0'] = $candidate1->getAddresses()[0];    // first candidate start
                // we use the following array to check the order of points (eg. if B1 is before B0 we skip)
                $check=[];
                $take = false;
                foreach ($combination as $key=>$value) {
                    $take = false;
                    if ($key[0] == 'A' && in_array('A'.(substr($key, 1)+1), $check)) {
                        // 'A' order not respected !
                        break;
                    }
                    if ($key[0] == 'B' && in_array('B'.(substr($key, 1)+1), $check)) {
                        // 'B' order not respected !
                        break;
                    }
                    $take = true;
                    $check[] = $key;
                    $address[$key] = $value;
                }
                if ($take) {
                    $address['A'.(count($candidate1->getAddresses())-1)] = $candidate1->getAddresses()[count($candidate1->getAddresses())-1]; // first candidate end
                    $pointsArray[] = $address;
                }
            }
        }
        return $pointsArray;
    }

    /**
     * Returns the order of the points.
     *
     * @param array $points     The points in order
     * @param array $durations  The duration of each part
     * @return void
     */
    private function generateOrder(array $points, ?array $durations)
    {
        $order = [];
        $i = 0;
        foreach ($points as $key=>$point) {
            $order[] = [
                'candidate'         => (substr($key, 0, 1) == 'A') ? 1 : 2,
                'position'          => substr($key, 1),
                'duration'          => isset($durations[$i]) ? $durations[$i]['duration'] : null,
                'approx_duration'   => isset($durations[$i]) ? $durations[$i]['approx_duration'] : null,    // approx_duration : if the duration to the waypoint isn't strictly returned by the SIG
                'approx_point'      => isset($durations[$i]) ? $durations[$i]['approx_point'] : null,       // approx_point : if the position of the waypoint isn't strictly returned by the SIG
                'address'           => $point
            ];
            $i++;
        }
        return $order;
    }

}

// Class used temporarily to generate path combinations
// Will be dumped when optimization engine will work
class CombinationsGenerator
{
    /**
     * Generate combinations for an array.
     *
     * @param array $list
     * @return \Generator
     */
    public function generate(array $list): \Generator
    {
        if (count($list) > 2) {
            for ($i = 0; $i < count($list); $i++) {
                $listCopy = $list;

                $entry = array_splice($listCopy, $i, 1);
                foreach ($this->generate($listCopy) as $combination) {
                    yield array_merge($entry, $combination);
                }
            }
        } elseif (count($list) > 0) {
            yield $list;

            if (count($list) > 1) {
                yield array_reverse($list);
            }
        }
    }
}
