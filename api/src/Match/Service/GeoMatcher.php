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
use App\Geography\Interfaces\GeorouterInterface;
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
     * Search for matchings between a candidate and multiple candidates
     *
     * @param array $candidates     An associative array representing the matches to test :
     *                          [
     *                              'candidate'     => Candidate        // the proposal candidate
     *                              'candidates'    => Candidates[0]    // the array of candidates to match with the proposal candidate
     *                              'master'        => bool             // if the candidate proposal is the master => we have to use its max detour (= driver !)
     *                          ]
     * @return array                The array of results
     */
    public function singleMatch(array $candidates): ?array
    {
        $matchesReturned = [];
        // we create the points for the routes alternatives for each candidate
        $addressesForRoutes = [];
        $variants = [];
        $i = 0;

        foreach ($candidates as $pears) {
            foreach ($pears['candidates'] as $candidate) {
                $role = 'driver';
                if ($pears['master']) {
                    $pointsArray = $this->generatePointsArray($pears['candidate'], $candidate);
                } else {
                    $pointsArray = $this->generatePointsArray($candidate, $pears['candidate']);
                    $role = 'passenger';
                }
                foreach ($pointsArray as $variant) {
                    $variants[$i] = [
                        'candidate' => $pears['candidate'],
                        'candidateToMatch' => $candidate,
                        'variantPoints' => $variant,
                        'role' => $role
                    ];
                    $addressesForRoutes[$i][] = $variant;
                    $i++;
                }
            }
        }
        
        // we get the routes for all candidates
        $this->logger->info("GeoMatcher : start multipleAsync " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, true, false, GeorouterInterface::RETURN_TYPE_ARRAY);
        $this->logger->info("GeoMatcher : end multipleAsync " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // we treat the routes to check if they match
        foreach ($ownerRoutes as $ownerId=>$routes) {
            if ($variants[$ownerId]['role'] == 'driver') {
                if ($match = $this->checkMatch(
                    $variants[$ownerId]['candidate'],
                    $variants[$ownerId]['candidateToMatch'],
                    $routes,
                    $variants[$ownerId]['variantPoints']
                )) {
                    $matchesReturned['driver'][$variants[$ownerId]['candidateToMatch']->getId()][] = $match;
                }
            } elseif ($match = $this->checkMatch(
                $variants[$ownerId]['candidateToMatch'],
                $variants[$ownerId]['candidate'],
                $routes,
                $variants[$ownerId]['variantPoints']
            )) {
                $matchesReturned['passenger'][$variants[$ownerId]['candidateToMatch']->getId()][] = $match;
            }
        }
        $this->logger->info("GeoMatcher : return matches " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        return $matchesReturned;
    }

    /**
     * Search for matchings between candidates
     *
     * @param array $candidates  The array of candidates to match in the form :
     * [
     *      0 => [
     *          'driver'      => $driver,
     *          'passengers'  => [$passenger1,$passenger2...]
     *      ],
     *      1 => [
     *          'driver'      => $driver,
     *          'passengers'  => [$passenger1,$passenger2...]
     *      ],
     *      ...
     * ]
     * @param bool $forMass     The multimatch is for mass matching
     * @return array|null               The array of results
     */
    public function multiMatch(array $candidates, $forMass = false): ?array
    {
        $matchesReturned = [];

        // we create the points for the routes alternatives for each candidate
        $addressesForRoutes = [];
        $variants = [];
        $routesOwner = [];
        $i=0;

        if (!$forMass) {
            foreach ($candidates as $keyActors=>$actors) {
                foreach ($actors['passengers'] as $keyPassenger=>$candidateToMatch) {
                    if ($pointsArray = $this->generatePointsArray($actors['driver'], $candidateToMatch)) {
                        foreach ($pointsArray as $variant) {
                            $variants[$i] = [
                                'candidate' => $actors['driver'],
                                'candidateToMatch' => $candidateToMatch,
                                'variantPoints' => $variant
                            ];
                            $addressesForRoutes[$i][] = $variant;
                            $i++;
                        }
                    }
                }
            }

            $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, true, false, GeorouterInterface::RETURN_TYPE_ARRAY);
    
            // we treat the routes to check if they match
            foreach ($ownerRoutes as $ownerId=>$routes) {
                if ($match = $this->checkMatch(
                    $variants[$ownerId]['candidate'],
                    $variants[$ownerId]['candidateToMatch'],
                    $routes,
                    $variants[$ownerId]['variantPoints']
                )) {
                    // the following means : $matchesReturned[driverId][passengerId][] = $match;
                    $matchesReturned[$variants[$ownerId]['candidate']->getId()][$variants[$ownerId]['candidateToMatch']->getId()][] = $match;
                }

                //$this->logger->debug('Multi Match | Check matches for id #'.$ownerId);
                // if ($match = $this->checkMatch(
                //     $candidates[$routesOwner[$ownerId]['actors']]['driver'],
                //     $candidates[$routesOwner[$ownerId]['actors']]['passengers'][$routesOwner[$ownerId]['passenger']],
                //     $routes,
                //     $addressesForRoutes[$ownerId]
                // )) {
                //     $matchesReturned[] = [
                //             'driver' => $candidates[$routesOwner[$ownerId]['actors']]['driver'],
                //             'passenger' => $candidates[$routesOwner[$ownerId]['actors']]['passengers'][$routesOwner[$ownerId]['passenger']],
                //             'match' => $match
                //         ];
                // }
            }
        } else {
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


            $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_ARRAY);
    
            // we treat the routes to check if they match
            foreach ($ownerRoutes as $ownerId=>$routes) {
                if ($matches = $this->checkMassMultiMatch(
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
            unset($ownerRoutes);
        }
        
        return $matchesReturned;
    }

    /**
     * Force the match between 2 candidates.
     * Used for example to compute the reverse route after a successful outward matching.
     */
    public function forceMatch(Candidate $candidate1, Candidate $candidate2): ?array
    {
        $result = [];
        
        $pointsArray = $this->generatePointsArray($candidate1, $candidate2);

        // for each possible route, we compute its results
        foreach ($pointsArray as $points) {
            if ($routes = $this->geoRouter->getRoutes(array_values($points), true, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                if ($match = $this->getMatch($candidate1, $candidate2, $routes, $points)) {
                    $result[] = $match;
                }
            }
        }
        return $result;
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
    }

    private function checkMatch(Candidate $candidate1, Candidate $candidate2, array $routes, ?array $points): ?array
    {
        $result = null;

        $detourDistance = false;
        $detourDuration = false;
        $commonDistance = false;

        $distance = null;
        $duration = null;

        if (is_array($routes[0])) {
            if (isset($routes[0]["distance"])) {
                $distance = $routes[0]["distance"];
            }
            if (isset($routes[0]["duration"])) {
                $duration = $routes[0]["duration"];
            }
        } else {
            $distance = $routes[0]->getDistance();
            $duration = $routes[0]->getDuration();
        }

        // we check the detour distance
        if ($candidate1->getDirection()) {
            if ($candidate1->getMaxDetourDistance()) {
                // in meters
                if ($distance<=($candidate1->getDirection()->getDistance()+$candidate1->getMaxDetourDistance())) {
                    $detourDistance = true;
                }
            } elseif ($candidate1->getMaxDetourDistancePercent()) {
                // in percentage
                if ($distance<=(($candidate1->getDirection()->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getDirection()->getDistance())) {
                    $detourDistance = true;
                }
            }
        } else {
            if ($candidate1->getMaxDetourDistance()) {
                // in meters
                if ($distance<=($candidate1->getDistance()+$candidate1->getMaxDetourDistance())) {
                    $detourDistance = true;
                }
            } elseif ($candidate1->getMaxDetourDistancePercent()) {
                // in percentage
                if ($distance<=(($candidate1->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getDistance())) {
                    $detourDistance = true;
                }
            }
        }
        
        // we check the detour duration
        if ($candidate1->getDirection()) {
            if ($candidate1->getMaxDetourDuration()) {
                // in seconds
                if ($duration<=($candidate1->getDirection()->getDuration()+$candidate1->getMaxDetourDuration())) {
                    $detourDuration = true;
                }
            } elseif ($candidate1->getMaxDetourDurationPercent()) {
                // in percentage
                if ($duration<=(($candidate1->getDirection()->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getDirection()->getDuration())) {
                    $detourDuration = true;
                }
            }
        } else {
            if ($candidate1->getMaxDetourDuration()) {
                // in seconds
                if ($duration<=($candidate1->getDuration()+$candidate1->getMaxDetourDuration())) {
                    $detourDuration = true;
                }
            } elseif ($candidate1->getMaxDetourDurationPercent()) {
                // in percentage
                if ($duration<=(($candidate1->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getDuration())) {
                    $detourDuration = true;
                }
            }
        }

        // we check the common distance (if the distance is not 0, that can happen for solidary proposals without destination)
        if ($candidate2->getDirection() && $candidate2->getDirection()->getDistance()>0) {
            if ($candidate1->getDirection()) {
                if (($candidate1->getDirection()->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                    (($candidate2->getDirection()->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                    $commonDistance = true;
                }
            } else {
                if (($candidate1->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                    (($candidate2->getDirection()->getDistance()*100/$candidate1->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                    $commonDistance = true;
                }
            }
        } elseif ($candidate2->getDistance()>0) {
            if ($candidate1->getDirection()) {
                if (($candidate1->getDirection()->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                    (($candidate2->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                    $commonDistance = true;
                }
            } else {
                if (($candidate1->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                    (($candidate2->getDistance()*100/$candidate1->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                    $commonDistance = true;
                }
            }
        } else {
            $commonDistance = true;
        }
        // if the detour is acceptable we keep the candidate
        if ($detourDistance && $detourDuration && $commonDistance) {
            // we deserialize the direction if needed
            $direction = $routes[0];
            if (is_array($routes[0])) {
                $direction = $this->geoRouter->getRouter()->deserializeDirection($routes[0]);
            }
            
            $result = [
                'route' => is_array($points) ? $this->generateRoute($points, $direction->getDurations()) : null,
                'originalDistance' => $candidate1->getDirection() ? $candidate1->getDirection()->getDistance() : $candidate1->getDistance(),
                'acceptedDetourDistance' => $candidate1->getMaxDetourDistance(),
                'newDistance' => $distance,
                'detourDistance' => $candidate1->getDirection() ? ($distance-$candidate1->getDirection()->getDistance()) : ($distance-$candidate1->getDistance()),
                'detourDistancePercent' => $candidate1->getDirection() ? round($distance*100/$candidate1->getDirection()->getDistance()-100, 2) : round($distance*100/$candidate1->getDistance()-100, 2),
                'originalDuration' => $candidate1->getDirection() ? $candidate1->getDirection()->getDuration() : $candidate1->getDuration(),
                'acceptedDetourDuration' => $candidate1->getMaxDetourDuration(),
                'newDuration' => $duration,
                'detourDuration' => $candidate1->getDirection() ? ($duration-$candidate1->getDirection()->getDuration()) : ($duration-$candidate1->getDuration()),
                'detourDurationPercent' => $candidate1->getDirection() ? round($duration*100/$candidate1->getDirection()->getDuration()-100, 2) : round($duration*100/$candidate1->getDuration()-100, 2),
                'commonDistance' => $candidate2->getDirection() ? $candidate2->getDirection()->getDistance() : $candidate2->getDistance(),
                'candidate1' => $candidate1->getId(),
                'candidate2' => $candidate2->getId()
            ];
        }
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
            if ($routes[0]->getDistance()<=($candidate1->getDirection()->getDistance()+$candidate1->getMaxDetourDistance())) {
                $detourDistance = true;
            }
        } elseif ($candidate1->getMaxDetourDistancePercent()) {
            // in percentage
            if ($routes[0]->getDistance()<=(($candidate1->getDirection()->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getDirection()->getDistance())) {
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
            if ($routes[0]->getDuration()<=(($candidate1->getDirection()->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getDirection()->getDuration())) {
                $detourDuration = true;
            }
        }
        // we check the common distance
        if ($candidate2->getDirection()) {
            if (($candidate1->getDirection()->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                (($candidate2->getDirection()->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                $commonDistance = true;
            }
        } else {
            if (($candidate1->getDirection()->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
                (($candidate2->getDistance()*100/$candidate1->getDirection()->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
                $commonDistance = true;
            }
        }
        
        // if the detour is acceptable we keep the candidate
        if ($detourDistance && $detourDuration && $commonDistance) {
            $result[] = [
                'route' => is_array($points[0]) ? $this->generateRoute($points[0], null) : null,
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
                'commonDistance' => $candidate2->getDirection() ? $candidate2->getDirection()->getDistance() : $candidate2->getDistance(),
                'direction' => $routes[0],
                'id' => $candidate2->getId()
            ];
        }
        return $result;
    }

    private function checkMassMultiMatch(Candidate $candidate1, Candidate $candidate2, array $routes, ?array $points): ?array
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
            }
        } elseif ($candidate1->getMaxDetourDistancePercent()) {
            // in percentage
            if ($routes[0]['distance']<=(($candidate1->getMassPerson()->getDistance()*($candidate1->getMaxDetourDistancePercent()/100))+$candidate1->getMassPerson()->getDistance())) {
                $detourDistance = true;
            }
        }
        // we check the detour duration
        if ($candidate1->getMaxDetourDuration()) {
            // in seconds
            if ($routes[0]['duration']<=($candidate1->getMassPerson()->getDuration()+$candidate1->getMaxDetourDuration())) {
                $detourDuration = true;
            }
        } elseif ($candidate1->getMaxDetourDurationPercent()) {
            // in percentage
            if ($routes[0]['duration']<=(($candidate1->getMassPerson()->getDuration()*($candidate1->getMaxDetourDurationPercent()/100))+$candidate1->getMassPerson()->getDuration())) {
                $detourDuration = true;
            }
        }
        // we check the common distance
        if (($candidate1->getMassPerson()->getDistance()<ProposalMatcher::getMinCommonDistanceCheck()) ||
            (($candidate2->getMassPerson()->getDistance()*100/$candidate1->getMassPerson()->getDistance()) > ProposalMatcher::getMinCommonDistancePercent())) {
            $commonDistance = true;
        }
        
        // if the detour is acceptable we keep the candidate
        if ($detourDistance && $detourDuration && $commonDistance) {
            $result[] = [
                'route' => is_array($points) ? $this->generateRoute($points, null) : null,
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
        }
        return $result;
    }

    private function getMatch(Candidate $candidate1, Candidate $candidate2, array $routes, ?array $points): ?array
    {
        $result = null;

        // we add the zones to the direction
        // $direction = $this->zoneManager->createZonesForDirection($routes[0]);
        $direction = $routes[0];

        // /!\ detour can be empty has we "force" the match, all directions may not be computed /!\
        $result = [
            'route' => is_array($points) ? $this->generateRoute($points, $routes[0]->getDurations()) : null,
            'originalDistance' => !is_null($candidate1->getDirection()) ? $candidate1->getDirection()->getDistance() : null,
            'acceptedDetourDistance' => $candidate1->getMaxDetourDistance(),
            'newDistance' => $routes[0]->getDistance(),
            'detourDistance' => !is_null($candidate1->getDirection()) ? ($routes[0]->getDistance()-$candidate1->getDirection()->getDistance()) : null,
            'detourDistancePercent' => !is_null($candidate1->getDirection()) ? round($routes[0]->getDistance()*100/$candidate1->getDirection()->getDistance()-100, 2) : null,
            'originalDuration' => !is_null($candidate1->getDirection()) ? $candidate1->getDirection()->getDuration() : null,
            'acceptedDetourDuration' => $candidate1->getMaxDetourDuration(),
            'newDuration' => $routes[0]->getDuration(),
            'detourDuration' => !is_null($candidate1->getDirection()) ? ($routes[0]->getDuration()-$candidate1->getDirection()->getDuration()) : null,
            'detourDurationPercent' => !is_null($candidate1->getDirection()) ? round($routes[0]->getDuration()*100/$candidate1->getDirection()->getDuration()-100, 2) : null,
            'commonDistance' => !is_null($candidate2->getDirection()) ? $candidate2->getDirection()->getDistance() : null,
            'direction' => $direction,
            'id' => $candidate2->getId()
        ];
        return $result;
    }

    /**
     * Generate waypoints for two candidates.
     * Returns all combinations of the points keeping the overall order of the points
     */
    public function generatePointsArray(Candidate $candidate1, Candidate $candidate2): ?array
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
     * Returns the route (order of the points).
     *
     * @param array $points     The points in order
     * @param array $durations  The duration of each part
     * @return void
     */
    private function generateRoute(array $points, ?array $durations)
    {
        $route = [];
        $i = 0;
        $curDuration = 0;
        foreach ($points as $key=>$point) {
            $curDuration = isset($durations[$i]['duration']) ? $durations[$i]['duration'] : $curDuration;
            $route[] = [
                'candidate'         => (substr($key, 0, 1) == 'A') ? 1 : 2,
                'position'          => substr($key, 1),
                'duration'          => isset($durations[$i]) ? $durations[$i]['duration'] : $curDuration,
                'approx_duration'   => isset($durations[$i]) ? $durations[$i]['approx_duration'] : null,    // approx_duration : if the duration to the waypoint isn't strictly returned by the SIG
                'approx_point'      => isset($durations[$i]) ? $durations[$i]['approx_point'] : null,       // approx_point : if the position of the waypoint isn't strictly returned by the SIG
                'address'           => $point
            ];
            $i++;
        }
        return $route;
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
