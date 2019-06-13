<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
use App\PublicTransport\Entity\PTTripPoint;

/**
 * Public transport DataProvider.
 *
 * To add a provider :
 * - write the custom Provider class in src/DataProvider/Entity/
 * - complete the PROVIDERS array with the new provider
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTDataProvider
{
    const PROVIDERS = [
        "cityway" => CitywayProvider::class
    ];
    
    const DATETIME_FORMAT = \DateTime::RFC3339;
    
    const DATETYPE_DEPARTURE = "departure";
    const DATETYPE_ARRIVAL = "arrival";
    
    const ALGORITHM_FASTEST = "fastest";
    const ALGORITHM_SHORTEST = "shortest";
    const ALGORITHM_MINCHANGES = "minchanges";
    
    /**
     * Get journeys from an external Public Transport data provider.
     *
     * @param string $provider                  The name of the provider
     * @param string $apikey                    The API Key for the provider
     * @param string $origin_latitude           The latitude of the origin point
     * @param string $origin_longitude          The longitude of the origin point
     * @param string $destination_latitude      The latitude of the destination point
     * @param string $destination_longitude     The longitude of the destination point
     * @param \Datetime $date                   The datetime of the trip
     * @param string $dateType                  The date type of the trip (departure or arrival)
     * @param string $algorithm                 The algorithm used for the trip calculation (fastest, shortest or minchanges)
     * @param string $modes                     The trip modes accepted (PT, BIKE, CAR, PT+BIKE, PT+CAR)
     * @return NULL|array                       The journeys found or null if no journey is found
     */
    public function getJourneys(
        string $provider,
        string $apikey,
        string $origin_latitude,
        string $origin_longitude,
        string $destination_latitude,
        string $destination_longitude,
        \Datetime $date,
        string $dateType,
        string $algorithm,
        string $modes
            ): ?array {
        if (!array_key_exists($provider, self::PROVIDERS)) {
            return null;
        }
        $providerClass = self::PROVIDERS[$provider];
        $providerInstance = new $providerClass();
        return call_user_func_array([$providerInstance,"getCollection"], [PTJourney::class,$apikey,[
                "origin_latitude" => $origin_latitude,
                "origin_longitude" => $origin_longitude,
                "destination_latitude" => $destination_latitude,
                "destination_longitude" => $destination_longitude,
                "date" => $date,
                "dateType" => $dateType,
                "algorithm" => $algorithm,
                "modes" => $modes
        ]]);
    }

    /**
     * Get trip points from an external Public Transport data provider.
     *
     * @param string $provider                  The name of the provider
     * @param float $latitude           The latitude of the origin point
     * @param float $longitude          The longitude of the origin point
     * @param int $perimeter                     Radius of the perimeter (in meters)
     * @param string $transportModes                     The trip modes accepted (PT, BIKE, CAR, PT+BIKE, PT+CAR)
     * @return NULL|array                       The journeys found or null if no journey is found
     */
    public function getTripPoints(
        string $provider,
        float $latitude,
        float $longitude,
        int $perimeter,
        string $transportModes
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
            "transportModes" => $transportModes
        ]]);
    }
}
