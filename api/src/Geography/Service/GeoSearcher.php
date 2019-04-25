<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\Geography\Service;

use App\Geography\Entity\Address;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;


/**
 * The geo searcher service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoSearcher
{
    private $geocoder;

    /**
     * Constructor.
     */
    public function __construct(PluginProvider $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    /**
     * Returns an array of geocoded addresses
     *
     * @param string $input     The string representing the user input
     * @return array            The results
     */
    public function geoCode(string $input)
    {
        $result = [];
        $geoResults = $this->geocoder->geocodeQuery(GeocodeQuery::create($input))->all();
        foreach ($geoResults as $geoResult) {
            $address = new Address();
            $address->setLatitude((string)$geoResult->getCoordinates()->getLatitude());
            $address->setLongitude((string)$geoResult->getCoordinates()->getLongitude());

            $streetNumber = $geoResult->getStreetNumber();
            $streetName = $geoResult->getStreetName();
            $address->setStreetAddress($streetNumber . ' ' . $streetName);
            //SubLocality or Locality of Geocoder-php
            $address->setAddressLocality($geoResult->getSubLocality() ?: $geoResult->getLocality());
            $address->setPostalCode($geoResult->getPostalCode());
            $address->setAddressCountry($geoResult->getCountry()->getName());

            $result[] = $address;
        }
        return $result;
    }
}
