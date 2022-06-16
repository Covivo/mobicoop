<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Geography\Service\Point;

use App\Geography\Entity\Address;
use App\Geography\Ressource\Point;

class AddressAdapter
{
    public const PROVIDER = 'MOBICOOP_API';

    public static function addressToPoint(Address $address, ?string $provider = null): Point
    {
        $point = new Point();
        $point->setCountry($address->getAddressCountry());
        $point->setCountryCode($address->getCountryCode());
        $point->setHouseNumber($address->getHouseNumber());
        $point->setLat((float) $address->getLatitude());
        $point->setLocality($address->getAddressLocality());
        $point->setLon((float) $address->getLongitude());
        $point->setMacroRegion($address->getMacroRegion());
        $point->setPostalCode($address->getPostalCode());
        $point->setRegion($address->getRegion());
        $point->setStreetName($address->getStreet() ? $address->getStreet() : $address->getStreetAddress());
        $point->setProvider($provider ? $provider : self::PROVIDER);

        return $point;
    }

    public static function pointToAddress(Point $point): Address
    {
        $address = new Address();
        $address->setAddressCountry($point->getCountry());
        $address->setCountryCode($point->getCountryCode());
        $address->setHouseNumber($point->getHouseNumber());
        $address->setLatitude($point->getLat());
        $address->setAddressLocality($point->getLocality());
        $address->setLongitude($point->getLon());
        $address->setMacroRegion($point->getMacroRegion());
        $address->setPostalCode($point->getPostalCode());
        $address->setRegion($point->getRegion());
        $address->setStreet($point->getStreetName());

        return $address;
    }
}
