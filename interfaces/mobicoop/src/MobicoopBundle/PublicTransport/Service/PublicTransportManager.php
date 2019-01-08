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

/**
 * Public transport management service.
 */
class PublicTransportManager
{
    const PROVIDER_CITYWAY = "cityway";
    
    const DATETYPE_DEPARTURE = "departure";
    const DATETYPE_ARRIVAL = "arrival";
    
    private $dataProvider;
    
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
        string $dateType
        ) {
        $response = $this->dataProvider->getCollection([
            'provider'              => $provider,
            'apikey'                => $apikey,
            'origin_latitude'       => $origin_latitude,
            'origin_longitude'      => $origin_longitude,
            'destination_latitude'  => $destination_latitude,
            'destination_longitude' => $destination_longitude,
            'date'                  => $date,
            'dateType'              => $dateType
        ]);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
