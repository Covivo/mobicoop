<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Service\MobicoopMatcher;

use App\Geography\Entity\Address;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\PointSearcher;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherAddressBuilder
{
    public const LONGITUDE = 0;
    public const LATITUDE = 1;

    private $_pointSearcher;

    public function __construct(
        PointSearcher $pointSearcher
    ) {
        $this->_pointSearcher = $pointSearcher;
    }

    public function build(array $point): Address
    {
        $address = new Address();
        $address->setLatitude($point[self::LATITUDE]);
        $address->setLongitude($point[self::LONGITUDE]);

        return $this->_reverseGeocodeAddresses($address);
    }

    private function _reverseGeocodeAddresses(Address $address): Address
    {
        if ($foundAddresses = $this->_pointSearcher->reverse($address->getLongitude(), $address->getLatitude())) {
            return AddressAdapter::pointToAddress($foundAddresses[0]);
        }

        return $address;
    }
}
