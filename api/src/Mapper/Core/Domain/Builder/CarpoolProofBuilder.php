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

namespace App\Mapper\Core\Domain\Builder;

use App\Carpool\Entity\CarpoolProof;
use App\ExternalService\Interfaces\DTO\DTO;
use App\Mapper\Core\Application\Ports\BuilderPort;
use App\Mapper\Interfaces\DTO\CarpoolProof\CarpoolProofDTO;
use App\Mapper\Interfaces\DTO\CarpoolProof\DriverDTO;
use App\Mapper\Interfaces\DTO\CarpoolProof\PassengerDTO;
use App\Mapper\Interfaces\DTO\CarpoolProof\WaypointDTO;

class CarpoolProofBuilder implements BuilderPort
{
    public function build(object $object): DTO
    {
        return $this->_buildCarpoolProofDto($object);
    }

    private function _buildCarpoolProofDto(CarpoolProof $carpoolProof): CarpoolProofDTO
    {
        $carpoolProofDto = new CarpoolProofDTO();
        $carpoolProofDto->setId($carpoolProof->getId());
        $carpoolProofDto->setDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance());

        $passengerDto = new PassengerDTO();
        $passengerDto->setId($carpoolProof->getPassenger()->getId());
        $passengerDto->setGivenName($carpoolProof->getPassenger()->getGivenName());
        $passengerDto->setLastName($carpoolProof->getPassenger()->getFamilyName());
        $passengerDto->setBirthDate($carpoolProof->getPassenger()->getBirthDate());
        $passengerDto->setPhone($carpoolProof->getPassenger()->getTelephone());
        $passengerDto->setSeats($carpoolProof->getAsk()->getCriteria()->getSeatsPassenger());
        $passengerDto->setContribution((int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0));
        $carpoolProofDto->setPassenger($passengerDto);

        $driverDto = new DriverDTO();
        $driverDto->setId($carpoolProof->getDriver()->getId());
        $driverDto->setGivenName($carpoolProof->getDriver()->getGivenName());
        $driverDto->setLastName($carpoolProof->getDriver()->getFamilyName());
        $driverDto->setBirthDate($carpoolProof->getDriver()->getBirthDate());
        $driverDto->setPhone($carpoolProof->getDriver()->getTelephone());
        $driverDto->setRevenue((int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0));
        $carpoolProofDto->setDriver($driverDto);

        if (!is_null($carpoolProof->getPickUpPassengerAddress()) && !is_null($carpoolProof->getPickUpPassengerDate())) {
            $pickUpPassenger = new WaypointDTO();
            $pickUpPassenger->setLat($carpoolProof->getPickUpPassengerAddress()->getLatitude());
            $pickUpPassenger->setLon($carpoolProof->getPickUpPassengerAddress()->getLongitude());
            $pickUpPassenger->setDatetime($carpoolProof->getPickUpPassengerDate());
            $carpoolProofDto->setPickUpPassenger($pickUpPassenger);
        }

        if (!is_null($carpoolProof->getPickUpDriverAddress()) && !is_null($carpoolProof->getPickUpDriverDate())) {
            $pickUpDriver = new WaypointDTO();
            $pickUpDriver->setLat($carpoolProof->getPickUpDriverAddress()->getLatitude());
            $pickUpDriver->setLon($carpoolProof->getPickUpDriverAddress()->getLongitude());
            $pickUpDriver->setDatetime($carpoolProof->getPickUpDriverDate());
            $carpoolProofDto->setPickUpDriver($pickUpDriver);
        }
        if (!is_null($carpoolProof->getDropOffDriverAddress()) && !is_null($carpoolProof->getDropOffDriverDate())) {
            $dropOffDriver = new WaypointDTO();
            $dropOffDriver->setLat($carpoolProof->getDropOffDriverAddress()->getLatitude());
            $dropOffDriver->setLon($carpoolProof->getDropOffDriverAddress()->getLongitude());
            $dropOffDriver->setDatetime($carpoolProof->getDropOffDriverDate());
            $carpoolProofDto->setDropOffDriver($dropOffDriver);
        }
        if (!is_null($carpoolProof->getDropOffPassengerAddress()) && !is_null($carpoolProof->getDropOffPassengerDate())) {
            $dropOffPassenger = new WaypointDTO();
            $dropOffPassenger->setLat($carpoolProof->getDropOffPassengerAddress()->getLatitude());
            $dropOffPassenger->setLon($carpoolProof->getDropOffPassengerAddress()->getLongitude());
            $dropOffPassenger->setDatetime($carpoolProof->getDropOffPassengerDate());
            $carpoolProofDto->setDropOffPassenger($dropOffPassenger);
        }

        return $carpoolProofDto;
    }
}
