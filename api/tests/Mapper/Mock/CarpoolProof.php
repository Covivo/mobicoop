<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Tests\Mapper\Mock;

use App\Carpool\Entity\CarpoolProof as V2CarpoolProof;
use App\Mapper\Interfaces\DTO\CarpoolProof\CarpoolProofDTO;

class CarpoolProof
{
    public static function getCarpoolProof(): V2CarpoolProof
    {
        return new V2CarpoolProof();
    }

    public static function getCarpoolProofDto(): CarpoolProofDTO
    {
        $carpoolProofDto = new CarpoolProofDTO();
        $carpoolProofDto->setId(1);
        $carpoolProofDto->setDistance(10000);
        $carpoolProofDto->setPickUpDriver(Waypoint::getWaypointDto());
        $carpoolProofDto->setPickUpPassenger(Waypoint::getWaypointDto());
        $carpoolProofDto->setDropOffDriver(Waypoint::getWaypointDto());
        $carpoolProofDto->setDropOffPassenger(Waypoint::getWaypointDto());
        $carpoolProofDto->setDriver(Actor::getDriverDto());
        $carpoolProofDto->setPassenger(Actor::getPassengerDto());

        return $carpoolProofDto;
    }
}
