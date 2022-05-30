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

namespace App\Geography\Service\Geocoder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;

class MobicoopGeocoder implements Geocoder
{
    private const MAX_RESULTS_BY_TYPE = 5;
    private const MAX_RESULTS_BY_GEOCODER = 5;
    private const SANITIZE = 1;
    private const CONSOLIDATE = 1;
    private const PROXIMITY = 5;
    private const MIN_CONFIDENCE = 50;

    private const TIME_OUT = 5;

    private $client;
    private $params;

    public function __construct(string $uri)
    {
        $this->client = new Client([
            'base_uri' => $uri,
        ]);
        $this->params = [
            'max_results_per_type' => self::MAX_RESULTS_BY_TYPE,
            'max_results_per_geocoder' => self::MAX_RESULTS_BY_GEOCODER,
            'sanitize' => self::SANITIZE,
            'consolidate' => self::CONSOLIDATE,
            'proximity' => self::PROXIMITY,
            'min_confidence' => self::MIN_CONFIDENCE / 100,
        ];
    }

    public function setPrioritizeCentroid(float $lon, float $lat): void
    {
        $this->params['lon'] = $lon;
        $this->params['lat'] = $lat;
    }

    public function setPrioritizeBox(float $minLon, float $minLat, float $maxLon, float $maxLat): void
    {
        $this->params['min_lon'] = $minLon;
        $this->params['min_lat'] = $minLat;
        $this->params['max_lon'] = $maxLon;
        $this->params['max_lat'] = $maxLat;
    }

    public function setPrioritizeRegion(string $region): void
    {
        $this->params['prioritization_region'] = $region;
    }

    public function setRestrictCountry(string $country): void
    {
        $this->params['country'] = $country;
    }

    public function setLang(string $lang): void
    {
        $this->params['lang'] = $lang;
    }

    public function geocode(string $search): array
    {
        $this->params['search'] = $search;

        try {
            $clientResponse = $this->client->get('?'.http_build_query($this->params), ['connect_timeout' => self::TIME_OUT]);

            return json_decode($clientResponse->getBody(), true);
        } catch (TransferException $exception) {
            return [];
        } catch (ConnectException $exception) {
            return [];
        }
    }

    public function reverse(float $lon, float $lat): array
    {
        $this->params['lon'] = $lon;
        $this->params['lat'] = $lon;

        try {
            $clientResponse = $this->client->get('/reverse?'.http_build_query($this->params), ['connect_timeout' => self::TIME_OUT]);

            return json_decode($clientResponse->getBody(), true);
        } catch (TransferException $exception) {
            return [];
        } catch (ConnectException $exception) {
            return [];
        }
    }
}
