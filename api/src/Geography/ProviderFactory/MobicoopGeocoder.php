<?php

declare(strict_types=1);

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
 **************************/

namespace App\Geography\ProviderFactory;

use Geocoder\Collection;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Sylvain Briat
 */
final class MobicoopGeocoder extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '?search=%s&lang=%s&max_results_per_type=5&max_results_per_geocoder=5&sanitize=1&consolidate=1';

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_PRIORITIZATION = '&lat=%f&lon=%f';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '/reverse?lat=%f&lon=%f&lang=%s&max_results_per_type=5&max_results_per_geocoder=5&sanitize=1&consolidate=1';

    /**
     * @var string
     */
    private $uri;

    /**
     * @param HttpClient $client an HTTP adapter
     * @param string     $uri the api uri
     */
    public function __construct(HttpClient $client, string $uri = null)
    {
        parent::__construct($client);

        $this->uri = rtrim($uri, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        $url = sprintf($this->uri . self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLocale());
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
        $url = sprintf($this->uri . self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLocale());
        return $this->executeQuery($url);
    }
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'mobicoop_geocoder';
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
        $results = [];
        foreach ($json as $location) {
            $result = PeliasAddress::createFromArray([
                'providedBy'   => $location['provider'],
                'latitude'     => $location['lat'],
                'longitude'    => $location['lon'],
                'streetNumber' => $location['house_number'],
                'streetName'   => $location['street_name'],
                'locality'     => $location['locality'],
                'postalCode'   => $location['postal_code'],
                'country'      => $location['country'],
                'countryCode'  => $location['country_code'],
                'adminLevels'  => [
                    ['name' => $location['region'], 'level' => 4],
                    ['name' => $location['macro_region'], 'level' => 5]
                ]
            ]);
            $result->setId($location['id']);
            $result->setVenue($location['type'] == 'venue' ? $location['name'] : null);
            $result->setDistance($location['distance']);
            $result->setLayer($location['type']);
            $results[] = $result;
        }

        return new AddressCollection($results);
    }
    
}
