<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace App\Geography\ProviderFactory;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Http\Client\HttpClient;

/**
 * @author Sylvain Briat
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class Addok extends AbstractHttpProvider implements Provider
{
    const TYPE_HOUSENUMBER = 'housenumber';
    const TYPE_STREET = 'street';
    const TYPE_LOCALITY = 'locality';
    const TYPE_MUNICIPALITY = 'municipality';
    const COUNTRY = 'France';
    const MIN_SCORE = 0.7;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '/search/?q=%s&limit=%d&autocomplete=0';

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_PRIORITIZATION = '&lat=%f&lon=%f';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '/reverse/?lat=%F&lon=%F&size=%f';

    /**
     * @var string
     */
    private $uri;

    /**
     * @param HttpClient  $client
     * @param string|null $locale
     *
     * @return Addok
     */
    public static function withBANServer(HttpClient $client)
    {
        return new self($client, 'https://api-adresse.data.gouv.fr');
    }

    /**
     * @param HttpClient $client  an HTTP adapter
     * @param string     $rootUrl Root URL of the addok server
     */
    public function __construct(HttpClient $client, $rootUrl)
    {
        parent::__construct($client);

        $this->uri = rtrim($rootUrl, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        // This API does not support IP
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Addok provider does not support IP addresses, only street addresses.');
        }

        // Save a request if no valid address entered
        if (empty($address)) {
            throw new InvalidArgument('Address cannot be empty.');
        }
        $url = sprintf($this->uri . self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit());
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
        $url = sprintf($this->uri . self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLimit());
        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'addok';
    }

    /**
     * @param string $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content);
        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        // no result
        if (empty($json->features)) {
            return new AddressCollection([]);
        }
        $results = [];
        foreach ($json->features as $feature) {
            if ((float)$feature->properties->score < self::MIN_SCORE) {
                continue;
            }
            $coordinates = $feature->geometry->coordinates;

            switch ($feature->properties->type) {
                case self::TYPE_HOUSENUMBER:
                    $streetName = !empty($feature->properties->street) ? $feature->properties->street : null;
                    $number = !empty($feature->properties->housenumber) ? $feature->properties->housenumber : null;
                    break;
                case self::TYPE_STREET:
                    $streetName = !empty($feature->properties->name) ? $feature->properties->name : null;
                    $number = null;
                    break;
                default:
                    $streetName = null;
                    $number = null;
            }
            $locality = !empty($feature->properties->city) ? $feature->properties->city : null;
            $postalCode = !empty($feature->properties->postcode) ? $feature->properties->postcode : null;
            $adminLevels = [];
            // context contains 2 or 3 properties :
            // 1 : department number
            // 2 : department name
            // 3 : region name (optional)
            if (is_array($context = explode(',', $feature->properties->context))) {
                $adminLevels[] = ['name' => trim($context[1]), 'level' => 4];
                if (count($context) == 3) {
                    $adminLevels[] = ['name' => trim($context[2]), 'level' => 5];
                }
            }

            $results[] = Address::createFromArray([
                'providedBy'   => $this->getName(),
                'latitude'     => $coordinates[1],
                'longitude'    => $coordinates[0],
                'streetNumber' => $number,
                'streetName'   => $streetName,
                'locality'     => $locality,
                'postalCode'   => $postalCode,
                'country'      => self::COUNTRY,
                'adminLevels'  => $adminLevels
            ]);
        }

        return new AddressCollection($results);
    }
}
