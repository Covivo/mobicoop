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

use App\ExternalService\Core\Domain\Entity\CarpoolProof\DriverEntity;
use App\ExternalService\Core\Domain\Entity\CarpoolProof\PassengerEntity;
use App\ExternalService\Interfaces\DTO\CarpoolProof\DriverDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\PassengerDto;

class Actor
{
    public static function getDriverDto(): DriverDto
    {
        $driverDto = new DriverDto();
        $driverDto->setRevenue(5);
        $driverDto->setGivenName('Jean-Michel');
        $driverDto->setLastName('Jefaisdestests');
        $driverDto->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $driverDto->setPhone('0303030303');

        return $driverDto;
    }

    public static function getDriverEntity(): DriverEntity
    {
        $driverEntity = new DriverEntity();
        $driverEntity->setRevenue(5);
        $driverEntity->setGivenName('Jean-Michel');
        $driverEntity->setLastName('Jefaisdestests');
        $driverEntity->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $driverEntity->setPhone('0303030303');

        return $driverEntity;
    }

    public static function getPassengerDto(): PassengerDto
    {
        $passengerDto = new PassengerDto();
        $passengerDto->setContribution(5);
        $passengerDto->setGivenName('Jean-Michel');
        $passengerDto->setLastName('Jefaisdestests');
        $passengerDto->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $passengerDto->setPhone('0303030303');

        return $passengerDto;
    }

    public static function getPassengerEntity(): PassengerEntity
    {
        $passengerEntity = new PassengerEntity();
        $passengerEntity->setContribution(5);
        $passengerEntity->setGivenName('Jean-Michel');
        $passengerEntity->setLastName('Jefaisdestests');
        $passengerEntity->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $passengerEntity->setPhone('0303030303');

        return $passengerEntity;
    }
}
