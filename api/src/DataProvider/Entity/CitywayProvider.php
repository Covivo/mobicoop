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

namespace App\DataProvider\Entity;

use App\PublicTransport\Entity\Journey;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\PublicTransport\Entity\Arrival;
use App\PublicTransport\Entity\Departure;
use App\PublicTransport\Entity\Section;
use App\PublicTransport\Entity\PTMode;

/**
 * Cityway data provider.
 */
Class CitywayProvider implements ProviderInterface 
{
    CONST CW_PT_MODE_BUS = "BUS";
    CONST CW_PT_MODE_TRAIN = "TRAIN";
    CONST CW_PT_MODE_BIKE = "BIKE";
    CONST CW_PT_MODE_WALK = "WALK";
    
    private CONST URI = "http://preprod.tsvc.grandest.cityway.fr/api/";
    private CONST DATETIME_FORMAT = "d/m/Y H:i:s";
    private $collection;
    
    public function __construct()
    {
        $this->collection = [];
    }
    
    public function getCollection (string $class, string $apikey, array $params)
    {
        switch ($class) {
            case Journey::class:
                $dataProvider = new DataProvider(self::URI, 'journeyplanner/opt/PlanTrips/json');
                $getParams = [
                        "DepartureType" => "COORDINATES",
                        "ArrivalType" => "COORDINATES",
                        "DateType" => "DEPARTURE",
                        "TripModes" => "PT",
                        "Date" => $params["date"],
                        "DepartureLatitude" => $params["origin_latitude"],
                        "DepartureLongitude" => $params["origin_longitude"],
                        "ArrivalLatitude" => $params["destination_latitude"],
                        "ArrivalLongitude" => $params["destination_longitude"]
                ];
                $response = $dataProvider->getCollection($getParams);
                if ($response->getCode() == 200) {
                    $data = json_decode($response->getValue(), true);
                    if (!isset($data["StatusCode"])) return $this->collection;
                    if ($data["StatusCode"] <> 200) return $this->collection;
                    if (!isset($data["Data"])) return $this->collection;
                    if (!isset($data["Data"][0]["response"])) break;
                    if (!isset($data["Data"][0]["response"]["trips"]["Trip"])) break;
                    foreach ($data["Data"][0]["response"]["trips"]["Trip"] as $trip) {
                        $this->collection[] = self::deserialize($class,$trip);
                    }
                    return $this->collection;
                }
                break;
            default:
                break;
        }
    }

    public function getItem (string $class, string $apikey, array $params)
    {
        
    }
    
    function deserialize (string $class, array $data)
    {
        switch ($class) {
            case Journey::class:
                return self::deserializeJourney($data);
                break;
            default:
                break;
        }
    }
    
    private function deserializeJourney($data)
    {
        $journey = new Journey(count($this->collection)+1);
        if (isset($data["Distance"])) $journey->setDistance($data["Distance"]);
        if (isset($data["Duration"])) $journey->setDuration($data["Duration"]);
        if (isset($data["DepartureTime"])) {
            $departure = new Departure(1);
            $departure->setDate(\DateTime::createFromFormat(self::DATETIME_FORMAT, $data["DepartureTime"]));
            $journey->setDeparture($departure);
        }
        if (isset($data["ArrivalTime"])) {
            $arrival = new Arrival(1);
            $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_FORMAT, $data["ArrivalTime"]));
            $journey->setArrival($arrival);
        }
        if (isset($data["sections"]["Section"])) {
            $sections = [];
            foreach ($data["sections"]["Section"] as $section) {
                $sections[] = self::deserializeSection($section,count($sections)+1);
            }
            $journey->setSections($sections);
        }
        return $journey;
    }
    
    private function deserializeSection($data,$num)
    {
        $section = new Section($num);
        if (isset($data["Leg"]) && !is_null($data["Leg"])) {
            $ptmode = new PTMode(PTMode::PT_MODE_WALK);
            $section->setPtmode($ptmode);
            if (isset($data["Leg"]["pathLinks"]["PathLink"])) {
                foreach ($data["Leg"]["pathLinks"]["PathLink"] as $pathLink) {
                    
                }
            }
        }
        if (isset($data["PTRide"]) && !is_null($data["PTRide"])) {
            if ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_BUS) {
                $ptmode = new PTMode(PTMode::PT_MODE_BUS);
                $section->setPtmode($ptmode);
            }
        }
        return $section;
    }
       
}