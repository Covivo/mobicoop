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

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Service;

use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLineStop;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTTripPoint;

/**
 * Public transport management service.
 */
class PublicTransportManager
{
    const PROVIDER_CITYWAY = "cityway";
    
    const DATETYPE_DEPARTURE = "departure";
    const DATETYPE_ARRIVAL = "arrival";
    
    private $dataProvider;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(PTJourney::class, "public_transport/journeys");
    }
    
    /**
     * Get public transport journeys
     *
     * @param string    $provider                   The name of the provider
     * @param string    $apikey                     The apikey
     * @param float     $origin_latitude            The latitude of the origin point
     * @param float     $origin_longitude           The longitude of the origin point
     * @param float     $destination_latitude       The latitude of the destination point
     * @param float     $destination_longitude      The longitude of the destination point
     * @param string    $date                       The date of the journey
     * @param string    $dateType                   The date type (departure or arrival)
     * @param string    $algorithm                  The algorithm used for the trip calculation (fastest, shortest or minchanges)
     * @param string    $modes                      The trip modes accepted (PT, BIKE, CAR, PT+BIKE, PT+CAR)
     * @return Hydra|null The journeys found (as an Hydra object) or null if not found.
     */
    public function getJourneys(
        string $provider,
        string $apikey,
        float $origin_latitude,
        float $origin_longitude,
        float $destination_latitude,
        float $destination_longitude,
        string $date,
        string $dateType,
        string $algorithm,
        string $modes
        ) {
        $response = $this->dataProvider->getCollection([
            'provider'              => $provider,
            'apikey'                => $apikey,
            'origin_latitude'       => $origin_latitude,
            'origin_longitude'      => $origin_longitude,
            'destination_latitude'  => $destination_latitude,
            'destination_longitude' => $destination_longitude,
            'date'                  => $date,
            'dateType'              => $dateType,
            'algorithm'             => $algorithm,
            'modes'                 => $modes
        ]);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get Trip Points near a given couple of Latitude and Longitude
     *
     * @param string $provider          The name of the provider
     * @param float $latitude           The latitude near de trip points
     * @param float $longitude          The longitude near de trip points
     * @param int $perimeter            The perimeter you want to search fortrip points
     * @param string $transportModes    The transport modes you want to search fortrip points
     * @param string $keywords          Trip points whose name contains these keywords (can't combine with lat/lon)
     * @return array|object|null
     */
    public function getTripPoints(
        string $provider,
        float $latitude,
        float $longitude,
        int $perimeter,
        string $transportModes,
        string $keywords
    ) {
        $this->dataProvider->setClass(PTTripPoint::class, "public_transport/trippoints");

        $response = $this->dataProvider->getCollection([
            'provider'       => $provider,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'perimeter'      => $perimeter,
            'transportModes'      => $transportModes,
            'keywords'      => $keywords
        ]);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }


    /**
     * Get line Stops for a logicalid
     *
     * @param string $provider          The name of the provider
     * @param int $logicalId            The logicalId to retreive linestops
     * @param string $transportModes    The transport modes to search
     * @return array|object|null
     */
    public function getLineStops(
        string $provider,
        int $logicalId,
        string $transportModes=""
    ) {
        $this->dataProvider->setClass(PTLineStop::class, "public_transport/linestops");

        $response = $this->dataProvider->getCollection([
            'provider'       => $provider,
            'logicalId'       => $logicalId,
            'transportModes'       => $transportModes
        ]);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
