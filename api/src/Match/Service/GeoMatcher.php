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

/**
 * Geographical Matching service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoMatcher
{
    private $geoRouter;

    /**
     * Constructor.
     *
     * @param GeoRouter $geoRouter
     */
    public function __construct(GeoRouter $geoRouter)
    {
        $this->geoRouter = $geoRouter;
    }

    /**
     * Search for matchings between a candidate and an array of candidates
     *
     * @param Candidate $candidate      The candidate for which we want to find matchings
     * @param Candidate[] $candidates   The array of candidates to match
     * @param bool $master              The candidate is the master side (meaning that the have to take account of its maximum detour)
     * @return array|null               The array of results
     */
    public function singleMatch(Candidate $candidate, array $candidates, bool $master=true): ?array
    {
        $matches = [];
        
        foreach ($candidates as $candidateToMatch) {
            if ($master && $match = $this->match($candidate, $candidateToMatch)) {
                // if we stream, we should return the result here !
                $matches[] = $match;
            } elseif (!$master && $match = $this->match($candidateToMatch, $candidate)) {
                // if we stream, we should return the result here !
                $matches[] = $match;
            }
        }

        return $matches;
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
        $result = null;
        $addresses = [];

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
            $addresses[] = [
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
                    $addresses[] = $address;
                }
            }
        }

        // for each possible route, we check if it's acceptable
        foreach ($addresses as $points) {
            if ($routes = $this->geoRouter->getRoutes(array_values($points), true)) {
                $detourDistance = false;
                $detourDuration = false;
                $commonDistance = false;
    
                // we check the detour distance
                if ($candidate1->getMaxDetourDistance()) {
                    // in meters
                    if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()+$candidate1->getMaxDetourDistance())) {
                        $detourDistance = true;
                    }
                } elseif ($candidate1->getMaxDetourDistancePercent()) {
                    // in percentage
                    if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()*$candidate1->getMaxDetourDistancePercent()+$candidate1->getDirection()->getDistance())) {
                        $detourDistance = true;
                    }
                }
                // we check the detour duration
                if ($candidate1->getMaxDetourDuration()) {
                    // in seconds
                    if ($routes[0]->getDuration()<=($candidate1->getDirection()->getDuration()+$candidate1->getMaxDetourDuration())) {
                        $detourDuration = true;
                    }
                } elseif ($candidate1->getMaxDetourDurationPercent()) {
                    // in percentage
                    if ($routes[0]->getDuration()<=($candidate1->getDirection()->getDuration()*$candidate1->getMaxDetourDurationPercent()+$candidate1->getDirection()->getDuration())) {
                        $detourDuration = true;
                    }
                }
                // we check the common distance
                if (($candidate1->getDirection()->getDistance()<ProposalMatcher::MIN_COMMON_DISTANCE_CHECK) ||
                    (($candidate2->getDirection()->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::MIN_COMMON_DISTANCE_PERCENT)) {
                    $commonDistance = true;
                }
                
                // if the detour is acceptable we keep the candidate
                if ($detourDistance && $detourDuration && $commonDistance) {
                    $result[] = [
                        'order' => $this->generateOrder(array_keys($points), $routes[0]->getDurations()),
                        'originalDistance' => $candidate1->getDirection()->getDistance()/1000,
                        'acceptedDetourDistance' => $candidate1->getMaxDetourDistance()/1000,
                        'newDistance' => $routes[0]->getDistance()/1000,
                        'detourDistance' => ($routes[0]->getDistance()-$candidate1->getDirection()->getDistance())/1000,
                        'detourDistancePercent' => round($routes[0]->getDistance()*100/$candidate1->getDirection()->getDistance()-100, 2),
                        'originalDuration' => $candidate1->getDirection()->getDuration()/60,
                        'acceptedDetourDuration' => $candidate1->getMaxDetourDuration()/60,
                        'newDuration' => $routes[0]->getDuration()/60,
                        'detourDuration' => ($routes[0]->getDuration()-$candidate1->getDirection()->getDuration())/60,
                        'detourDurationPercent' => round($routes[0]->getDuration()*100/$candidate1->getDirection()->getDuration()-100, 2)
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Returns the order of the points.
     *
     * @param array $keys   The keys representing the order
     * @param array $keys   The duration of each part
     * @return void
     */
    private function generateOrder(array $keys, array $durations)
    {
        $order = [];
        foreach ($keys as $i=>$key) {
            $order[] = [
                'candidate'         => (substr($key, 0, 1) == 'A') ? 1 : 2,
                'position'          => substr($key, 1),
                'duration'          => isset($durations[$i]) ? $durations[$i]['duration'] : null,
                'approx_duration'   => isset($durations[$i]) ? $durations[$i]['approx_duration'] : null,
                'approx_point'      => isset($durations[$i]) ? $durations[$i]['approx_point'] : null
            ];
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
