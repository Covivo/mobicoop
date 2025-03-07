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
 */

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLineStop;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTTripPoint;

/**
 * Public transport management service.
 */
class PublicTransportManager
{
    private $dataProvider;

    /**
     * Constructor.
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(PTJourney::class, 'public_transport/journeys');
    }

    /**
     * Get public transport journeys.
     *
     * @param float  $origin_latitude       The latitude of the origin point
     * @param float  $origin_longitude      The longitude of the origin point
     * @param float  $destination_latitude  The latitude of the destination point
     * @param float  $destination_longitude The longitude of the destination point
     * @param string $date                  The date of the journey
     * @param string $dateType              (optional) Date criteria like "arrival" or "departure"
     * @param string $modes                 (optional) Mode criteria
     *
     * @return null|Hydra the journeys found (as an Hydra object) or null if not found
     */
    public function getJourneys(
        float $origin_latitude,
        float $origin_longitude,
        float $destination_latitude,
        float $destination_longitude,
        string $date,
        ?string $dateType = null,
        ?string $modes = null
    ) {
        $response = $this->dataProvider->getCollection([
            'origin_latitude' => $origin_latitude,
            'origin_longitude' => $origin_longitude,
            'destination_latitude' => $destination_latitude,
            'destination_longitude' => $destination_longitude,
            'date' => $date,
            'dateType' => $dateType,
            'modes' => $modes,
        ]);

        return $response->getValue();
    }

    /**
     * Get Trip Points near a given couple of Latitude and Longitude.
     *
     * @param float  $latitude       The latitude near de trip points
     * @param float  $longitude      The longitude near de trip points
     * @param int    $perimeter      The perimeter you want to search fortrip points
     * @param string $transportModes The transport modes you want to search fortrip points
     * @param string $keywords       Trip points whose name contains these keywords (can't combine with lat/lon)
     *
     * @return null|array|object
     */
    public function getTripPoints(
        float $latitude,
        float $longitude,
        int $perimeter,
        string $transportModes,
        string $keywords = ''
    ) {
        $this->dataProvider->setClass(PTTripPoint::class, 'public_transport/trippoints');

        $response = $this->dataProvider->getCollection([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'perimeter' => $perimeter,
            'transportModes' => $transportModes,
            'keywords' => $keywords,
        ]);

        return $response->getValue();
    }

    /**
     * Get line Stops for a logicalid.
     *
     * @param int $logicalId The logicalId to retreive linestops
     *
     * @return null|array|object
     */
    public function getLineStops(
        string $provider,
        int $logicalId,
        string $transportModes = ''
    ) {
        $this->dataProvider->setClass(PTLineStop::class, 'public_transport/linestops');

        $response = $this->dataProvider->getCollection([
            'logicalId' => $logicalId,
        ]);

        return $response->getValue();
    }

    public function checkThreshold(float $origin_latitude, float $origin_longitude)
    {
        $this->dataProvider->setClass(PTJourney::class, 'public_transport/checkThreshold');
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getCollection([
            'origin_latitude' => $origin_latitude,
            'origin_longitude' => $origin_longitude,
        ]);

        return $response->getValue();
    }
}
