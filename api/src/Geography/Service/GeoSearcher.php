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
 */

namespace App\Geography\Service;

use App\Community\Entity\CommunityUser;
use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use App\Geography\Entity\Address;
use App\Geography\ProviderFactory\PeliasAddress;
use App\Geography\Repository\AddressRepository;
use App\Image\Repository\IconRepository;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Geocoder\Model\Bounds;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The geo searcher service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoSearcher
{
    public const ICON_ADDRESS_ANY = 1;
    public const ICON_ADDRESS_PERSONAL = 2;
    public const ICON_COMMUNITY = 3;
    public const ICON_EVENT = 4;
    public const ICON_VENUE = 23;

    private $geocoder;
    private $geoTools;
    private $userRepository;
    private $addressRepository;
    private $relayPointRepository;
    private $iconRepository;
    private $security;
    private $iconPath;
    private $dataPath;
    private $eventRepository;
    private $defaultSigResultNumber;
    private $defaultSigReturnedResultNumber;
    private $defaultNamedResultNumber;
    private $defaultRelayPointResultNumber;
    private $defaultEventResultNumber;
    private $geoDataFixes;
    private $distanceOrder;
    private $sigPrioritizeCoordinates;
    private $sigPrioritizeRegion;
    private $sigShowVenues;

    private $_geocodeInput;
    private $_geocodeResults;
    private $_geocodeTemporaryResults = [];
    private $_geocodeRelaypointsResults = [];
    private $_geocodeEventpointsResults = [];

    private $_user;
    private $_userPrioritize;

    /**
     * Constructor.
     */
    public function __construct(
        PluginProvider $geocoder,
        GeoTools $geoTools,
        UserRepository $userRepository,
        AddressRepository $addressRepository,
        RelayPointRepository $relayPointRepository,
        EventRepository $eventRepository,
        IconRepository $iconRepository,
        Security $security,
        TranslatorInterface $translator,
        string $iconPath,
        string $dataPath,
        string $defaultSigResultNumber,
        int $defaultSigReturnedResultNumber,
        string $defaultNamedResultNumber,
        string $defaultRelayPointResultNumber,
        string $defaultEventResultNumber,
        array $geoDataFixes,
        bool $distanceOrder,
        array $sigPrioritizeCoordinates,
        string $sigPrioritizeRegion,
        bool $sigShowVenues
    ) {
        $this->geocoder = $geocoder;
        $this->geoTools = $geoTools;
        $this->userRepository = $userRepository;
        $this->addressRepository = $addressRepository;
        $this->relayPointRepository = $relayPointRepository;
        $this->iconRepository = $iconRepository;
        $this->security = $security;
        $this->translator = $translator;
        $this->iconPath = $iconPath;
        $this->dataPath = $dataPath;
        $this->eventRepository = $eventRepository;
        $this->defaultSigResultNumber = $defaultSigResultNumber;
        $this->defaultSigReturnedResultNumber = $defaultSigReturnedResultNumber;
        $this->defaultNamedResultNumber = $defaultNamedResultNumber;
        $this->defaultRelayPointResultNumber = $defaultRelayPointResultNumber;
        $this->defaultEventResultNumber = $defaultEventResultNumber;
        $this->geoDataFixes = $geoDataFixes;
        $this->distanceOrder = $distanceOrder;
        $this->sigPrioritizeCoordinates = $sigPrioritizeCoordinates;
        $this->sigPrioritizeRegion = $sigPrioritizeRegion;
        $this->sigShowVenues = $sigShowVenues;
    }

    /**
     * Returns an array of result addresses (named addresses, relaypoints, sig addresses...).
     *
     * @param string $input The string representing the user input
     *
     * @return array The results
     */
    public function geoCode(string $input)
    {
        // the result array will contain different addresses :
        // - named addresses (if the user is logged)
        // - relaypoints (with or without private relaypoints depending on if th user is logged)
        // - sig addresses
        // - other objects ? to be defined
        $this->_geocodeResults = [];
        $this->_geocodeTemporaryResults = [];
        $this->_geocodeRelaypointsResults = [];
        $this->_geocodeEventpointsResults = [];

        // First we handle the quote
        $this->_geocodeInput = str_replace("'", "''", $input);

        // we search if the user is a real user (not an app)
        $userPrioritize = null;
        $user = $this->security->getUser();
        $this->_user = $user;
        if ($user instanceof User) {
            // we search its home address
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $userPrioritize = [
                        'latitude' => $address->getLatitude(),
                        'longitude' => $address->getLongitude(),
                    ];

                    break;
                }
            }
        }
        $this->_userPrioritize = $userPrioritize;

        // Set the results by categories
        $this->setGeocodeNamedAddresses();
        $this->setGeocodeSigAddresses();
        $this->setGeocodeRelaypoints();
        $this->setGeocodeEventpoints();

        // Build the array to be returned
        $this->buidGeocodeResults();

        return $this->_geocodeResults;
    }

    /**
     * Returns an array of reversed geocoded addresses.
     *
     * @param float $lat The latitude
     * @param float $lon The longitude
     *
     * @return array The array of addresses found
     */
    public function reverseGeoCode(float $lat, float $lon)
    {
        $addresses = [];
        if ($geoResults = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($lat, $lon))) {
            foreach ($geoResults as $geoResult) {
                $address = new Address();
                $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_ANY)->getFileName());
                if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLatitude()) {
                    $address->setLatitude((string) $geoResult->getCoordinates()->getLatitude());
                }
                if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLongitude()) {
                    $address->setLongitude((string) $geoResult->getCoordinates()->getLongitude());
                }
                $address->setHouseNumber($geoResult->getStreetNumber());
                $address->setStreet($geoResult->getStreetName());
                $address->setStreetAddress($geoResult->getStreetName() ? trim(($geoResult->getStreetNumber() ? $geoResult->getStreetNumber() : '').' '.$geoResult->getStreetName()) : null);
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
                if ((method_exists($geoResult, 'getEstablishment')) && (null != $geoResult->getEstablishment())) {
                    $address->setVenue($geoResult->getEstablishment());
                }

                if ((method_exists($geoResult, 'getPointOfInterest')) && (null != $geoResult->getPointOfInterest())) {
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

    private function buidGeocodeResults()
    {
        $this->mergeNamedResultsIntoGeocodeResults($this->_geocodeTemporaryResults);

        $this->mergeNamedResultsIntoGeocodeResults($this->_geocodeRelaypointsResults);

        $this->mergeNamedResultsIntoGeocodeResults($this->_geocodeEventpointsResults);
    }

    private function mergeNamedResultsIntoGeocodeResults(array $data)
    {
        $this->_geocodeResults = array_merge($this->_geocodeResults, $data);
    }

    private function setGeocodeNamedAddresses()
    {
        if ($this->_user instanceof User) {
            $namedAddresses = $this->addressRepository->findByName($this->translator->trans($this->_geocodeInput), $this->_user->getId());
            if (count($namedAddresses) > 0) {
                $i = 0;
                foreach ($namedAddresses as $address) {
                    $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $this->_user));
                    $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_PERSONAL)->getFileName());
                    array_push($this->_geocodeTemporaryResults, $address);
                    ++$i;
                    if ($i >= $this->defaultNamedResultNumber) {
                        break;
                    }
                }
            }
        }
    }

    private function setGeocodeSigAddresses()
    {
        // The query always include SIG_GEOCODER_PRIORITIZE_COORDINATES (see services.yaml Georouter.query_data_plugin)
        // But some SIG use the Bound param for a viewbox/zone so we need to detect if it's only a point or a viewbox

        // Check the options (priozitize, viewbox, region...)
        $optionUserPrioritize = $optionBounds = $optionRegion = false;

        // If there is viewbox in .env SIG_GEOCODER_PRIORITIZE_COORDINATES
        if (
            isset($this->sigPrioritizeCoordinates['minLatitude'], $this->sigPrioritizeCoordinates['maxLatitude'], $this->sigPrioritizeCoordinates['minLongitude'], $this->sigPrioritizeCoordinates['maxLongitude'])
        ) {
            $optionBounds = true;
        }

        // Centroid on user's home address
        if (!is_null($this->_userPrioritize)) {
            $optionUserPrioritize = true;
        }

        // Specific region using ccTLD standard (https://en.wikipedia.org/wiki/CcTLD)
        $optionRegion = '' !== $this->sigPrioritizeRegion;

        // Considering the options, we build the request

        if ($optionUserPrioritize && !$optionBounds && !$optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withData('userPrioritize', $this->_userPrioritize)
            ;
        } elseif (!$optionUserPrioritize && $optionBounds && !$optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withBounds(new Bounds($this->sigPrioritizeCoordinates['minLatitude'], $this->sigPrioritizeCoordinates['minLongitude'], $this->sigPrioritizeCoordinates['maxLatitude'], $this->sigPrioritizeCoordinates['maxLongitude']))
            ;
        } elseif (!$optionUserPrioritize && !$optionBounds && $optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withData('region', $this->sigPrioritizeRegion)
            ;
        } elseif ($optionUserPrioritize && $optionBounds && !$optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withData('userPrioritize', $this->_userPrioritize)
                ->withBounds(new Bounds($this->sigPrioritizeCoordinates['minLatitude'], $this->sigPrioritizeCoordinates['minLongitude'], $this->sigPrioritizeCoordinates['maxLatitude'], $this->sigPrioritizeCoordinates['maxLongitude']))
            ;
        } elseif (!$optionUserPrioritize && $optionBounds && $optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withData('region', $this->sigPrioritizeRegion)
                ->withBounds(new Bounds($this->sigPrioritizeCoordinates['minLatitude'], $this->sigPrioritizeCoordinates['minLongitude'], $this->sigPrioritizeCoordinates['maxLatitude'], $this->sigPrioritizeCoordinates['maxLongitude']))
            ;
        } elseif ($optionUserPrioritize && $optionBounds && $optionRegion) {
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
                ->withData('userPrioritize', $this->_userPrioritize)
                ->withData('region', $this->sigPrioritizeRegion)
                ->withBounds(new Bounds($this->sigPrioritizeCoordinates['minLatitude'], $this->sigPrioritizeCoordinates['minLongitude'], $this->sigPrioritizeCoordinates['maxLatitude'], $this->sigPrioritizeCoordinates['maxLongitude']))
            ;
        } else {
            // Not specific option
            $query = GeocodeQuery::create($this->_geocodeInput)
                ->withLimit($this->defaultSigResultNumber)
            ;
        }

        $geoResults = $this->geocoder->geocodeQuery($query)->all();

        foreach ($geoResults as $geoResult) {
            /**
             * @var PeliasAddress $geoResult
             */

            // ?? todo : exclude all results that doesn't include any input word at all
            $address = new Address();
            // set address icon
            $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_ADDRESS_ANY)->getFileName());
            if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLatitude()) {
                $address->setLatitude((string) $geoResult->getCoordinates()->getLatitude());
            }
            if ($geoResult->getCoordinates() && $geoResult->getCoordinates()->getLongitude()) {
                $address->setLongitude((string) $geoResult->getCoordinates()->getLongitude());
            }
            $address->setHouseNumber($geoResult->getStreetNumber());
            $address->setStreet($geoResult->getStreetName());
            $address->setStreetAddress($geoResult->getStreetName() ? trim(($geoResult->getStreetNumber() ? $geoResult->getStreetNumber() : '').' '.$geoResult->getStreetName()) : null);
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
            if ((method_exists($geoResult, 'getEstablishment')) && (null != $geoResult->getEstablishment())) {
                $address->setVenue($geoResult->getEstablishment());
            }
            if ((method_exists($geoResult, 'getPointOfInterest')) && (null != $geoResult->getPointOfInterest())) {
                $address->setVenue($geoResult->getPointOfInterest());
            }

            $address->setProvidedBy($geoResult->getProvidedBy());

            if ($address->getVenue()) {
                if (!$this->sigShowVenues) {
                    continue;
                }
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

            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $this->_user));

            // We set the similarity (algorithm method)
            $address->setSimilarityWithSearch(levenshtein($this->_geocodeInput, $address->getAddressLocality()));

            // Before adding a new address we check if there is a similar already in the array
            // If so, we take the tinier layer index
            $addAddress = true;
            foreach ($this->_geocodeTemporaryResults as $address_key => $previous_address) {
                if (0 == count(array_diff($address->getDisplayLabel(), $previous_address->getDisplayLabel()))) {
                    if ($address->getLayer() < $previous_address->getLayer()) {
                        $this->_geocodeTemporaryResults[$address_key] = $address;
                    }

                    $addAddress = false;

                    break;
                }
            }

            if ($addAddress) {
                array_push($this->_geocodeTemporaryResults, $address);
            }

            usort($this->_geocodeTemporaryResults, function ($a, $b) {
                return $a->getSimilarityWithSearch() > $b->getSimilarityWithSearch();
            });
        }

        if ($this->distanceOrder) {
            usort($this->_geocodeTemporaryResults, function ($a, $b) {
                return $a->getDistance() > $b->getDistance();
            });
        }

        $this->_geocodeTemporaryResults = array_slice($this->_geocodeTemporaryResults, 0, $this->defaultSigReturnedResultNumber);

        return $this->_geocodeTemporaryResults;
    }

    private function setGeocodeRelaypoints()
    {
        $relayPoints = $this->relayPointRepository->findByNameAndStatus($this->_geocodeInput, RelayPoint::STATUS_ACTIVE);

        // exclude the private relay points
        $i = 0;
        foreach ($relayPoints as $relayPoint) {
            $exclude = false;
            if ($relayPoint->getCommunity() && $relayPoint->isPrivate()) {
                $exclude = true;
                if ($this->_user) {
                    // todo : maybe find a quicker way than a foreach :)
                    foreach ($relayPoint->getCommunity()->getCommunityUsers() as $communityUser) {
                        if ($communityUser->getUser()->getId() == $this->_user->getId() && $communityUser->getStatus() == (CommunityUser::STATUS_ACCEPTED_AS_MEMBER or CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)) {
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
                $relayPointType = $relayPoint->getRelayPointType();

                if (!is_null($relayPointType) && is_null($relayPointType->getIcon())) {
                    $relayPointType->setIcon($this->iconRepository->find(1));
                }

                if (!is_null($relayPointType) && !is_null($relayPointType->getIcon())) {
                    if ($relayPointType->getIcon()->getPrivateIconLinked()) {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPointType->getIcon()->getPrivateIconLinked()->getFileName());
                    } else {
                        $address->setIcon($this->dataPath.$this->iconPath.$relayPointType->getIcon()->getFileName());
                    }
                }
                $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $this->_user));
                array_push($this->_geocodeRelaypointsResults, $address);
                ++$i;
                if ($i >= $this->defaultRelayPointResultNumber) {
                    break;
                }
            }
        }
    }

    private function setGeocodeEventpoints()
    {
        $events = $this->eventRepository->findByNameAndStatus($this->_geocodeInput, Event::STATUS_ACTIVE);
        // exclude the private relay points
        $i = 0;
        foreach ($events as $event) {
            $address = $event->getAddress();
            $address->setEvent($event);
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $this->_user));
            $address->setIcon($this->dataPath.$this->iconPath.$this->iconRepository->find(self::ICON_EVENT)->getFileName());
            array_push($this->_geocodeEventpointsResults, $address);
            ++$i;
            if ($i >= $this->defaultEventResultNumber) {
                break;
            }
        }
    }

    /**
     * Fix potential wrong addresses.
     *
     * @param string  $id      The id of the source data
     * @param Address $address The address to fix
     *
     * @return Address The address fixed
     */
    private function fixAddress(string $id, Address $address)
    {
        // we search in the fixes if there's one corresponding to the id
        if (array_key_exists($id, $this->geoDataFixes)) {
            foreach ($this->geoDataFixes[$id] as $property => $value) {
                if (method_exists($address, 'set'.ucfirst($property))) {
                    $method = 'set'.ucfirst($property);
                    $address->{$method}($value);
                }
            }
        }

        return $address;
    }

    /**
     * Get layer id by layer string.
     *
     * @param string $layer The string layer
     *
     * @return null|int The int layer or null
     */
    private function getLayer(string $layer): ?int
    {
        switch ($layer) {
            case 'address':
                return Address::LAYER_ADDRESS;

            case 'locality':
                return Address::LAYER_LOCALITY;

            case 'localadmin':
                return Address::LAYER_LOCALADMIN;

            default:
                return null;
        }
    }
}
