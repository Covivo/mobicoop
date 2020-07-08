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
use App\Geography\ProviderFactory\PeliasAddress;

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
    private $defaultSigResultNumber;
    private $defaultNamedResultNumber;
    private $defaultRelayPointResultNumber;
    private $defaultEventResultNumber;
    private $geoDataFixes;
    private $distanceOrder;

    /**
     * Constructor.
     */
    public function __construct(PluginProvider $geocoder, GeoTools $geoTools, UserRepository $userRepository, AddressRepository $addressRepository, RelayPointRepository $relayPointRepository, EventRepository $eventRepository, IconRepository $iconRepository, string $iconPath, string $dataPath, string $defaultSigResultNumber, string $defaultNamedResultNumber, string $defaultRelayPointResultNumber, string $defaultEventResultNumber, array $geoDataFixes, bool $distanceOrder)
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
        $this->defaultSigResultNumber = $defaultSigResultNumber;
        $this->defaultNamedResultNumber = $defaultNamedResultNumber;
        $this->defaultRelayPointResultNumber = $defaultRelayPointResultNumber;
        $this->defaultEventResultNumber = $defaultEventResultNumber;
        $this->geoDataFixes = $geoDataFixes;
        $this->distanceOrder = $distanceOrder;
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
        $userPrioritize = null;
        if ($token) {
            if ($user = $this->userRepository->findOneBy(['geoToken'=>$token])) {
                // we search its home address
                foreach ($user->getAddresses() as $address) {
                    if ($address->isHome()) {
                        $userPrioritize = [
                            'latitude' => $address->getLatitude(),
                            'longitude' => $address->getLongitude()
                        ];
                        break;
                    }
                }
            }
        }

        // 1 - sig addresses
        if (!is_null($userPrioritize)) {
            $geoResults = $this->geocoder->geocodeQuery(GeocodeQuery::create($input)
            ->withLimit($this->defaultSigResultNumber)
            ->withData('userPrioritize', $userPrioritize))
            ->all();
        } else {
            $geoResults = $this->geocoder->geocodeQuery(GeocodeQuery::create($input)
            ->withLimit($this->defaultSigResultNumber))
            ->all();
        }
        
        foreach ($geoResults as $geoResult) {
            /**
             * @var PeliasAddress $geoResult
             */
            
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
            // add layer if handled by the provider
            if (method_exists($geoResult, 'getLayer')) {
                $address->setLayer($this->getLayer($geoResult->getLayer()));
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

            $address->setProvidedBy($geoResult->getProvidedBy());

            if ($address->getVenue()) {
                $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_VENUE)->getFileName());
            }

            if (method_exists($geoResult, 'getDistance')) {
                if (!is_null($geoResult->getDistance())) {
                    $address->setDistance($geoResult->getDistance());
                }
            }

            // add id and fix result if handled by the provider
            if (method_exists($geoResult, 'getId')) {
                $address = $this->fixAddress($geoResult->getId(), $address);
            }

            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));

            $result[] = $address;
        }

        if ($this->distanceOrder) {
            usort($result, function ($a, $b) {
                return $a->getDistance()>$b->getDistance();
            });
        }
        
        // 2 - named addresses
        if ($user) {
            $namedAddresses = $this->addressRepository->findByName($input, $user->getId());
            if (count($namedAddresses)>0) {
                $i = 0;
                foreach ($namedAddresses as $address) {
                    $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
                    $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_PERSONAL)->getFileName());
                    $result[] = $address;
                    $i++;
                    if ($i>=$this->defaultNamedResultNumber) {
                        break;
                    }
                }
            }
        }

        // 3 - relay points
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($input, RelayPoint::STATUS_ACTIVE);
        // exclude the private relay points
        $i = 0;
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
                $relayPointTypes = $relayPoint->getRelayPointTypes();
                if (count($relayPointTypes)>0 && is_null($relayPointTypes[0]->getIcon())) {
                    $relayPointTypes[0]->setIcon($this->iconRepository->find(1));
                }
                
                if (count($relayPointTypes)>0 && !is_null($relayPointTypes[0]->getIcon())) {
                    if ($relayPointTypes[0]->getIcon()->getPrivateIconLinked()) {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPointTypes[0]->getIcon()->getPrivateIconLinked()->getFileName());
                    } else {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPointTypes[0]->getIcon()->getFileName());
                    }
                }
                $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
                $result[] = $address;
                $i++;
                if ($i>=$this->defaultRelayPointResultNumber) {
                    break;
                }
            }
        }

        // 4 - Events points
        $events = $this->eventRepository->findByNameAndStatus($input, Event::STATUS_ACTIVE);
        // exclude the private relay points
        $i = 0;
        foreach ($events as $event) {
            $address = $event->getAddress();
            $address->setEvent($event);
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
            $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_EVENT)->getFileName());
            $result[] = $address;
            $i++;
            if ($i>=$this->defaultEventResultNumber) {
                break;
            }
        }

        return $result;
    }

    /**
     * Returns an array of reversed geocoded addresses
     *
     * @param float $lat     The latitude
     * @param float $lon     The longitude
     * @return array         The array of addresses found
     */
    public function reverseGeoCode(float $lat, float $lon)
    {
        $addresses = [];
        if ($geoResults = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat, $lon))) {
            foreach ($geoResults as $geoResult) {
                $address = new Address();
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
                // add layer if handled by the provider
                if (method_exists($geoResult, 'getLayer')) {
                    $address->setLayer($this->getLayer($geoResult->getLayer()));
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

                // add id and fix result if handled by the provider
                if (method_exists($geoResult, 'getId')) {
                    $address = $this->fixAddress($geoResult->getId(), $address);
                }

                $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));

                $addresses[] = $address;
            }
            return $addresses;
        }
        return false;
    }

    /**
     * Get an address using an array. The array may contain only some informations like latitude or longitude.
     * The other informations are retrieved from the GeoSearcher.
     *
     * @param array $point  The point
     * @return Address
     */
    public function getAddressByPartialAddressArray(array $point)
    {
        $address = new Address();

        // first we set the lat/lon
        if (isset($point['latitude'])) {
            $address->setLatitude($point['latitude']);
        }
        if (isset($point['longitude'])) {
            $address->setLongitude($point['longitude']);
        }

        // then we reverse geocode, to get a full address if the other properties are not sent
        if ($addresses = $this->reverseGeoCode($address->getLatitude(), $address->getLongitude())) {
            if (count($addresses)>0) {
                $address = $addresses[0];
            }
        }

        // we set again the lat/lon to keep the original values !
        if (isset($point['latitude'])) {
            $address->setLatitude($point['latitude']);
        }
        if (isset($point['longitude'])) {
            $address->setLongitude($point['longitude']);
        }

        // if other properties are sent we use them
        if (isset($point['houseNumber'])) {
            $address->setHouseNumber($point['houseNumber']);
        }
        if (isset($point['street'])) {
            $address->setStreet($point['street']);
        }
        if (isset($point['streetAddress'])) {
            $address->setStreetAddress($point['streetAddress']);
        }
        if (isset($point['postalCode'])) {
            $address->setPostalCode($point['postalCode']);
        }
        if (isset($point['subLocality'])) {
            $address->setSubLocality($point['subLocality']);
        }
        if (isset($point['addressLocality'])) {
            $address->setAddressLocality($point['addressLocality']);
        }
        if (isset($point['localAdmin'])) {
            $address->setLocalAdmin($point['localAdmin']);
        }
        if (isset($point['county'])) {
            $address->setCounty($point['county']);
        }
        if (isset($point['macroCounty'])) {
            $address->setMacroCounty($point['macroCounty']);
        }
        if (isset($point['region'])) {
            $address->setRegion($point['region']);
        }
        if (isset($point['macroRegion'])) {
            $address->setMacroRegion($point['macroRegion']);
        }
        if (isset($point['addressCountry'])) {
            $address->setAddressCountry($point['addressCountry']);
        }
        if (isset($point['countryCode'])) {
            $address->setCountryCode($point['countryCode']);
        }
        if (isset($point['elevation'])) {
            $address->setElevation($point['elevation']);
        }
        if (isset($point['name'])) {
            $address->setName($point['name']);
        }
        if (isset($point['home'])) {
            $address->setHome($point['home']);
        }
        return $address;
    }

    /**
     * Fix potential wrong addresses.
     *
     * @param string $id        The id of the source data
     * @param Address $address  The address to fix
     * @return Address The address fixed
     */
    private function fixAddress(string $id, Address $address)
    {
        // we search in the fixes if there's one corresponding to the id
        if (array_key_exists($id, $this->geoDataFixes)) {
            foreach ($this->geoDataFixes[$id] as $property=>$value) {
                if (method_exists($address, 'set'.ucfirst($property))) {
                    $method = 'set'.ucfirst($property);
                    $address->$method($value);
                }
            }
        }
        return $address;
    }

    /**
     * Get layer id by layer string
     *
     * @param string $layer The string layer
     * @return int|null  The int layer or null
     */
    private function getLayer(string $layer): ?int
    {
        switch ($layer) {
            case 'address': return Address::LAYER_ADDRESS;
            case 'locality': return Address::LAYER_LOCALITY;
            default: return null;
        }
    }
}
