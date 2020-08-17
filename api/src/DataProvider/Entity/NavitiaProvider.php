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
 **************************/

namespace App\DataProvider\Entity;

use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Entity\PTArrival;
use App\PublicTransport\Entity\PTDeparture;
use App\Travel\Entity\TravelMode;
use App\PublicTransport\Entity\PTLine;
use App\PublicTransport\Entity\PTLeg;
use App\Geography\Entity\Address;
use App\PublicTransport\Entity\PTCompany;
use App\DataProvider\Exception\DataProviderException;

/**
 * Navitia Public Transportation data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
class NavitiaProvider implements ProviderInterface
{
    private const PT_TYPE_STREET = "street_network";
    private const PT_TYPE_PT = "public_transport";
    private const PT_TYPE_WAITING = "waiting";
    private const PT_TYPE_TRANSFER = "transfer";

    private const PT_MODE_CAR = "ridesharing";
    private const PT_MODE_BUS = "Bus";
    private const PT_MODE_TRAIN = "Train";
    private const PT_MODE_TRAIN_LOCAL = "LocalTrain";
    private const PT_MODE_TRAIN_LONG_DISTANCE = "LongDistanceTrain";
    private const PT_MODE_WALK = "walking";
    private const PT_MODE_SUBWAY = "Metro";
    private const PT_MODE_TRAMWAY = "Tramway";

    private const COUNTRY = "France";
    private const NC = "NC";

    private const COLLECTION_RESSOURCE_JOURNEYS = "v1/journeys";

    private const DATETIME_INPUT_FORMAT = "Y-m-d\TH:i:s";

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


    private function getCollectionJourneys($class, array $params, string $apikey)
    {
        // Do the PT search
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_JOURNEYS);

        $params = [
            "from"=> $params["origin_longitude"].";".$params["origin_latitude"],
            'to'=> $params["destination_longitude"].";".$params["destination_latitude"]
        ];

        $header = [
            "Authorization" => $apikey
        ];

        $response = $dataProvider->getCollection($params, $header);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            
            foreach ($data['journeys'] as $journey) {
                $this->collection[] = $this->deserialize($class, $journey);
            }
        } elseif ($response->getCode() == 510) {
            // Out of bound for conduent
            //throw new DataProviderException(DataProviderException::OUT_OF_BOUND);
            // For out of bound we do nothing. We just treat it as a no found solution
        } else {
            throw new DataProviderException(DataProviderException::ERROR_COLLECTION_RESSOURCE_JOURNEYS);
        }
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

    private function deserializeJourney($data)
    {
        $journey = new PTJourney(count($this->collection)+1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if (isset($data['duration'])) {
            $journey->setDuration($data['duration']);
        }
        if (isset($data['nbConnections'])) {
            $journey->setChangeNumber($data['nb_transfers']);
        }
        
        
        if (isset($data['sections'])) {
            $nblegs = 0;
            foreach ($data['sections'] as $section) {

                // First leg, it's the departure
                if ($nblegs==0) {
                    if (isset($section['from'])) {
                        $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        if ($data['departure_date_time']) {
                            $departure->setDate(new \DateTime($data['departure_date_time']));
                        }
                
                        $departureAddress = new Address();
                        $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        $departureAddress->setAddressCountry(self::COUNTRY);
                            
                        $departureAddress->setAddressLocality(self::NC);
                        if (isset($section['from']['address']['administrative_regions'])) {
                            foreach ($section['from']['address']['administrative_regions'] as $administrative_region) {
                                if (isset($administrative_region["level"]) && $administrative_region["level"]==8) {
                                    $departureAddress->setAddressLocality($administrative_region['name']);
                                }
                            }
                        }
                            
                        $departureAddress->setStreetAddress(self::NC);
                        if (isset($section['from']['address']['name'])) {
                            $departureAddress->setStreetAddress($section['from']['address']['name']);
                        }
                
                        if (isset($section['from']['address']['coord'])) {
                            $departureAddress->setLatitude($section['from']['address']['coord']['lat']);
                            $departureAddress->setLongitude($section['from']['address']['coord']['lon']);
                        }
                        $departure->setAddress($departureAddress);
                    
                        $journey->setPTDeparture($departure);
                    }
                }


                // Last leg, it's the arrival
                if ($nblegs==(count($data['sections'])-1)) {
                    if (isset($section['to'])) {
                        $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        if ($data['arrival_date_time']) {
                            $arrival->setDate(new \DateTime($data['arrival_date_time']));
                        }
                        
                        $arrivalAddress = new Address();
                        $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                        $arrivalAddress->setAddressCountry(self::COUNTRY);
                            
                        $arrivalAddress->setAddressLocality(self::NC);
                        if (isset($section['to']['address']['administrative_regions'])) {
                            foreach ($section['to']['address']['administrative_regions'] as $administrative_region) {
                                if (isset($administrative_region["level"]) && $administrative_region["level"]==8) {
                                    $arrivalAddress->setAddressLocality($administrative_region['name']);
                                }
                            }
                        }
                            
                        $arrivalAddress->setStreetAddress(self::NC);
                        if (isset($section['to']['address']['name'])) {
                            $arrivalAddress->setStreetAddress($section['to']['address']['name']);
                        }

                        if (isset($section['to']['address']['coord'])) {
                            $arrivalAddress->setLatitude($section['to']['address']['coord']['lat']);
                            $arrivalAddress->setLongitude($section['to']['address']['coord']['lon']);
                        }

                        $arrival->setAddress($arrivalAddress);
                        
                        $journey->setPTArrival($arrival);
                    }
                }


                // Treat the Leg

                $nblegs++;
                $leg = $this->deserializeTravelSection($section, $nblegs);
                if (!is_null($leg)) {
                    $journey->addPTLeg($leg);
                }
            }
        }
        if (isset($data['data']["environment"]["totalEnvironmentalCost"])) {
            $journey->setCo2($data['data']["environment"]["totalEnvironmentalCost"]);
        }
        return $journey;
    }

    private function deserializeTravelSection($data, $num)
    {
        $leg = new PTLeg($num);

        if ($data["type"] == self::PT_TYPE_STREET && $data["mode"] == self::PT_MODE_WALK) {
            // walk mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_STREET && $data["mode"] == self::PT_MODE_CAR) {
            // car mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_CAR);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_WAITING) {
            // waiting mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WAITING);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_TRANSFER && $data["transfer_type"] == self::PT_MODE_WALK) {
            // waiting mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_PT && $data["display_informations"]["physical_mode"] == self::PT_MODE_BUS) {
            // bus mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BUS);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_PT &&
                ($data["display_informations"]["physical_mode"] == self::PT_MODE_TRAIN_LOCAL || $data["display_informations"]["physical_mode"] == self::PT_MODE_TRAIN)
            ) {
            // train local mode
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_LOCAL);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_PT && $data["display_informations"]["physical_mode"] == self::PT_MODE_TRAIN_LONG_DISTANCE) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_HIGH_SPEED);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_PT && $data["display_informations"]["physical_mode"] == self::PT_MODE_SUBWAY) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_SUBWAY);
            $leg->setTravelMode($travelMode);
        } elseif ($data["type"] == self::PT_TYPE_PT && $data["display_informations"]["physical_mode"] == self::PT_MODE_TRAMWAY) {
            // subway
            $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAMWAY);
            $leg->setTravelMode($travelMode);
        }
            
        if (!isset($travelMode)) {
            // No travel mode found for this leg
            return null;
        }

        if (isset($data["duration"]) && !is_null($data["duration"])) {
            $leg->setDuration($data["duration"]);
        }


        $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if ($data['departure_date_time']) {
            $departure->setDate(new \DateTime($data['departure_date_time']));
        }
        
        $departureAddress = new Address();
        $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        $departureAddress->setAddressCountry(self::COUNTRY);
                    
        if (isset($data['from'])) {
            $departureAddress->setAddressLocality(self::NC);
            if (isset($data['from']['address']['administrative_regions'])) {
                foreach ($data['from']['address']['administrative_regions'] as $administrative_region) {
                    if (isset($administrative_region["level"]) && $administrative_region["level"]==8) {
                        $departureAddress->setAddressLocality($administrative_region['name']);
                    }
                }
            }
                        
            $departureAddress->setStreetAddress(self::NC);
            if (isset($data['from']['address']['name'])) {
                $departureAddress->setStreetAddress($data['from']['address']['name']);
            }
            
            if (isset($data['from']['address']['coord'])) {
                $departureAddress->setLatitude($data['from']['address']['coord']['lat']);
                $departureAddress->setLongitude($data['from']['address']['coord']['lon']);
            }
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
            $arrivalAddress->setAddressLocality(self::NC);
            if (isset($data['to']['address']['administrative_regions'])) {
                foreach ($data['to']['address']['administrative_regions'] as $administrative_region) {
                    if (isset($administrative_region["level"]) && $administrative_region["level"]==8) {
                        $arrivalAddress->setAddressLocality($administrative_region['name']);
                    }
                }
            }
                        
            $arrivalAddress->setStreetAddress(self::NC);
            if (isset($data['to']['address']['name'])) {
                $arrivalAddress->setStreetAddress($data['to']['address']['name']);
            }

            if (isset($data['to']['address']['coord'])) {
                $arrivalAddress->setLatitude($data['to']['address']['coord']['lat']);
                $arrivalAddress->setLongitude($data['to']['address']['coord']['lon']);
            }
        }

        $arrival->setAddress($arrivalAddress);
                
        $leg->setPTArrival($arrival);

            
        if (isset($data["display_informations"])) {
            $ptline = new PTLine(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $ptline->setTravelMode($leg->getTravelMode());
            if (isset($data["display_informations"]['name'])) {
                $ptline->setName($data["display_informations"]['name']);
            }
            if (isset($data["display_informations"]['label'])) {
                $ptline->setNumber($data["display_informations"]["label"]);
            }
            if (isset($data["display_informations"]["direction"])) {
                $leg->setDirection($data["display_informations"]["direction"]);
            }
            if (isset($data["display_informations"]["color"])) {
                $ptline->setColor($data["display_informations"]["color"]);
            }
                
            $ptcompany = new PTCompany(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $ptcompany->setName(self::NC);
            if (isset($data["display_informations"]["network"])) {
                $ptcompany->setName($data["display_informations"]["network"]);
            }
            $ptline->setPTCompany($ptcompany);
                
            $leg->setPTLine($ptline);
        }

        return $leg;
    }

    /**
     * Convert a Duration type hh:ii:ss in seconds
     *
     * @param string $duration
     * @return int
     */
    private function convertToSeconds(string $duration)
    {
        $durationTab = explode(":", $duration);
        $durationInSeconds = 0;

        if (isset($durationTab[0])) {
            $durationInSeconds += (int)($durationTab[0]) * 3600;
        }
        if (isset($durationTab[1])) {
            $durationInSeconds += (int)($durationTab[1]) * 60;
        }
        if (isset($durationTab[2])) {
            $durationInSeconds += (int)($durationTab[2]);
        }

        return $durationInSeconds;
    }
}
