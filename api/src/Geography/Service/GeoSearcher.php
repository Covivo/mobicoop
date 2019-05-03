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
    private $params;

    /**
     * Constructor.
     */
    public function __construct(PluginProvider $geocoder, array $params)
    {
        $this->geocoder = $geocoder;
        $this->params = $params;
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


            // Determine the more logical display label considering the params
            $displayLabelTab = [];
            if (trim($address->getStreetAddress())!=="") {
                $displayLabelTab[] = $address->getStreetAddress();
            }


            $displayLabelTab[] = $address->getAddressLocality();

            if (trim($address->getPostalCode())!=="") {
                $displayLabelTab[] = $address->getPostalCode();
            }

            if (isset($this->params[0]['displayRegion']) && trim($this->params[0]['displayRegion'])==="true") {
                if (trim($address->getMacroRegion())!=="") {
                    $displayLabelTab[] = $address->getMacroRegion();
                }
            }

            if (isset($this->params[0]['displayCountry']) && trim($this->params[0]['displayCountry'])==="true") {
                if (trim($address->getCountryCode())!=="") {
                    $displayLabelTab[] = $address->getCountryCode();
                }
            }

            $address->setDisplayLabel(implode($this->params[0]['displaySeparator'], $displayLabelTab));

            $result[] = $address;
        }
        return $result;
    }
}
