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
 * Conduent Public Transportation data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ConduentPTProvider implements ProviderInterface
{
    private const PT_MODE_CAR = 'Driving';
    private const PT_MODE_BUS = 'Bus';
    private const PT_MODE_TRAIN_LOCAL = 'Train';
    private const PT_MODE_WALK = 'Walking';
    private const PT_MODE_SUBWAY = 'Subway';
    private const PT_MODE_WAITING = 'Waiting';
    private const PT_MODE_BIKE = 'Biking';

    private const COUNTRY = 'France';
    private const NC = '';

    private const PROFILE_RESSOURCE = 'MCP.ID.API/profiles';
    private const COLLECTION_RESSOURCE_JOURNEYS = 'MCP.TSUP.API/travelQueries/full';

    private const DATETIME_INPUT_FORMAT = 'Y-m-d\\TH:i:s';

    private const NB_TRAVELERS = 3;
    private const ALLOW_EXTENDED_QUERIES_IN_PAST = 1;

    private const DEFAULT_PROFILE_ID = 5275;

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
        // Get profile id
        $dataProvider = new DataProvider($this->uri, self::PROFILE_RESSOURCE);
        $paramsGet = [
            'criteria.name' => $params['username'],
        ];
        $headers = [
            'Cookie' => 'XAuthToken=ApiKey-'.$apikey,
        ];
        $response = $dataProvider->getCollection($paramsGet, $headers);

        $profileId = self::DEFAULT_PROFILE_ID;
        if (200 == $response->getCode()) {
            $data = json_decode((string) $response->getValue(), true);
            if (isset($data['data'], $data['data']['list'])) {
                foreach ($data['data']['list'] as $profile) {
                    if (isset($profile['data'], $profile['data']['profileId']) && $profile['data']['isDefault']) {
                        $profileId = $profile['data']['profileId'];
                    }
                }
            }
        }

        // Do the PT search
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_JOURNEYS);

        $paramsPost = [
            'origin' => [
                'latitude' => $params['origin_latitude'],
                'longitude' => $params['origin_longitude'],
            ],
            'destination' => [
                'latitude' => $params['destination_latitude'],
                'longitude' => $params['destination_longitude'],
            ],
            'travelerProfileId' => $profileId,
            'nbTravelers' => self::NB_TRAVELERS,
            'allowExtendedQueriesInPast' => self::ALLOW_EXTENDED_QUERIES_IN_PAST,
            'departureDate' => $params['date']->format(self::DATETIME_INPUT_FORMAT).'Z',
        ];

        $response = $dataProvider->postCollection($paramsPost, $headers);
        if (200 == $response->getCode()) {
            $data = json_decode((string) $response->getValue(), true);

            foreach ($data['data']['list'] as $trip) {
                $journey = $this->deserialize($class, $trip);
                if (is_null($journey)) {
                    continue;
                }
                $this->collection[] = $journey;
            }
        } elseif (510 == $response->getCode()) {
            // Out of bound for conduent
            // throw new DataProviderException(DataProviderException::OUT_OF_BOUND);
            // For out of bound we do nothing. We just treat it as a no found solution
        } else {
            throw new DataProviderException(DataProviderException::ERROR_COLLECTION_RESSOURCE_JOURNEYS);
        }
    }

    private function deserializeJourney($data)
    {
        $journey = new PTJourney(count($this->collection) + 1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if (isset($data['data']['result']['travelDuration'])) {
            $journey->setDuration($this->convertToSeconds($data['data']['result']['travelDuration']));
        }
        if (isset($data['data']['result']['nbConnections'])) {
            $journey->setChangeNumber($data['data']['result']['nbConnections']);
        }

        $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if ($data['data']['result']['departureDate']) {
            $departure->setDate(new \DateTime($data['data']['result']['departureDate']));
        }

        $departureAddress = new Address();
        $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        $departureAddress->setAddressCountry(self::COUNTRY);

        if (isset($data['data']['result']['originTown'])) {
            $departureAddress->setAddressLocality($data['data']['result']['originTown']);
        } else {
            $departureAddress->setAddressLocality(self::NC);
        }

        if (isset($data['data']['result']['originName'])) {
            $departureAddress->setStreetAddress($data['data']['result']['originName']);
        } else {
            $departureAddress->setStreetAddress(self::NC);
        }

        if (isset($data['data']['result']['origin'], $data['data']['result']['origin']['latitude'])) {
            $departureAddress->setLatitude($data['data']['result']['origin']['latitude']);
        }
        if (isset($data['data']['result']['origin'], $data['data']['result']['origin']['longitude'])) {
            $departureAddress->setLongitude($data['data']['result']['origin']['longitude']);
        }
        $departure->setAddress($departureAddress);

        $journey->setPTDeparture($departure);

        $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if ($data['data']['result']['arrivalDate']) {
            $arrival->setDate(new \DateTime($data['data']['result']['arrivalDate']));
        }

        $arrivalAddress = new Address();
        $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        $arrivalAddress->setAddressCountry(self::COUNTRY);

        if (isset($data['data']['result']['destinationTown'])) {
            $arrivalAddress->setAddressLocality($data['data']['result']['destinationTown']);
        } else {
            $arrivalAddress->setAddressLocality(self::NC);
        }

        if (isset($data['data']['result']['destinationName'])) {
            $arrivalAddress->setStreetAddress($data['data']['result']['destinationName']);
        } else {
            $arrivalAddress->setStreetAddress(self::NC);
        }

        if (isset($data['data']['result']['destination'], $data['data']['result']['destination']['latitude'])) {
            $arrivalAddress->setLatitude($data['data']['result']['destination']['latitude']);
        }
        if (isset($data['data']['result']['destination'], $data['data']['result']['destination']['longitude'])) {
            $arrivalAddress->setLongitude($data['data']['result']['destination']['longitude']);
        }
        $arrival->setAddress($arrivalAddress);

        $journey->setPTArrival($arrival);

        if (isset($data['data']['result']['travelSections'])) {
            $nblegs = 0;
            foreach ($data['data']['result']['travelSections'] as $travelSection) {
                $leg = $this->deserializeTravelSection($travelSection, $nblegs);
                if (is_null($leg)) {
                    return null;
                }
                ++$nblegs;
                $journey->addPTLeg($leg);
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
        if (isset($data['data']) && !is_null($data['data'])) {
            if (self::PT_MODE_WALK == $data['data']['modalityDescription']['modality']) {
                // walk mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_CAR == $data['data']['modalityDescription']['modality']) {
                // car mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_CAR);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_BUS == $data['data']['modalityDescription']['modality']) {
                // bus mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BUS);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_TRAIN_LOCAL == $data['data']['modalityDescription']['modality']) {
                // train local mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_LOCAL);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_SUBWAY == $data['data']['modalityDescription']['modality']) {
                // subway
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_SUBWAY);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_WAITING == $data['data']['modalityDescription']['modality']) {
                // waiting
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WAITING);
                $leg->setTravelMode($travelMode);
            } elseif (self::PT_MODE_BIKE == $data['data']['modalityDescription']['modality']) {
                // waiting
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BIKE);
                $leg->setTravelMode($travelMode);
            } else {
                return null;
            }

            if (isset($data['data']['duration']) && !is_null($data['data']['duration'])) {
                $leg->setDuration($this->convertToSeconds($data['data']['duration']));
            }

            if (isset($data['data']['distance']) && !is_null($data['data']['distance'])) {
                $leg->setDistance($data['data']['distance']);
            }

            if (isset($data['data']['origin'])) {
                $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if ($data['data']['departureDate']) {
                    $departure->setDate(new \DateTime($data['data']['departureDate']));
                }

                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::COUNTRY);

                if (isset($data['data']['origin']['town'])) {
                    $departureAddress->setAddressLocality($data['data']['origin']['town']);
                } else {
                    $departureAddress->setAddressLocality(self::NC);
                }

                if (isset($data['data']['origin']['name'])) {
                    $departureAddress->setStreetAddress($data['data']['origin']['name']);
                } else {
                    $departureAddress->setStreetAddress(self::NC);
                }

                if (isset($data['data']['origin']['position'], $data['data']['origin']['position']['latitude'])) {
                    $departureAddress->setLatitude($data['data']['origin']['position']['latitude']);
                }
                if (isset($data['data']['origin']['position'], $data['data']['origin']['position']['longitude'])) {
                    $departureAddress->setLongitude($data['data']['origin']['position']['longitude']);
                }
                $departure->setAddress($departureAddress);

                if (isset($data['data']['origin']['description']) && '' != $data['data']['origin']['description']) {
                    $departure->setName($data['data']['origin']['description']);
                } else {
                    if (isset($data['data']['origin']['name']) && '' != $data['data']['origin']['name']) {
                        $departure->setName($data['data']['origin']['name']);
                    } else {
                        $departure->setName(self::NC);
                    }
                }

                $leg->setPTDeparture($departure);
            }
            if (isset($data['data']['destination'])) {
                $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if ($data['data']['arrivalDate']) {
                    $arrival->setDate(new \DateTime($data['data']['arrivalDate']));
                }

                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::COUNTRY);

                if (isset($data['data']['destination']['town'])) {
                    $arrivalAddress->setAddressLocality($data['data']['destination']['town']);
                } else {
                    $arrivalAddress->setAddressLocality(self::NC);
                }

                if (isset($data['data']['destination']['name'])) {
                    $arrivalAddress->setStreetAddress($data['data']['destination']['name']);
                } else {
                    $arrivalAddress->setStreetAddress(self::NC);
                }

                if (isset($data['data']['destination']['position'], $data['data']['destination']['position']['latitude'])) {
                    $arrivalAddress->setLatitude($data['data']['destination']['position']['latitude']);
                }
                if (isset($data['data']['destination']['position'], $data['data']['destination']['position']['longitude'])) {
                    $arrivalAddress->setLongitude($data['data']['destination']['position']['longitude']);
                }
                $arrival->setAddress($arrivalAddress);

                if (isset($data['data']['destination']['description']) && '' != $data['data']['destination']['description']) {
                    $arrival->setName($data['data']['destination']['description']);
                } else {
                    if (isset($data['data']['destination']['name']) && '' != $data['data']['destination']['name']) {
                        $arrival->setName($data['data']['destination']['name']);
                    } else {
                        $arrival->setName(self::NC);
                    }
                }

                $leg->setPTArrival($arrival);
            }
            if (isset($data['data']['directionName']) && '' !== $data['data']['directionName']) {
                $ptline = new PTLine(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $ptline->setTravelMode($leg->getTravelMode());
                if (isset($data['data']['lineShortName'])) {
                    $ptline->setName($data['data']['lineShortName']);
                }
                if (isset($data['data']['networkId'])) {
                    $ptline->setNumber($data['data']['networkId']);
                }
                if (isset($data['data']['directionName'])) {
                    $leg->setDirection($data['data']['directionName']);
                }

                $ptcompany = new PTCompany(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $ptcompany->setName(self::NC);
                $ptline->setPTCompany($ptcompany);

                $leg->setPTLine($ptline);
            }
        }

        return $leg;
    }

    /**
     * Convert a Duration type hh:ii:ss in seconds.
     */
    private function convertToSeconds(string $duration): int
    {
        $durationTab = explode(':', $duration);
        $durationInSeconds = 0;

        if (isset($durationTab[0])) {
            $durationInSeconds += (int) ($durationTab[0]) * 3600;
        }
        if (isset($durationTab[1])) {
            $durationInSeconds += (int) ($durationTab[1]) * 60;
        }
        if (isset($durationTab[2])) {
            $durationInSeconds += (int) ($durationTab[2]);
        }

        return $durationInSeconds;
    }
}
