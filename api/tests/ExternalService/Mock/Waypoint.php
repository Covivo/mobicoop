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

use App\ExternalService\Core\Domain\Entity\CarpoolProof\WaypointEntity;
use App\ExternalService\Interfaces\DTO\CarpoolProof\WaypointDto;

class Waypoint
{
    public static function getWaypointDto(): WaypointDto
    {
        $pickUpDriverDto = new WaypointDto();
        $pickUpDriverDto->setLat(18.0146548);
        $pickUpDriverDto->setLon(6.0146548);
        $pickUpDriverDto->setDatetime(\DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00'));

        return $pickUpDriverDto;
    }

    public static function getWaypointEntity(): WaypointEntity
    {
        $pickUpDriverEntity = new WaypointEntity();
        $pickUpDriverEntity->setLat(18.0146548);
        $pickUpDriverEntity->setLon(6.0146548);
        $pickUpDriverEntity->setDatetime(\DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00'));

        return $pickUpDriverEntity;
    }
}