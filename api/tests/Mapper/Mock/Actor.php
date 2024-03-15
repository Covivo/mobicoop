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

use App\Mapper\Interfaces\DTO\CarpoolProof\DriverDTO;
use App\Mapper\Interfaces\DTO\CarpoolProof\PassengerDTO;

class Actor
{
    public static function getDriverDto(): DriverDTO
    {
        $driverDto = new DriverDTO();
        $driverDto->setRevenue(5);
        $driverDto->setGivenName('Jean-Michel');
        $driverDto->setLastName('Jefaisdestests');
        $driverDto->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $driverDto->setPhone('0303030303');

        return $driverDto;
    }

    public static function getPassengerDto(): PassengerDTO
    {
        $passengerDto = new PassengerDTO();
        $passengerDto->setContribution(5);
        $passengerDto->setGivenName('Francis-Patrick');
        $passengerDto->setLastName('Cesttoujoursuntest');
        $passengerDto->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $passengerDto->setPhone('0303030303');

        return $passengerDto;
    }
}
