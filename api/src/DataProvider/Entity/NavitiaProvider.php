<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity;

use App\DataProvider\Exception\DataProviderException;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\Address;
use App\PublicTransport\Entity\PTArrival;
use App\PublicTransport\Entity\PTCompany;
use App\PublicTransport\Entity\PTDeparture;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Entity\PTLeg;
use App\PublicTransport\Entity\PTLine;
use App\Travel\Entity\TravelMode;

/**
 * Navitia Public Transportation data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class NavitiaProvider implements ProviderInterface
{
    private const PT_TYPE_STREET = 'street_network';
    private const PT_TYPE_PT = 'public_transport';
    private const PT_TYPE_WAITING = 'waiting';
    private const PT_TYPE_TRANSFER = 'transfer';

    private const PT_MODE_CAR = 'ridesharing';
    private const PT_MODE_BUS = 'Bus';
    private const PT_MODE_TRAIN = 'Train';
    private const PT_MODE_TRAIN_LOCAL = 'LocalTrain';
    private const PT_MODE_TRAIN_LONG_DISTANCE = 'LongDistanceTrain';
    private const PT_MODE_WALK = 'walking';
    private const PT_MODE_SUBWAY = 'Metro';
    private const PT_MODE_TRAMWAY = 'Tramway';

    private const COUNTRY = 'France';
    private const NC = '';

    private const COLLECTION_RESSOURCE_JOURNEYS = 'v1/journeys';

    private const DATETIME_INPUT_FORMAT = 'Y-m-d\\TH:i:s';

    private $collection;
    private $uri;

    public function __construct(string $uri)
    {
        $this->collection = [];
        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
        switch ($class) {
            case PTJourney::class:
                $this->getCollectionJourneys($class, $params, $apikey);

                return $this->collection;

               break;

            default:
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $class, string $apikey, array $params)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case PTJourney::class:
                return $this->deserializeJourney($data);

                break;

            default:
                break;
        }
    }

    private function getCollectionJourneys($class, array $params, string $apikey)
    {
        // Do the PT search
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_JOURNEYS);

        $params = [
            'from' => $params['origin_longitude'].';'.$params['origin_latitude'],
            'to' => $params['destination_longitude'].';'.$params['destination_latitude'],
        ];

        $header = [
            'Authorization' => $apikey,
        ];

        $response = $dataProvider->getCollection($params, $header);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);

            if (isset($data['error'])) {
                return;
            }

            foreach ($data['journeys'] as $journey) {
                $this->collection[] = $this->deserialize($class, $journey);
            }
        } elseif (510 == $response->getCode() || 404 == $response->getCode() || 500 == $response->getCode()) {
            // Out of bound
            // throw new DataProviderException(DataProviderException::OUT_OF_BOUND);
            // For out of bound we do nothing. We just treat it as a no found solution
        } else {
            throw new DataProviderException(DataProviderException::ERROR_COLLECTION_RESSOURCE_JOURNEYS);
        }
    }

    private function deserializeJourney($data)
    {
        $journey = new PTJourney(count($this->collection) + 1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if (isset($data['duration'])) {
            $journey->setDuration($data['duration']);
        }
        if (isset($data['nb_transfers'])) {
            $journey->setChangeNumber($data['nb_transfers']);
        }

        if (isset($data['sections'])) {
            $nblegs = 0;
            foreach ($data['sections'] as $section) {
                // First leg, it's the departure
                if (0 == $nblegs) {
                    if (isset($section['from'])) {
                        $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        if ($data['departure_date_time']) {
                            $departure->setDate(new \DateTime($data['departure_date_time']));
                        }

                        $departureAddress = new Address();
                        $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        $departureAddress->setAddressCountry(self::COUNTRY);

                        $departureAddress = $this->setAddressInfos($departureAddress, $section['from']);

                        $departure->setAddress($departureAddress);

                        $journey->setPTDeparture($departure);
                    }
                }

                // Last leg, it's the arrival
                if ($nblegs == (count($data['sections']) - 1)) {
                    if (isset($section['to'])) {
                        $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        if ($data['arrival_date_time']) {
                            $arrival->setDate(new \DateTime($data['arrival_date_time']));
                        }

                        $arrivalAddress = new Address();
                        $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        $arrivalAddress->setAddressCountry(self::COUNTRY);

                        $arrivalAddress = $this->setAddressInfos($arrivalAddress, $section['to']);

                        $arrival->setAddress($arrivalAddress);

                        $journey->setPTArrival($arrival);
                    }
                }

                // Treat the Leg

                ++$nblegs;
                $leg = $this->deserializeTravelSection($section, $nblegs);
                if (!is_null($leg)) {
                    $journey->addPTLeg($leg);
                }
            }
        }
        if (isset($data['data']['environment']['totalEnvironmentalCost'])) {
            $journey->setCo2($data['data']['environment']['totalEnvironmentalCost']);
        }

        return $journey;
    }

    private function deserializeTravelSection($data, $num)
    {
        $leg = new PTLeg($num);

        if (self::PT_TYPE_STREET == $data['type'] && self::PT_MODE_WALK == $data['mode']) {
            // walk mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_STREET == $data['type'] && self::PT_MODE_CAR == $data['mode']) {
            // car mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_CAR);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_WAITING == $data['type']) {
            // waiting mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WAITING);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_TRANSFER == $data['type'] && self::PT_MODE_WALK == $data['transfer_type']) {
            // waiting mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_PT == $data['type'] && self::PT_MODE_BUS == $data['display_informations']['physical_mode']) {
            // bus mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BUS);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_PT == $data['type']
                && (self::PT_MODE_TRAIN_LOCAL == $data['display_informations']['physical_mode'] || self::PT_MODE_TRAIN == $data['display_informations']['physical_mode'])
            ) {
            // train local mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_LOCAL);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_PT == $data['type'] && self::PT_MODE_TRAIN_LONG_DISTANCE == $data['display_informations']['physical_mode']) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_HIGH_SPEED);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_PT == $data['type'] && self::PT_MODE_SUBWAY == $data['display_informations']['physical_mode']) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_SUBWAY);
            $leg->setTravelMode($travelMode);
        } elseif (self::PT_TYPE_PT == $data['type'] && self::PT_MODE_TRAMWAY == $data['display_informations']['physical_mode']) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAMWAY);
            $leg->setTravelMode($travelMode);
        }

        if (!isset($travelMode)) {
            // No travel mode found for this leg
            return null;
        }

        if (isset($data['duration']) && !is_null($data['duration'])) {
            $leg->setDuration($data['duration']);
        }

        $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if ($data['departure_date_time']) {
            $departure->setDate(new \DateTime($data['departure_date_time']));
        }

        $departureAddress = new Address();
        $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        $departureAddress->setAddressCountry(self::COUNTRY);

        if (isset($data['from'])) {
            $departureAddress = $this->setAddressInfos($departureAddress, $data['from']);
        }

        $departure->setAddress($departureAddress);

        $leg->setPTDeparture($departure);

        $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if ($data['arrival_date_time']) {
            $arrival->setDate(new \DateTime($data['arrival_date_time']));
        }

        $arrivalAddress = new Address();
        $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        $arrivalAddress->setAddressCountry(self::COUNTRY);

        if (isset($data['to'])) {
            $arrivalAddress = $this->setAddressInfos($arrivalAddress, $data['to']);
        }

        $arrival->setAddress($arrivalAddress);

        $leg->setPTArrival($arrival);

        if (isset($data['display_informations'])) {
            $ptline = new PTLine(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $ptline->setTravelMode($leg->getTravelMode());
            if (isset($data['display_informations']['name'])) {
                $ptline->setName($data['display_informations']['name']);
            }
            if (isset($data['display_informations']['label'])) {
                $ptline->setNumber($data['display_informations']['label']);
            }
            if (isset($data['display_informations']['direction'])) {
                $leg->setDirection($data['display_informations']['direction']);
            }
            if (isset($data['display_informations']['color'])) {
                $ptline->setColor($data['display_informations']['color']);
            }

            $ptcompany = new PTCompany(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $ptcompany->setName(self::NC);
            if (isset($data['display_informations']['network'])) {
                $ptcompany->setName($data['display_informations']['network']);
            }
            $ptline->setPTCompany($ptcompany);

            $leg->setPTLine($ptline);
        }

        return $leg;
    }

    private function setAddressInfos(Address $address, $data)
    {
        $address->setAddressLocality(self::NC);

        if (isset($data['address'])) {
            $base = $data['address'];
        } elseif (isset($data['stop_point'])) {
            $base = $data['stop_point'];
        }

        if (isset($base['administrative_regions'])) {
            foreach ($base['administrative_regions'] as $administrative_region) {
                if (isset($administrative_region['level']) && 8 == $administrative_region['level']) {
                    $address->setAddressLocality($administrative_region['name']);
                }
            }
        }

        $address->setStreetAddress(self::NC);
        if (isset($base['name'])) {
            $address->setStreetAddress($base['name']);
        }

        if (isset($base['coord'])) {
            $address->setLatitude($base['coord']['lat']);
            $address->setLongitude($base['coord']['lon']);
        }

        return $address;
    }
}
