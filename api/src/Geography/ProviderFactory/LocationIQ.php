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
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Location;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Sofiane Belaribi <sofiane.belaribi@etu.univ-lyon1.fr>
 */
final class LocationIQ extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const BASE_API_URL = 'https://eu1.locationiq.com/v1';

    /**
     * @var string
     */
    const VIEWBOX = '&bounded=1&viewbox=%min_lon%,%min_lat%,%max_lon%,%max_lat%';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * Limit search to a list of countries.
     * @var string
     */
    private $countrycodes = "fr";

    /**
     * For responses with no city value in the address section,
     * the next available element in this order - city_district,
     * locality, town, borough, municipality, village, hamlet,
     * quarter, neighbourhood - from the address section will be
     * normalized to city. Defaults to 0.
     * @var int
     */
    private $normalizecity = 1;

    /**
     * Sometimes you have several objects in OSM identifying the
     * same place or object in reality. The simplest case is a
     * street being split in many different OSM ways due to
     * different characteristics. Nominatim will attempt to
     * detect such duplicates and only return one match; this
     * is controlled by the dedupe parameter which defaults to 1.
     * Since the limit is, for reasons of efficiency,
     * enforced before and not after de-duplicating, it is
     * possible that de-duplicating leaves you with less
     * results than requested.
     * @var int
     */
    private $dedupe = 1;




    /**
     * @param HttpClient $client an HTTP adapter
     * @param string     $apiKey an API key
     */
    public function __construct(HttpClient $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;

        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        $url = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $query->getLimit());

        // Check if there is a viewbox
        if (
            !is_null($query->getData('minLatitude')) &&
            !is_null($query->getData('maxLatitude')) &&
            !is_null($query->getData('minLongitude')) &&
            !is_null($query->getData('maxLongitude'))
        ) {
            $url .= self::VIEWBOX;
            $url = str_replace('%min_lon%', $query->getData('minLongitude'), $url);
            $url = str_replace('%min_lat%', $query->getData('minLatitude'), $url);
            $url = str_replace('%max_lon%', $query->getData('maxLongitude'), $url);
            $url = str_replace('%max_lat%', $query->getData('maxLatitude'), $url);
        }
        
        $content = $this->executeQuery($url, $query->getLocale());

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw InvalidServerResponse::create($url);
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($places as $place) {
            $results[] = $this->xmlResultToArray($place, $place);
        }

        return new AddressCollection($results);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf($this->getReverseEndpointUrl(), $latitude, $longitude, $query->getData('zoom', 18));
        $content = $this->executeQuery($url, $query->getLocale());

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            return new AddressCollection([]);
        }

        $searchResult = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $searchResult->getElementsByTagName('addressparts')->item(0);
        $result = $searchResult->getElementsByTagName('result')->item(0);

        return new AddressCollection([$this->xmlResultToArray($result, $addressParts)]);
    }

    /**
     * @param \DOMElement $resultNode
     * @param \DOMElement $addressNode
     *
     * @return Location
     */
    private function xmlResultToArray(\DOMElement $resultNode, \DOMElement $addressNode): Location
    {
        $builder = new AddressBuilder($this->getName());

        foreach (['state', 'county'] as $i => $tagName) {
            if (null !== ($adminLevel = $this->getNodeValue($addressNode->getElementsByTagName($tagName)))) {
                $builder->addAdminLevel($i + 1, $adminLevel, '');
            }
        }

        // get the first postal-code when there are many
        $postalCode = $this->getNodeValue($addressNode->getElementsByTagName('postcode'));
        if (!empty($postalCode)) {
            $postalCode = current(explode(';', $postalCode));
        }
        $builder->setPostalCode($postalCode);
        $builder->setStreetName($this->getNodeValue($addressNode->getElementsByTagName('road')) ?: $this->getNodeValue($addressNode->getElementsByTagName('pedestrian')));
        $builder->setStreetNumber($this->getNodeValue($addressNode->getElementsByTagName('house_number')));
        //Locality if city not set ->county & if county not set ->village
        $builder->setLocality($this->getNodeValue($addressNode->getElementsByTagName('village')) ?: $this->getNodeValue($addressNode->getElementsByTagName('town')) ?: $this->getNodeValue($addressNode->getElementsByTagName('city')) ?: $this->getNodeValue($addressNode->getElementsByTagName('county')));
        //SubLocality if suburb(not useful for the moment) not set ->village & if village not set ->county
        $builder->setSubLocality(/*$this->getNodeValue($addressNode->getElementsByTagName('suburb')) ?:*/ $this->getNodeValue($addressNode->getElementsByTagName('village')) /*?: $this->getNodeValue($addressNode->getElementsByTagName('county'))*/);
        $builder->setCountry($this->getNodeValue($addressNode->getElementsByTagName('country')));
        $builder->setCountryCode(strtoupper($this->getNodeValue($addressNode->getElementsByTagName('country_code'))));
        $builder->setCoordinates($resultNode->getAttribute('lat'), $resultNode->getAttribute('lon'));

        $boundsAttr = $resultNode->getAttribute('boundingbox');
        if ($boundsAttr) {
            $bounds = [];
            list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = explode(',', $boundsAttr);
            $builder->setBounds($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']);
        }

        return $builder->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'locationiq';
    }

    /**
     * @param string      $url
     * @param string|null $locale
     *
     * @return string
     */
    private function executeQuery(string $url, string $locale = null): string
    {
        if (null !== $locale) {
            $url = sprintf('%s&accept-language=%s', $url, $locale);
        }

        return $this->getUrlContents($url);
    }

    private function getGeocodeEndpointUrl(): string
    {
        return self::BASE_API_URL.'/search.php?q=%s&format=xml&addressdetails=1&limit=%d&normalizecity=1&key='.$this->apiKey."&countrycodes=".$this->countrycodes;
    }

    private function getReverseEndpointUrl(): string
    {
        return self::BASE_API_URL.'/reverse.php?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=%d&key='.$this->apiKey;
    }

    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
