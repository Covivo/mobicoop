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

namespace App\Geography\Service;

use App\Geography\Entity\Address;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\Point\ReversePointProvider;

class AddressCompleter
{
    private $reverserPointProvider;

    public function __construct(ReversePointProvider $reversePointProvider)
    {
        $this->reverserPointProvider = $reversePointProvider;
    }

    public function getAddressByPartialAddressArray(array $addressAsArray): Address
    {
        $address = new Address();

        if (isset($addressAsArray['latitude'])) {
            $address->setLatitude($addressAsArray['latitude']);
        }
        if (isset($addressAsArray['longitude'])) {
            $address->setLongitude($addressAsArray['longitude']);
        }

        if ($points = $this->reverserPointProvider->reverse((float) $address->getLongitude(), (float) $address->getLatitude())) {
            if (count($points) > 0) {
                $address = AddressAdapter::pointToAddress($points[0]);
            }
        }

        if (isset($addressAsArray['latitude'])) {
            $address->setLatitude($addressAsArray['latitude']);
        }
        if (isset($addressAsArray['longitude'])) {
            $address->setLongitude($addressAsArray['longitude']);
        }

        if (isset($addressAsArray['houseNumber'])) {
            $address->setHouseNumber($addressAsArray['houseNumber']);
        }
        if (isset($addressAsArray['street'])) {
            $address->setStreet($addressAsArray['street']);
        }
        if (isset($addressAsArray['streetAddress'])) {
            $address->setStreetAddress($addressAsArray['streetAddress']);
        }
        if (isset($addressAsArray['postalCode'])) {
            $address->setPostalCode($addressAsArray['postalCode']);
        }
        if (isset($addressAsArray['subLocality'])) {
            $address->setSubLocality($addressAsArray['subLocality']);
        }
        if (isset($addressAsArray['addressLocality'])) {
            $address->setAddressLocality($addressAsArray['addressLocality']);
        }
        if (isset($addressAsArray['localAdmin'])) {
            $address->setLocalAdmin($addressAsArray['localAdmin']);
        }
        if (isset($addressAsArray['county'])) {
            $address->setCounty($addressAsArray['county']);
        }
        if (isset($addressAsArray['macroCounty'])) {
            $address->setMacroCounty($addressAsArray['macroCounty']);
        }
        if (isset($addressAsArray['region'])) {
            $address->setRegion($addressAsArray['region']);
        }
        if (isset($addressAsArray['macroRegion'])) {
            $address->setMacroRegion($addressAsArray['macroRegion']);
        }
        if (isset($addressAsArray['addressCountry'])) {
            $address->setAddressCountry($addressAsArray['addressCountry']);
        }
        if (isset($addressAsArray['countryCode'])) {
            $address->setCountryCode($addressAsArray['countryCode']);
        }
        if (isset($addressAsArray['elevation'])) {
            $address->setElevation($addressAsArray['elevation']);
        }
        if (isset($addressAsArray['name'])) {
            $address->setName($addressAsArray['name']);
        }
        if (isset($addressAsArray['home'])) {
            $address->setHome(is_null($addressAsArray['home']) ? false : true);
        }

        return $address;
    }
}
