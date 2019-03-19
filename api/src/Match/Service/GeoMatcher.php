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

/**
 * Geographical Matching service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoMatcher
{
    private $geoRouter;

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
            if ($master && $match = $this->match($candidate,$candidateToMatch)) {
                // if we stream, we should return the result here !
                $matches[] = $match;
            } elseif (!$master && $match = $this->match($candidate,$candidateToMatch)) {
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

        // for now, with the routing engine : 
        // - we just try the classic ACDB path if we have only 2 points for the first candidate
        // - or we try the combinations of : 
        //   - inner points of the first candidate 
        //   - the start and end point of the second candidate

        // we will write Axx for first candidate points, Bxx for second candidate points

        // TODO : solve the TSP using the optimization engine
        // TODO : we should also try to remove one or more inner points of the first candidate and see if the whole route is faster 
        // (it could be the case for example if an inner point and a B0/B1 point are close)

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
            array_pop($addresses1);
            array_shift($addresses1);
            for ($i=1;$i<=count($addresses1);$i++) {
                $innerAddresses['A'.$i] = $addresses1[$i-1];
            }
            // we add the second candidate start and end point
            $innerAddresses['B0'] = $candidate2->getAddresses()[0];
            $innerAddresses['B1'] = $candidate2->getAddresses()[count($candidate2->getAddresses())-1];
            $generator = new CombinationsGenerator();
            foreach ($generator->generate($innerAddresses) as $combination) {
                $address = [];
                $address['A0'] = $candidate1->getAddresses()[0];
                // we use the following array to check the order of points (eg. if B1 is before B0 we skip)
                $check=[];
                $take = false;
                foreach ($combination as $key=>$value) {
                    $take = false;
                    if ($key[0] == 'A' && in_array('A'.(substr($key,1)+1),$check)) break;
                    if ($key[0] == 'B' && in_array('B'.(substr($key,1)+1),$check)) break;
                    $take = true;
                    $check[] = $key;
                    $address[$key] = $value;
                }
                $address['A'.(count($candidate1->getAddresses())-1)] = $candidate1->getAddresses()[count($candidate1->getAddresses())-1];
                if ($take) $addresses[] = $address;
            }
        }
        
        foreach ($addresses as $points) {
            if ($routes = $this->geoRouter->getRoutes(array_values($points))) {
                // echo $routes[0]->getDistance() . "<br />";
                // echo $candidate1->getDirection()->getDistance() . "<br />";
                // echo $candidate1->getMaxDetourDistance() . "<br />";
                // echo $routes[0]->getDuration() . "<br />";
                // echo $candidate1->getDirection()->getDuration() . "<br />";
                // echo $candidate1->getMaxDetourDuration() . "<br />";
                $detourDistance = false;
                $detourDuration = false;
    
                if ($candidate1->getMaxDetourDistance()) {
                    if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()+$candidate1->getMaxDetourDistance())) $detourDistance = true;
                } elseif ($candidate1->getMaxDetourDistancePercent()) {
                    if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()*$candidate1->getMaxDetourDistancePercent()+$candidate1->getDirection()->getDistance())) $detourDistance = true;
                } 
                if ($candidate1->getMaxDetourDuration()) {
                    if ($routes[0]->getDuration()<=($candidate1->getDirection()->getDuration()+$candidate1->getMaxDetourDuration())) $detourDuration = true;
                } elseif ($candidate1->getMaxDetourDurationPercent()) {
                    if ($routes[0]->getDuration()<=($candidate1->getDirection()->getDuration()*$candidate1->getMaxDetourDurationPercent()+$candidate1->getDirection()->getDuration())) $detourDuration = true;
                }
    
                if ($detourDistance && $detourDuration) {
                    $result[] = [
                        'order' => array_keys($points),
                        'distance' => $routes[0]->getDistance(),
                        'detourDistance' => $routes[0]->getDistance()-$candidate1->getDirection()->getDistance(),
                        'detourDistancePercent' => round($routes[0]->getDistance()*100/$candidate1->getDirection()->getDistance()-100,2),
                        'duration' => $routes[0]->getDuration(),
                        'detourDuration' => $routes[0]->getDuration()-$candidate1->getDirection()->getDuration(),
                        'detourDurationPercent' => round($routes[0]->getDuration()*100/$candidate1->getDirection()->getDuration()-100,2)
                    ];
                } 
            }
        }
        return $result;
    }
}

// Class used temporarily to generate path combinations
// Will be dumped when optimization engine will work
class CombinationsGenerator
{
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
