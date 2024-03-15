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

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof as V2CarpoolProof;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Matching;
use App\Geography\Entity\Address;
use App\Mapper\Interfaces\DTO\CarpoolProof\CarpoolProofDTO;
use App\User\Entity\User;

class CarpoolProof
{
    public static function getCarpoolProof(): V2CarpoolProof
    {
        $carpoolProof = new V2CarpoolProof();

        $carpoolProof->setId(1);

        $passenger = new User();
        $passenger->setId(2);
        $passenger->setGivenName('Francis-Patrick');
        $passenger->setFamilyName('Cesttoujoursuntest');
        $passenger->setBirthDate(\DateTime::createFromFormat('d/m/Y', '20/12/1983'));
        $passenger->setTelephone('0606060606');
        $carpoolProof->setPassenger($passenger);

        $driver = new User();
        $driver->setId(1);
        $driver->setGivenName('Jean-Michel');
        $driver->setFamilyName('Jefaisdestests');
        $driver->setBirthDate(\DateTime::createFromFormat('d/m/Y', '03/02/1982'));
        $driver->setTelephone('0303030303');
        $carpoolProof->setDriver($driver);

        $pickUpPassengerAddress = new Address();
        $pickUpPassengerAddress->setLatitude(48.6937223);
        $pickUpPassengerAddress->setLongitude(6.1834097);

        $pickUpDriverAddress = $pickUpPassengerAddress;
        $carpoolProof->setPickUpPassengerAddress($pickUpPassengerAddress);
        $carpoolProof->setPickUpDriverAddress($pickUpDriverAddress);

        $dropOffPassengerAddress = new Address();
        $dropOffPassengerAddress->setLatitude(48.7145001);
        $dropOffPassengerAddress->setLongitude(6.2611518);

        $dropOffDriverAddress = $dropOffPassengerAddress;

        $carpoolProof->setDropOffPassengerAddress($dropOffPassengerAddress);
        $carpoolProof->setDropOffDriverAddress($dropOffDriverAddress);

        $pickUpPassengerDate = \DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00');
        $pickUpDriverDate = \DateTime::createFromFormat('Ymd H:i:s', '20240801 12:00:00');

        $carpoolProof->setPickUpPassengerDate($pickUpPassengerDate);
        $carpoolProof->setPickUpDriverDate($pickUpDriverDate);

        $dropOffPassengerDate = \DateTime::createFromFormat('Ymd H:i:s', '20240801 12:17:00');
        $dropOffDriverDate = \DateTime::createFromFormat('Ymd H:i:s', '20240801 12:17:00');

        $carpoolProof->setDropOffPassengerDate($dropOffPassengerDate);
        $carpoolProof->setDropOffDriverDate($dropOffDriverDate);

        $ask = new Ask();
        $matching = new Matching();
        $matching->setCommonDistance(7700);
        $ask->setMatching($matching);
        $criteria = new Criteria();
        $criteria->setPassengerComputedRoundedPrice(5);
        $ask->setCriteria($criteria);

        $carpoolProof->setAsk($ask);

        return $carpoolProof;
    }

    public static function getCarpoolProofDto(): CarpoolProofDTO
    {
        $carpoolProofDto = new CarpoolProofDTO();
        $carpoolProofDto->setId(1);
        $carpoolProofDto->setDistance(7700);
        $carpoolProofDto->setPickUpDriver(Waypoint::getOriginWaypointDto());
        $carpoolProofDto->setPickUpPassenger(Waypoint::getOriginWaypointDto());
        $carpoolProofDto->setDropOffDriver(Waypoint::getDestinationWaypointDto());
        $carpoolProofDto->setDropOffPassenger(Waypoint::getDestinationWaypointDto());
        $carpoolProofDto->setDriver(Actor::getDriverDto());
        $carpoolProofDto->setPassenger(Actor::getPassengerDto());

        return $carpoolProofDto;
    }
}
