<?php

declare(strict_types=1);

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

namespace App\Geography\ProviderFactory;

use Geocoder\Collection;
use App\Geography\ProviderFactory\PeliasAddress;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Sylvain Briat
 */
final class PeliasAutocomplete extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'autocomplete?text=%s&size=%d&lang=%s&layers=locality,localAdmin';

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_PRIORITIZATION = '&focus.point.lat=%f&focus.point.lon=%f';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'reverse?point.lat=%f&point.lon=%f&size=%d&lang=%s';

    /**
     * @var string
     */
    private $uri;

    // minimum confidence to consider a result as pertinent
    const MIN_CONFIDENCE = 0.85;

    /**
     * @param HttpClient $client an HTTP adapter
     * @param string     $uri the api uri
     */
    public function __construct(HttpClient $client, string $uri=null)
    {
        $this->uri = $uri;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        $url = sprintf($this->uri.self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $query->getLocale());
        if (!is_null($query->getData('userPrioritize'))) {
            $userPrioritize = $query->getData('userPrioritize');
            $url .= sprintf(self::GEOCODE_ENDPOINT_PRIORITIZATION, $userPrioritize['latitude'], $userPrioritize['longitude']);
        } elseif (!is_null($query->getData('latitude')) && !is_null($query->getData('longitude'))) {
            $url .= sprintf(self::GEOCODE_ENDPOINT_PRIORITIZATION, $query->getData('latitude'), $query->getData('longitude'));
        }
        return $this->executeQuery($url);
    }
    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf($this->uri.self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLimit(), $query->getLocale());
        return $this->executeQuery($url);
    }
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'pelias_autocomplete';
    }
    /**
     * @param $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);
        if (!isset($json['type']) || 'FeatureCollection' !== $json['type'] || !isset($json['features']) || 0 === count($json['features'])) {
            return new AddressCollection([]);
        }
        $locations = $json['features'];
        if (empty($locations)) {
            return new AddressCollection([]);
        }
        $results = [];
        foreach ($locations as $location) {
            $props = $location['properties'];

            // we check the confidence and match type from pelias result properties
            // if ($props['match_type'] == 'fallback' && $props['confidence'] < self::MIN_CONFIDENCE) {
            //     continue;
            // }

            // we check if the search has an id
            // we search first the locality id, then other ids
            $id = null;
            if (isset($props['locality_gid'])) {
                $id = preg_replace('/[^0-9]/', '', $props['locality_gid']);
            } elseif (isset($props['id'])) {
                $id = $props['id'];
            } // todo : complete with other ids if needed

            // we check if there's a layer provided
            $layer = null;
            if (isset($props['layer'])) {
                $layer = $props['layer'];
            }

            // we check if the search is a venue
            $venue = null;
            if ($layer == "venue") {
                $venue = $props['name'];
            }

            // we check if there's a distance provided
            $distance = null;
            if (isset($props['distance'])) {
                $distance = $props['distance'];
            }
            
            $bounds = [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ];
            if (isset($location['bbox'])) {
                $bounds = [
                    'south' => $location['bbox'][3],
                    'west' => $location['bbox'][2],
                    'north' => $location['bbox'][1],
                    'east' => $location['bbox'][0],
                ];
            }
            
            $adminLevels = [];
            foreach (['localadmin', 'county', 'macrocounty', 'region', 'macroregion'] as $i => $component) {
                if (isset($props[$component])) {
                    $adminLevels[] = ['name' => $props[$component], 'level' => $i + 1];
                }
            }
            // special treatment for dependency => replaces macroregion
            if (isset($props['dependency'])) {
                $adminLevels[] = ['name' => $props['dependency'], 'level' => 5];
            }
            $result = PeliasAddress::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $location['geometry']['coordinates'][1],
                'longitude' => $location['geometry']['coordinates'][0],
                'bounds' => $bounds,
                'streetNumber' => isset($props['housenumber']) ? $props['housenumber'] : null,
                'streetName' => isset($props['street']) ? $props['street'] : null,
                'subLocality' => isset($props['neighbourhood']) ? $props['neighbourhood'] : null,
                'locality' => (isset($props['locality']) && !is_null($props['locality'])) ? $props['locality'] : (isset($props['localadmin']) ? $props['localadmin'] : null),
                'postalCode' => isset($props['postalcode']) ? $props['postalcode'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($props['country']) ? $props['country'] : null,
                'countryCode' => isset($props['country_a']) ? strtoupper($props['country_a']) : null
            ]);
        
            $result->setId($id);
            $result->setVenue($venue);
            $result->setDistance($distance);
            $result->setLayer($layer);
            $results[] = $result;
        }
        return new AddressCollection($results);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = ['city', 'town', 'village', 'hamlet'];
        return $this->guessBestComponent($components, $localityKeys);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];
        return $this->guessBestComponent($components, $streetNameKeys);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'city_district'];
        return $this->guessBestComponent($components, $subLocalityKeys);
    }
    /**
     * @param array $components
     * @param array $keys
     *
     * @return null|string
     */
    protected function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }
        return null;
    }
}
