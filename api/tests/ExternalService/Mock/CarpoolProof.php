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

namespace App\Tests\ExternalService\Mock;

use App\ExternalService\Core\Domain\Entity\CarpoolProof\CarpoolProofEntity;
use App\ExternalService\Interfaces\DTO\CarpoolProof\CarpoolProofDto;

class CarpoolProof
{
    public static function getCarpoolProofDto(): CarpoolProofDto
    {
        $carpoolProofDto = new CarpoolProofDto();
        $carpoolProofDto->setId(1);
        $carpoolProofDto->setDistance(10000);
        $carpoolProofDto->setPickUpDriver(Waypoint::getWaypointDto());
        $carpoolProofDto->setPickUpPassenger(Waypoint::getWaypointDto());
        $carpoolProofDto->setDropOffDriver(Waypoint::getWaypointDto());
        $carpoolProofDto->setDropOffPassenger(Waypoint::getWaypointDto());

        return $carpoolProofDto;
    }

    public static function getCarpoolProofEntity(): CarpoolProofEntity
    {
        $carpoolProofEntity = new CarpoolProofEntity();
        $carpoolProofEntity->setId(1);
        $carpoolProofEntity->setDistance(10000);
        $carpoolProofEntity->setPickUpDriver(Waypoint::getWaypointEntity());
        $carpoolProofEntity->setPickUpPassenger(Waypoint::getWaypointEntity());
        $carpoolProofEntity->setDropOffDriver(Waypoint::getWaypointEntity());
        $carpoolProofEntity->setDropOffPassenger(Waypoint::getWaypointEntity());

        return $carpoolProofEntity;
    }
}
