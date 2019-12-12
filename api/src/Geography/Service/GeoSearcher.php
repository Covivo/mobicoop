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

use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use App\Geography\Entity\Address;
use App\Community\Entity\CommunityUser;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use App\Geography\Repository\AddressRepository;
use App\RelayPoint\Repository\RelayPointRepository;
use App\RelayPoint\Entity\RelayPoint;
use App\User\Repository\UserRepository;
use App\Image\Repository\IconRepository;

/**
 * The geo searcher service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoSearcher
{
    const ICON_ADDRESS_ANY = 1;
    const ICON_ADDRESS_PERSONAL = 2;
    const ICON_COMMUNITY = 3;
    const ICON_EVENT = 4;
    const ICON_VENUE = 23;


    private $geocoder;
    private $geoTools;
    private $userRepository;
    private $addressRepository;
    private $relayPointRepository;
    private $iconRepository;
    private $iconPath;
    private $dataPath;
    private $eventRepository;

    /**
     * Constructor.
     */
    public function __construct(PluginProvider $geocoder, GeoTools $geoTools, UserRepository $userRepository, AddressRepository $addressRepository, RelayPointRepository $relayPointRepository, EventRepository $eventRepository, IconRepository $iconRepository, string $iconPath, string $dataPath)
    {
        $this->geocoder = $geocoder;
        $this->geoTools = $geoTools;
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
        $this->relayPointRepository = $relayPointRepository;
        $this->iconRepository = $iconRepository;
        $this->iconPath = $iconPath;
        $this->dataPath = $dataPath;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Returns an array of result addresses (named addresses, relaypoints, sig addresses...)
     *
     * @param string $input     The string representing the user input
     * @param string $token     The geographic token authorization
     * @return array            The results
     */
    public function geoCode(string $input, string $token=null)
    {
        // the result array will contain different addresses :
        // - named addresses (if the user is logged)
        // - relaypoints (with or without private relaypoints depending on if th user is logged)
        // - sig addresses
        // - other objects ? to be defined
        $result = [];

        // First we handle the quote
        $input = str_replace("'", "''", $input);

        // if we have a token, we search for the corresponding user
        $user = null;
        if ($token) {
            $user = $this->userRepository->findOneBy(['geoToken'=>$token]);
        }
        
        // 1 - named addresses
        if ($user) {
            $namedAddresses = $this->addressRepository->findByName($input, $user->getId());
            if (count($namedAddresses)>0) {
                foreach ($namedAddresses as $address) {
                    $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
                    $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_PERSONAL)->getFileName());
                    $result[] = $address;
                }
            }
        }

        // 2 - Events points
        $events = $this->eventRepository->findByNameAndStatus($input, Event::STATUS_ACTIVE);
        // exclude the private relay points
        foreach ($events as $event) {
            $address = $event->getAddress();
            $address->setEvent($event);
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
            $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_EVENT)->getFileName());
            $result[] = $address;
        }

        // 3 - relay points
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($input, RelayPoint::STATUS_ACTIVE);
        // exclude the private relay points
        foreach ($relayPoints as $relayPoint) {
            $exclude = false;
            if ($relayPoint->getCommunity() && $relayPoint->isPrivate()) {
                $exclude = true;
                if ($user) {
                    // todo : maybe find a quicker way than a foreach :)
                    foreach ($relayPoint->getCommunity()->getCommunityUsers() as $communityUser) {
                        if ($communityUser->getUser()->getId() == $user->getId() && $communityUser->getStatus() == (CommunityUser::STATUS_ACCEPTED_AS_MEMBER or CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)) {
                            $exclude = false;
                            break;
                        }
                    }
                }
            }
            if (!$exclude) {
                $address = $relayPoint->getAddress();
                $address->setRelayPoint($relayPoint);
                // set address icon
                if (count($relayPoint->getRelayPointTypes())>0) {
                    if ($relayPoint->getRelayPointTypes()[0]->getIcon()->getPrivateIconLinked()) {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPoint->getRelayPointTypes()[0]->getIcon()->getPrivateIconLinked()->getFileName());
                    } else {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPoint->getRelayPointTypes()[0]->getIcon()->getFileName());
                    }
                }
                $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
                $result[] = $address;
            }
        }

        // 4 - sig addresses
        $geoResults = $this->geocoder->geocodeQuery(GeocodeQuery::create($input))->all();
        // var_dump($geoResults);exit;
        foreach ($geoResults as $geoResult) {
            // ?? todo : exclude all results that doesn't include any input word at all
            $address = new Address();
            // set address icon
            $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_ANY)->getFileName());
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
            // add venue if handled by the provider
            if (method_exists($geoResult, 'getVenue')) {
                $address->setVenue($geoResult->getVenue());
            }
            if ((method_exists($geoResult, 'getEstablishment')) && ($geoResult->getEstablishment() != null)) {
                $address->setVenue($geoResult->getEstablishment());
            }

            if ((method_exists($geoResult, 'getPointOfInterest')) && ($geoResult->getPointOfInterest() != null)) {
                $address->setVenue($geoResult->getPointOfInterest());
            }

            if ($address->getVenue()) {
                $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_VENUE)->getFileName());
            }
            
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));

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
