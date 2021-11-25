<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\PublicTransport\Service;

use App\PublicTransport\Entity\PTJourney;
use App\DataProvider\Entity\CitywayProvider;
use App\DataProvider\Entity\ConduentPTProvider;
use App\DataProvider\Entity\NavitiaProvider;
use App\Geography\Repository\TerritoryRepository;
use App\Geography\Service\GeoTools;
use App\PublicTransport\Entity\PTLineStop;
use App\PublicTransport\Entity\PTTripPoint;

/**
 * Public transport DataProvider.
 *
 * To add a provider :
 * - write the custom Provider class in src/DataProvider/Entity/
 * - complete the PROVIDERS array with the new provider
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTDataProvider
{
    const PROVIDERS = [
        "cityway" => CitywayProvider::class,
        "conduentPT" => ConduentPTProvider::class,
        "navitia" => NavitiaProvider::class
    ];
    
    const DATETIME_FORMAT = \DateTime::RFC3339;
    
    const DATETYPE_DEPARTURE = "departure";
    const DATETYPE_ARRIVAL = "arrival";
    
    const ALGORITHM_FASTEST = "fastest";
    const ALGORITHM_SHORTEST = "shortest";
    const ALGORITHM_MINCHANGES = "minchanges";
    
    private $geoTools;
    private $PTProviders;
    private $territoryRepository;

    public function __construct(GeoTools $geoTools, TerritoryRepository $territoryRepository, array $params)
    {
        $this->geoTools = $geoTools;
        $this->territoryRepository = $territoryRepository;
        $this->PTProviders = $params['ptProviders'];
    }
    
    /**
     * Get journeys from an external Public Transport data provider.
     *
     * @param string $provider                  The name of the provider (obsolete : not used anymore)
     * @param string $origin_latitude           The latitude of the origin point
     * @param string $origin_longitude          The longitude of the origin point
     * @param string $destination_latitude      The latitude of the destination point
     * @param string $destination_longitude     The longitude of the destination point
     * @param \Datetime $date                   The datetime of the trip
     * @param string    $dateCriteria           (optional) Date criteria like "arrival" or "departure"
     * @param string    $mode                   (optional) Mode criteria
     * @return NULL|array                       The journeys found or null if no journey is found
     */
    public function getJourneys(
        ?string $provider,
        string $origin_latitude,
        string $origin_longitude,
        string $destination_latitude,
        string $destination_longitude,
        \Datetime $date,
        string $dateType = null,
        string $modes = null
    ): ?array {
        $providerUri = null;

        $territoryId = "default";
        if (count($this->PTProviders)>1) {
            // If there is a territory, we look for the right provider. If there is no, we take the default.
            // Get the territory of the request
            $territories = $this->territoryRepository->findPointTerritories($origin_latitude, $origin_longitude);
            foreach ($territories as $territory) {
                // If the territoryId is in the providers.json for PT, we use this one
                if (isset($this->PTProviders[$territory['id']])) {
                    $territoryId = $territory['id'];
                    break;
                }
            }
        }

        // Values
        $provider = $this->PTProviders[$territoryId]['dataprovider'];
        // Authorized Providers
        if (!array_key_exists($provider, self::PROVIDERS)) {
            // echo "Unauthorized Providers";die;
            return null;
        }
        $providerUri = $this->PTProviders[$territoryId]['url'];
        $apikey = $this->PTProviders[$territoryId]['apikey'];
        $username = $this->PTProviders[$territoryId]['username'];
        $customParams = $this->PTProviders[$territoryId]['params'];



        $providerClass = self::PROVIDERS[$provider];
        $providerInstance = new $providerClass($providerUri);


        $params = [
            "origin_latitude" => $origin_latitude,
            "origin_longitude" => $origin_longitude,
            "destination_latitude" => $destination_latitude,
            "destination_longitude" => $destination_longitude,
            "date" => $date,
            "username" => $username
        ];

        // $mode and $dateCriteria are forced if they're send in parameters
        if (!is_null($dateType)) {
            $params['dateType'] = $dateType;
        }
        if (!is_null($modes)) {
            $params['modes'] = $modes;
        }

        foreach ($customParams as $key => $value) {
            // We don't override previously set parameters
            if (!isset($params[$key])) {
                $params[$key] = $value;
            }
        }

        $journeys = call_user_func_array([$providerInstance,"getCollection"], [PTJourney::class,$apikey,$params]);

        // Set the display label of the departure and arrival
        foreach ($journeys as $journey) {
            /**
             * @var PTJourney $journey
             */
            $departureAddress = $journey->getPTDeparture()->getAddress();
            $departureAddress->setDisplayLabel($this->geoTools->getDisplayLabel($departureAddress));
            $arrivalAddress = $journey->getPTArrival()->getAddress();
            $arrivalAddress->setDisplayLabel($this->geoTools->getDisplayLabel($arrivalAddress));

            foreach ($journey->getPTLegs() as $leg) {
                $departureAddress = $leg->getPTDeparture()->getAddress();
                $departureAddress->setDisplayLabel($this->geoTools->getDisplayLabel($departureAddress));
                $arrivalAddress = $leg->getPTArrival()->getAddress();
                $arrivalAddress->setDisplayLabel($this->geoTools->getDisplayLabel($arrivalAddress));
            }

            $journey->setProvider($provider);
        }

        return $journeys;
    }

    /**
     * Get trip points from an external Public Transport data provider.
     *
     * @param string $provider                  The name of the provider
     * @param float $latitude                   The latitude of the origin point
     * @param float $longitude                  The longitude of the origin point
     * @param int $perimeter                    Radius of the perimeter (in meters)
     * @param string $transportModes            The trip modes accepted (PT, BIKE, CAR, PT+BIKE, PT+CAR)
     * @param string $keywords                  Keywords to search in trip point name
     * @return NULL|array                       The trip points or null if no trip points is found
     */
    public function getTripPoints(
        string $provider,
        float $latitude,
        float $longitude,
        int $perimeter,
        string $transportModes,
        string $keywords
    ): ?array {
        if (!array_key_exists($provider, self::PROVIDERS)) {
            return null;
        }
        $providerClass = self::PROVIDERS[$provider];
        $providerInstance = new $providerClass();
        return call_user_func_array([$providerInstance,"getCollection"], [PTTripPoint::class,"",[
            "latitude" => $latitude,
            "longitude" => $longitude,
            "perimeter" => $perimeter,
            "transportModes" => $transportModes,
            "keywords" => $keywords
        ]]);
    }

    /**
     * Get line stop from an external Public Transport data provider.
     *
     * @param string $provider                  The name of the provider
     * @param int $logicalId                 The logicalId of the line stop
     * @param string $transportModes|null         The transport modes to search
     * @return NULL|array                       The line stop found or null if no line stop is found
     */
    public function getLineStop(
        string $provider,
        int $logicalId,
        string $transportModes = ""
    ): ?array {
        if (!array_key_exists($provider, self::PROVIDERS)) {
            return null;
        }
        $providerClass = self::PROVIDERS[$provider];
        $providerInstance = new $providerClass();
        return call_user_func_array([$providerInstance,"getCollection"], [PTLineStop::class,"",[
            "provider" => $provider,
            "logicalId" => $logicalId,
            "transportModes" => $transportModes
        ]]);
    }
}
