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

use App\Carpool\Entity\MobicoopMatcher\Waypoint;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherWaypointExtractor
{
    private $_waypoints;

    public function __construct(array $waypoints)
    {
        $this->_waypoints = $waypoints;
    }

    public function findFirstWaypoint(): array
    {
        foreach ($this->_waypoints as $waypoint) {
            foreach ($waypoint['actors'] as $actor) {
                if (Waypoint::ROLE_DRIVER == $actor['role'] && Waypoint::STEP_START == $actor['step']) {
                    return $waypoint;
                }
            }
        }
    }

    public function findPickUpPoint(): array
    {
        foreach ($this->_waypoints as $waypoint) {
            foreach ($waypoint['actors'] as $actor) {
                if (Waypoint::ROLE_PASSENGER == $actor['role'] && Waypoint::STEP_START == $actor['step']) {
                    return $waypoint;
                }
            }
        }
    }

    public function findDropOffPoint(): array
    {
        foreach ($this->_waypoints as $waypoint) {
            foreach ($waypoint['actors'] as $actor) {
                if (Waypoint::ROLE_PASSENGER == $actor['role'] && Waypoint::STEP_FINISH == $actor['step']) {
                    return $waypoint;
                }
            }
        }
    }
}
