<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\User\Interoperability\Ressource\User;

class BadRequestInteroperabilityCarpoolException extends BadRequestHttpException
{
    public const NO_SCHEDULE_FOR_REGULAR = "You have to give a schedule for a regular journey";
    public const NO_OUTWARDTIME_FOR_PUNTUAL = "You have to give an outward time for a punctual journey";
    public const NO_RETURNTIME_FOR_PUNTUAL = "You have to give a return time for a round punctual journey";
    public const NO_OUTWARD_WAYPOINTS = "There is no outward waypoints";
    public const NO_RETURN_WAYPOINTS = "There is no return waypoints";

    public const INVALID_FREQUENCY = "Frequency must be a valid value : 1 punctual, 2 regular";
    public const INVALID_ROLE = "Role must be a valid value : 1 driver, 2 passenger, 3 both driver or passenger";
    public const INVALID_OUTWARD_WAYPOINT = "All your outward waypoints must have at least latitude and longitude fields";
    public const INVALID_NUMBER_OUTWARD_WAYPOINT = "You can't have less than 2 outward waypoints";
    public const INVALID_RETURN_WAYPOINT = "All your return waypoints must have at least latitude and longitude fields";
    public const INVALID_NUMBER_RETURN_WAYPOINT = "You can't have less than 2 return waypoints";
}
