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

namespace App\Geography\Service\Geocoder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;

class PeliasGeocoder implements Geocoder
{
    private const SANITIZE = 1;
    private const CONSOLIDATE = 1;
    private const PROXIMITY = 5;
    private const MIN_CONFIDENCE = 50;

    private const TIME_OUT = 5;

    private $client;
    private $params;
    // private $realparams;

    public function __construct(string $uri, int $maxResultsByType, int $maxResultsByGeocoder)
    {
        $this->client = new Client([
            'base_uri' => $uri,
        ]);
        $this->params = [
            // 'max_results_per_type' => $maxResultsByType,
            'size' => $maxResultsByGeocoder,
            // 'sanitize' => self::SANITIZE,
            // 'consolidate' => self::CONSOLIDATE,
            // 'proximity' => self::PROXIMITY,
            // 'min_confidence' => self::MIN_CONFIDENCE / 100,
        ];
    }

    public function setPrioritizeCentroid(float $lon, float $lat): void
    {
        $this->params['focus.point.lon'] = $lon;
        $this->params['focus.point.lat'] = $lat;
    }

    public function setPrioritizeBox(float $minLon, float $minLat, float $maxLon, float $maxLat): void
    {
        $this->params['boundary.rect.min_lon'] = $minLon;
        $this->params['boundary.rect.min_lat'] = $minLat;
        $this->params['boundary.rect.max_lon'] = $maxLon;
        $this->params['boundary.rect.max_lat'] = $maxLat;
    }

    public function setPrioritizeRegion(string $region): void
    {
        $this->params['boundary.country'] = $region;
    }

    public function setRestrictCountry(string $country): void
    {
        $this->params['boundary.country'] = $country;
    }

    public function setLang(string $lang): void
    {
        $this->params['lang'] = $lang;
    }

    public function geocode(string $search): array
    {
        $this->params['text'] = $search;
        $this->params['lang'] = "fr";
        try {
            $clientResponseAutocomplete = $this->client->get('v1/autocomplete?'.http_build_query($this->params), ['connect_timeout' => self::TIME_OUT]);
            $arrayPelias = json_decode((string) $clientResponseAutocomplete->getBody(), true);
            if ($arrayPelias['features'] == []) {
                $clientResponseSearch = $this->client->get('v1/search?'.http_build_query($this->params), ['connect_timeout' => self::TIME_OUT]);
                $arrayPelias = json_decode((string) $clientResponseSearch->getBody(), true);
            }
            return $this->peliasSerialize($arrayPelias);
        } catch (TransferException $exception) {
            return [];
        } catch (ConnectException $exception) {
            return [];
        }
    }

    public function reverse(float $lon, float $lat): array
    {
        $this->params['point.lon'] = $lon;
        $this->params['point.lat'] = $lat;

        try {
            $clientResponse = $this->client->get('v1/reverse?'.http_build_query($this->params), ['connect_timeout' => self::TIME_OUT]);

            return $this->peliasSerialize(json_decode((string) $clientResponse->getBody(), true));
        } catch (TransferException $exception) {
            return [];
        } catch (ConnectException $exception) {
            return [];
        }
    }

    private function peliasSerialize(array $data):array
    {
        $result = [];
    
        foreach ($data['features'] as $feature) {
            $properties = $feature['properties'];
            $geometry = $feature['geometry'];
            $adresstype = ($properties['layer'] == 'address') ? 'housenumber' : $properties['layer'];
            $result[] = [
                'country' => $properties['country'] ?? null,
                'country_code' => $properties['country_code'] ?? null,
                'distance' => null, // Distance n'est pas dans la réponse, à calculer si nécessaire
                'house_number' => $properties['housenumber'] ?? null,
                'id' => $properties['id'] ?? null,
                'lat' => $geometry['coordinates'][1] ?? null,
                'locality' => $properties['locality'] ?? null,
                'locality_code' => $properties['locality_gid'] ?? null,
                'lon' => $geometry['coordinates'][0] ?? null,
                'macro_region' => $properties['macroregion'] ?? null,
                'name' => $properties['name'] ?? null,
                'population' => null, // Population n'est pas dans la réponse, à ajouter si disponible
                'postal_code' => $properties['postalcode'] ?? null,
                'region' => $properties['region'] ?? null,
                'region_code' => isset($properties['postalcode']) ? mb_substr($properties['postalcode'], 0, 2) : null,
                'street_name' => $properties['street'] ?? null,
                'type' => $adresstype ?? null,
                'provider' => $data['geocoding']['engine']['name'] ?? null
            ];
        }
    
        return $result;
    }
}
