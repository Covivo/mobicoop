<?php

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
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class Addok extends AbstractHttpProvider implements Provider
{
    const TYPE_HOUSENUMBER = 'housenumber';
    const TYPE_STREET = 'street';
    const TYPE_LOCALITY = 'locality';
    const TYPE_MUNICIPALITY = 'municipality';
    const COUNTRY = 'France';
    const MIN_SCORE = 0.70;

    /**
     * @var string
     */
    private $rootUrl;

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

        $this->rootUrl = rtrim($rootUrl, '/');
    }

    private function getGeocodeEndpointUrl(): string
    {
        return $this->rootUrl . '/search/?q=%s&limit=%d&autocomplete=0';
    }

    private function getReverseEndpointUrl(): string
    {
        return $this->rootUrl . '/reverse/?lat=%F&lon=%F';
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

        $url = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $query->getLimit());

        if ($type = $query->getData('type', null)) {
            $url .= sprintf('&type=%s', $type);
        }

        $json = $this->executeQuery($url);

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
            if (is_array($context = explode(',', $feature->properties->context))) {
                $adminLevels = [
                    ['name' => trim($context[1]), 'level' => 4],
                    ['name' => trim($context[2]), 'level' => 5]
                ];
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

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();

        $url = sprintf($this->getReverseEndpointUrl(), $coordinates->getLatitude(), $coordinates->getLongitude());
        $json = $this->executeQuery($url);

        // no result
        if (empty($json->features)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json->features as $feature) {
            $coordinates = $feature->geometry->coordinates;
            $streetName = !empty($feature->properties->street) ? $feature->properties->street : null;
            $number = !empty($feature->properties->housenumber) ? $feature->properties->housenumber : null;
            $municipality = !empty($feature->properties->city) ? $feature->properties->city : null;
            $postalCode = !empty($feature->properties->postcode) ? $feature->properties->postcode : null;

            $results[] = Address::createFromArray([
                'providedBy'   => $this->getName(),
                'latitude'     => $coordinates[1],
                'longitude'    => $coordinates[0],
                'streetNumber' => $number,
                'streetName'   => $streetName,
                'locality'     => $municipality,
                'postalCode'   => $postalCode,
            ]);
        }

        return new AddressCollection($results);
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
     * @return \stdClass
     */
    private function executeQuery(string $url): \stdClass
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content);
        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        return $json;
    }
}
