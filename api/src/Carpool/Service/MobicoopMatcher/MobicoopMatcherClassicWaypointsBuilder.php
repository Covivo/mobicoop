<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
 */

namespace App\Carpool\Service\MobicoopMatcher;

use App\Carpool\Entity\Matching;
use App\Carpool\Entity\MobicoopMatcher\Waypoint;
use App\Carpool\Entity\Waypoint as ClassicWaypoint;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherClassicWaypointsBuilder
{
    private $_classicWaypoints;
    private $_addressBuilder;
    private $_waypointExtractor;
    private $_computingTools;

    public function __construct(MobicoopMatcherAddressBuilder $addressBuilder, MobicoopMatcherComputingTools $computingTools)
    {
        $this->_addressBuilder = $addressBuilder;
        $this->_computingTools = $computingTools;
    }

    public function build(Matching $currentMatching, array $waypoints): Matching
    {
        $this->_waypointExtractor = new MobicoopMatcherWaypointExtractor($waypoints);
        $this->_classicWaypoints = [];

        foreach ($waypoints as $waypoint) {
            $this->_treatWaypoint($waypoint);
        }
        foreach ($this->_classicWaypoints as $currentClassicWaypoint) {
            $currentMatching->addWaypoint($currentClassicWaypoint);
        }

        return $currentMatching;
    }

    private function _computeDurationFromStart(array $waypoint)
    {
        $startPoint = $this->_waypointExtractor->findFirstWaypoint();

        return $this->_computingTools->computeElapsedTimeInSeconds($startPoint['time'], $waypoint['time']);
    }

    /**
     * @return ClassicWaypoint
     */
    private function _treatWaypoint(array $waypoint)
    {
        foreach ($waypoint['actors'] as $actor) {
            if (Waypoint::STEP_NEUTRAL == $actor['step']) {
                continue;
            }

            $classicWaypoint = new ClassicWaypoint();

            if (Waypoint::ROLE_DRIVER == $actor['role']) {
                $classicWaypoint->setRole(ClassicWaypoint::ROLE_DRIVER);
            } elseif (Waypoint::ROLE_PASSENGER == $actor['role']) {
                $classicWaypoint->setRole(ClassicWaypoint::ROLE_PASSENGER);
            }

            if (Waypoint::ROLE_DRIVER == $actor['role'] && Waypoint::STEP_START == $actor['step']) {
                $classicWaypoint->setPosition(0);
            }
            if (Waypoint::ROLE_DRIVER == $actor['role'] && Waypoint::STEP_FINISH == $actor['step']) {
                $classicWaypoint->setDestination(true);
            }

            if (is_null($classicWaypoint->getPosition())) {
                $this->_setUniquePosition($classicWaypoint);
            }

            $classicWaypoint->setDuration($this->_computeDurationFromStart($waypoint));

            $classicWaypoint->setAddress($this->_addressBuilder->build($waypoint['point']));

            $this->_classicWaypoints[] = $classicWaypoint;
        }
    }

    private function _setUniquePosition(ClassicWaypoint $classicWaypoint)
    {
        foreach ($this->_classicWaypoints as $currentClassicWaypoint) {
            $position = $currentClassicWaypoint->getPosition();
        }
        ++$position;
        $classicWaypoint->setPosition($position);
    }
}
