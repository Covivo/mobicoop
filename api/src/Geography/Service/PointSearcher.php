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

use App\Event\Repository\EventRepository;
use App\Geography\Repository\AddressRepository;
use App\Geography\Service\Geocoder\GeocoderFactory;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\Point\EventPointProvider;
use App\Geography\Service\Point\GeocoderPointProvider;
use App\Geography\Service\Point\RelayPointPointProvider;
use App\Geography\Service\Point\UserPointProvider;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Point searcher.
 */
class PointSearcher
{
    private $providers;
    private $reverseProviders;
    private $tokenStorage;
    private $addressAdapter;
    private $geoTools;

    private $_fixer;
    private $_fixerEnabled;

    public function __construct(
        RelayPointRepository $relayPointRepository,
        EventRepository $eventRepository,
        AddressRepository $addressRepository,
        TranslatorInterface $translator,
        Security $security,
        TokenStorageInterface $tokenStorage,
        GeocoderFactory $geocoderFactory,
        AddressAdapter $addressAdapter,
        int $maxRelayPointResults,
        int $maxEventResults,
        int $maxUserResults,
        ?array $prioritizeCentroid = null,
        ?array $prioritizeBox = null,
        ?string $prioritizeRegion = null,
        ?string $restrictCountry = null,
        array $exclusionTypes = [],
        array $relayPointParams,
        array $fixerData,
        bool $fixerEnabled,
        GeoTools $geoTools
    ) {
        $geocoder = $geocoderFactory->getGeocoder();
        $this->tokenStorage = $tokenStorage;
        $this->_fixerEnabled = $fixerEnabled;
        $this->_fixer = new PointGeoFixer($fixerData);
        $user = $security->getUser();
        $userPointProvider = new UserPointProvider($addressRepository, $translator);
        if ($prioritizeCentroid) {
            $geocoder->setPrioritizeCentroid(
                $prioritizeCentroid['lon'],
                $prioritizeCentroid['lat']
            );
        }
        if ($prioritizeBox) {
            $geocoder->setPrioritizeBox(
                $prioritizeBox['minLon'],
                $prioritizeBox['minLat'],
                $prioritizeBox['maxLon'],
                $prioritizeBox['maxLat']
            );
        }
        if ($prioritizeRegion) {
            $geocoder->setPrioritizeRegion($prioritizeRegion);
        }
        if ($restrictCountry) {
            $geocoder->setRestrictCountry($restrictCountry);
        }
        if ($user instanceof User) {
            $userPointProvider->setUser($user);
            $userPointProvider->setMaxResults($maxUserResults);

            /**
             * @var null|User $user
             */
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $geocoder->setPrioritizeCentroid(
                        (float) $address->getLongitude(),
                        (float) $address->getLatitude()
                    );

                    break;
                }
            }
        }
        $geocoderPointProvider = new GeocoderPointProvider($geocoder);
        $geocoderPointProvider->setExclusionTypes($exclusionTypes);

        $relayPointPointProvider = new RelayPointPointProvider($relayPointRepository);
        $relayPointPointProvider->setMaxResults($maxRelayPointResults);
        $relayPointPointProvider->setParams($relayPointParams);

        $eventPointProvider = new EventPointProvider($eventRepository);
        $eventPointProvider->setMaxResults($maxEventResults);

        $this->providers = [
            $geocoderPointProvider,
            $relayPointPointProvider,
            $eventPointProvider,
        ];

        if ($user instanceof User) {
            $this->providers[] = $userPointProvider;
        }

        $this->reverseProviders = [$geocoderPointProvider];
        $this->addressAdapter = $addressAdapter;
        $this->geoTools = $geoTools;
    }

    public function geocode(string $search): array
    {
        $user = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()->getUser();
        }
        $points = [];
        foreach ($this->providers as $provider) {
            $points = array_merge($points, $provider->search(str_replace(['"', "'"], ' ', $search), $user));
        }

        return $this->_fixerEnabled ? $this->_fixer->fix($points) : $points;
    }

    public function reverse(float $lon, float $lat): array
    {
        $points = [];
        foreach ($this->reverseProviders as $provider) {
            $points = array_merge($points, $provider->reverse($lon, $lat));
        }

        return $points;
    }

    public function reverseAddressFormated(float $lon, float $lat): array
    {
        $points = $this->reverse($lon, $lat);

        foreach ($points as $point) {
            $address = $this->addressAdapter->pointToAddress($point);
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address));
            $addresses[] = $address;
        }

        return $addresses;
    }
}
