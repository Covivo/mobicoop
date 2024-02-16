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

namespace App\ExternalService\Interfaces\Builder;

use App\ExternalService\Core\Domain\Entity\CarpoolProof\DriverEntity;
use App\ExternalService\Core\Domain\Entity\CarpoolProof\PassengerEntity;
use App\ExternalService\Interfaces\DTO\CarpoolProof\DriverDto;
use App\ExternalService\Interfaces\DTO\CarpoolProof\PassengerDto;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActorEntityBuilder
{
    public function buildDriver(DriverDto $driverDto): DriverEntity
    {
        $driverEntity = new DriverEntity();
        $driverEntity->setGivenName($driverDto->getGivenName());
        $driverEntity->setLastName($driverDto->getLastName());
        $driverEntity->setPhone($driverDto->getPhone());
        $driverEntity->setBirthDate($driverDto->getBirthDate());
        $driverEntity->setRevenue($driverDto->getRevenue());

        return $driverEntity;
    }

    public function buildPassenger(PassengerDto $driverDto): PassengerEntity
    {
        return new PassengerEntity();
    }
}
