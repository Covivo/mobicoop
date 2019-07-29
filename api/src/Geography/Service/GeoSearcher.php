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
use App\Community\Entity\CommunityUser;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use App\Geography\Repository\AddressRepository;
use App\RelayPoint\Repository\RelayPointRepository;
use App\RelayPoint\Entity\RelayPoint;

/**
 * The geo searcher service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoSearcher
{
    private $geocoder;
    private $addressRepository;
    private $relayPointRepository;
    private $params;

    /**
     * Constructor.
     */
    public function __construct(PluginProvider $geocoder, AddressRepository $addressRepository, RelayPointRepository $relayPointRepository, array $params)
    {
        $this->geocoder = $geocoder;
        $this->addressRepository = $addressRepository;
        $this->relayPointRepository = $relayPointRepository;
        $this->params = $params;
    }

    /**
     * Returns an array of result addresses (named addresses, relaypoints, sig addresses...)
     *
     * @param string $input     The string representing the user input
     * @param int $userId       The user id
     * @return array            The results
     */
    public function geoCode(string $input, int $userId=null)
    {
        // the result array will contain different addresses :
        // - named addresses (if the user is logged)
        // - relaypoints (with or without private relaypoints depending on if th user is logged)
        // - sig addresses
        // - other objects ? to be defined
        $result = [];
        
        // 1 - named addresses
        if ($userId) {
            $result[] = $this->addressRepository->findByName($input, $userId);
        }

        // 2 - relay points
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($input, RelayPoint::STATUS_ACTIVE);
        // exclude the private relay points
        foreach ($relayPoints as $relayPoint) {
            $exclude = false;
            if ($relayPoint->getCommunity() && $relayPoint->isPrivate()) {
                $exclude = true;
                if ($userId) {
                    // todo : maybe find a quicker way than a foreach :)
                    foreach ($relayPoint->getCommunity()->getCommunityUsers() as $communityUser) {
                        if ($communityUser->getUser()->getId() == $userId && $communityUser->getStatus() == CommunityUser::STATUS_ACCEPTED) {
                            $exclude = false;
                            break;
                        }
                    }
                }
            }
            if (!$exclude) {
                $address = $relayPoint->getAddress();
                $address->setRelayPoint($relayPoint);
                $result[] = $address;
            }
        }
        
        // 3 - sig addresses
        $geoResults = $this->geocoder->geocodeQuery(GeocodeQuery::create($input))->all();
        foreach ($geoResults as $geoResult) {
            // ?? todo : exclude all results that doesn't include any input word at all
            $address = new Address();
            if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLatitude()) {
                $address->setLatitude((string)$geoResult->getCoordinates()->getLatitude());
            }
            if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLongitude()) {
                $address->setLongitude((string)$geoResult->getCoordinates()->getLongitude());
            }
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
            if ($geoResult->getCountry() && $geoResult->getCountry()->getName()) {
                $address->setAddressCountry($geoResult->getCountry()->getName());
            }
            if ($geoResult->getCountry() && $geoResult->getCountry()->getCode()) {
                $address->setCountryCode($geoResult->getCountry()->getCode());
            }

            // Determine the more logical display label considering the params
            $displayLabelTab = [];
            if (trim($address->getStreetAddress())!=="") {
                $displayLabelTab[] = $address->getStreetAddress();
            }


            $displayLabelTab[] = $address->getAddressLocality();

            if (trim($address->getPostalCode())!=="") {
                $displayLabelTab[] = $address->getPostalCode();
            }

            // Theses following parameters are in your .env.local
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

            // if no separators in .env.local, we are using comma
            $displaySeparator = ", ";
            if (isset($this->params[0]['displaySeparator'])) {
                $displaySeparator = $this->params[0]['displaySeparator'];
            }

            $address->setDisplayLabel(implode($displaySeparator, $displayLabelTab));

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
                }
            }
        }
        return false;
    }
}
