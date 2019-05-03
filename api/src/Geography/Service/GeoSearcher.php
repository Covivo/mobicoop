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
use Geocoder\Query\ReverseQuery;

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
            $address->setHouseNumber($geoResult->getStreetNumber());
            $address->setStreet($geoResult->getStreetName());
            $address->setStreetAddress($geoResult->getStreetName() ? trim(($geoResult->getStreetNumber() ? $geoResult->getStreetNumber() : '') . ' ' . $geoResult->getStreetName()) : null);
            $address->setSubLocality($geoResult->getSubLocality());
            $address->setAddressLocality($geoResult->getLocality());
            foreach ($geoResult->getAdminLevels() as $level) {
                switch ($level->getLevel()) {
                    case 1:
                        $address->setLocalAdmin($level->getName());
                        break;
                    case 2:
                        $address->setCounty($level->getName());
                        break;
                    case 3:
                        $address->setMacroCounty($level->getName());
                        break;
                    case 4:
                        $address->setRegion($level->getName());
                        break;
                    case 5:
                        $address->setMacroRegion($level->getName());
                        break;
                }
            }
            $address->setPostalCode($geoResult->getPostalCode());
            $address->setAddressCountry($geoResult->getCountry()->getName());
            $address->setCountryCode($geoResult->getCountry()->getCode());

            $result[] = $address;
        }
        return $result;
    }

    /**
     * Returns an array of reversed geocoded addresses
     *
     * @param float $lat     The latitude
     * @param float $lon     The longitude
     * @return array            The results
     */
    public function reverseGeoCode(float $lat, float $lon)
    {
        if ($geoResults = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat, $lon))) {
            foreach ($geoResults as $geoResult) {
                if (
                    ($geoResult->getStreetNumber() <> "") &&
                    ($geoResult->getStreetName() <> "") &&
                    ($geoResult->getPostalCode() <> "") &&
                    ($geoResult->getLocality() <> "") &&
                    ($geoResult->getStreetNumber() < 500)
                ) {
                    return $geoResult->getStreetNumber() . ";" . $geoResult->getStreetName() . ";" . $geoResult->getPostalCode() . ";" . $geoResult->getLocality();
                    break;
                }
            }
        }
        return false;

        // $address = new Address();
        // $address->setLatitude((string)$geoResult->getCoordinates()->getLatitude());
        // $address->setLongitude((string)$geoResult->getCoordinates()->getLongitude());
        // $address->setHouseNumber($geoResult->getStreetNumber());
        // $address->setStreet($geoResult->getStreetName());
        // $address->setStreetAddress($geoResult->getStreetName() ? trim(($geoResult->getStreetNumber() ? $geoResult->getStreetNumber() : '') . ' ' . $geoResult->getStreetName()) : null);
        // $address->setSubLocality($geoResult->getSubLocality());
        // $address->setAddressLocality($geoResult->getLocality());
        // foreach ($geoResult->getAdminLevels() as $level) {
        //     switch ($level->getLevel()) {
        //         case 1:
        //             $address->setLocalAdmin($level->getName());
        //             break;
        //         case 2:
        //             $address->setCounty($level->getName());
        //             break;
        //         case 3:
        //             $address->setMacroCounty($level->getName());
        //             break;
        //         case 4:
        //             $address->setRegion($level->getName());
        //             break;
        //         case 5:
        //             $address->setMacroRegion($level->getName());
        //             break;
        //     }
        // }
        // $address->setPostalCode($geoResult->getPostalCode());
        // $address->setAddressCountry($geoResult->getCountry()->getName());
        // $address->setCountryCode($geoResult->getCountry()->getCode());
    }
}
