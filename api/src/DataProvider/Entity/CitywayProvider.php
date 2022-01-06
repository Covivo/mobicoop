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

use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\PublicTransport\Entity\PTAccessibilityStatus;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Entity\PTArrival;
use App\PublicTransport\Entity\PTDeparture;
use App\PublicTransport\Entity\PTLineStop;
use App\PublicTransport\Entity\PTLineStopList;
use App\PublicTransport\Entity\PTLocality;
use App\PublicTransport\Entity\PTStop;
use App\PublicTransport\Entity\PTTripPoint;
use App\Travel\Entity\TravelMode;
use App\PublicTransport\Entity\PTStep;
use App\PublicTransport\Entity\PTLine;
use App\PublicTransport\Entity\PTLeg;
use App\PublicTransport\Service\PTDataProvider;
use App\Geography\Entity\Address;
use App\Match\Exception\MassException;
use App\PublicTransport\Entity\PTCompany;

/**
 * Cityway data provider.
 *
 * Implements all the methods needed to retrieve data from CityWay :
 * - get collection and item
 * - deserialize to populate Public Transport entities
 *
 * With CityWay :
 * - the Journey consists in one or more Trips
 * - each Trip is an alternative to reach the destination from the departure
 * - a Trip has a departure and arrival time, a duration, a distance...
 * - a Trip consists in one or more Sections
 * - a Section consists in a Leg or a PTRide
 * - a Leg is the name for an indivual transport mode section (walk, bike, car...)
 * - a Leg consists in one or more PathLinks
 * - a PathLink consists in a departure and arrival point (and distance, duration, direction, eventually a site like a bus stop etc...)
 * - typically, a PathLink is a path between two points on the same road; when one needs to turn or change direction it is another PathLink
 * - a PTRide is the name for a public transport ride, it can be by bus, train, coach...
 * - a PTRide consists in one or more Steps
 * - typically, a step is a path between two consecutive stops, for example two consecutive stops of the same bus line
 *
 * Eg :
 *
 * if a trip consist in :
 * - a walk,
 * - then a bus ride,
 * - then another bus ride on another line,
 * - then a walk,
 *
 * we will have :
 * - one Leg,
 * - a first PTRide,
 * - a second PTRide,
 * - and finally a second Leg
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
class CitywayProvider implements ProviderInterface
{
    private const CW_PT_MODE_CAR = "PRIVATE_VEHICLE";
    private const CW_PT_MODE_BUS = "BUS";
    private const CW_PT_MODE_TRAMWAY = "TRAMWAY";
    private const CW_PT_MODE_COACH = "COACH";
    private const CW_PT_MODE_TRAIN = "TRAIN";
    private const CW_PT_MODE_TRAIN_LOCAL = "LOCAL_TRAIN";
    private const CW_PT_MODE_TRAIN_HIGH_SPEED = "HST";
    private const CW_PT_MODE_BIKE = "BICYCLE";
    private const CW_PT_MODE_WALK = "WALK";
    private const CW_PT_MODE_ON_DEMAND = "TOD";
    private const CW_PT_MODE_METRO = "METRO";
    private const CW_PT_MODE_TROLLEY_BUS = "TROLLEY_BUS";
    private const CW_PT_MODE_UNKNOWN = "UNKNOWN";
 
    private const CW_COUNTRY = "France";
    private const CW_NC = "";

    private const COLLECTION_RESSOURCE_JOURNEYS = "journeyplanner/api/opt/PlanTrips/json";
    private const COLLECTION_RESSOURCE_TRIPPOINTS = "api/transport/v3/trippoint/GetTripPoints/json";
    private const COLLECTION_RESSOURCE_LINESTOPS = "api/transport/v3/stop/GetLineStops/json";

    private const DATETIME_OUTPUT_FORMAT = "d/m/Y H:i:s";
    private const DATETIME_INPUT_FORMAT = "Y-m-d_H-i";

    private const DATETYPES = [
        PTDataProvider::DATETYPE_DEPARTURE => "DEPARTURE",
        PTDataProvider::DATETYPE_ARRIVAL => "ARRIVAL"
    ];

    private const ALGORITHMS = [
        PTDataProvider::ALGORITHM_FASTEST => "FASTEST",
        PTDataProvider::ALGORITHM_SHORTEST => "SHORTEST",
        PTDataProvider::ALGORITHM_MINCHANGES => "MINCHANGES"
    ];

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
                $this->getCollectionJourneys($class, $params);
                return $this->collection;
                break;
            case PTTripPoint::class:
                $this->getCollectionTripPoints($class, $params);
                return $this->collection;
                break;
            case PTLineStop::class:
                $this->getCollectionLineStops($class, $params);
                return $this->collection;
                break;
            default:
                break;
        }
    }

    private function getTripModes($modes)
    {
        switch ($modes) {
            case "PT":
                return "PT";
                break;
            case "BIKE":
                return "BIKE";
                break;
            case "CAR":
                return "CAR";
                break;
            case "PT+BIKE":
                return "PT,BIKE";
                break;
            case "PT+CAR":
                return "PT,CAR";
                break;
            default:
                return "PT";
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $class, string $apikey, array $params)
    {
    }


    private function getCollectionJourneys($class, array $params)
    {
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_JOURNEYS);
        $getParams = [
            "DepartureType" => "COORDINATES",
            "ArrivalType" => "COORDINATES",
            "TripModes" => $this->getTripModes($params["modes"]),
            "Algorithm" => self::ALGORITHMS[$params["algorithm"]],
            "Date" => $params["date"]->format(self::DATETIME_INPUT_FORMAT),
            "DateType" => self::DATETYPES[$params["dateType"]],
            "DepartureLatitude" => $params["origin_latitude"],
            "DepartureLongitude" => $params["origin_longitude"],
            "ArrivalLatitude" => $params["destination_latitude"],
            "ArrivalLongitude" => $params["destination_longitude"],
            "UserId" => $params["UserId"],
            "UserRequestRef" => $params["UserId"]
        ];
        $response = $dataProvider->getCollection($getParams);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            if (!isset($data["StatusCode"])) {
                return $this->collection;
            }
            if ($data["StatusCode"] <> 200) {
                return $this->collection;
            }
            if (!isset($data["Data"])) {
                return $this->collection;
            }
            if (!isset($data["Data"][0]["response"])) {
                return $this->collection;
            }

            // Several versions of CityWay api exists we need to check whick on it is.

            // Last versions has "Trace" data to identify which mode is used
            $matchingData = [];
            if (isset($data["Data"][0]['Trace'])) {
                // We keep only the data that matches the given mode
                foreach ($data["Data"] as $currentData) {
                    if (strtoupper($currentData['Trace']) == $this->getTripModes($params["modes"])) {
                        $matchingData[] = $currentData;
                    }
                }
            } else {
                // oldest versions doesn't have "Trace" data. We take the first data array
                $matchingData[] = $data["Data"][0];
            }

            foreach ($matchingData as $currentMatchingData) {
                if (!isset($currentMatchingData["response"]["trips"]["Trip"])) {
                    return $this->collection;
                }
                foreach ($currentMatchingData["response"]["trips"]["Trip"] as $trip) {
                    $this->collection[] = $this->deserialize($class, $trip);
                }
            }
        }
    }

    private function getCollectionTripPoints($class, array $params)
    {
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_TRIPPOINTS);
        $getParams = [
            "TransportModes" => $params["transportModes"],
            "Perimeter" => $params["perimeter"]
        ];


        // First, I check the Lat/Lon. If they are given, we ignore keywords
        if ($params["latitude"]!=0 && $params["longitude"]!=0) {
            $getParams["Latitude"] = $params["latitude"];
            $getParams["Longitude"] = $params["longitude"];
        } else {
            // We assume that we have to use keywords for the search
            $getParams["Keywords"] = $params["keywords"];
        }

        $response = $dataProvider->getCollection($getParams);

        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            if (!isset($data["StatusCode"])) {
                return $this->collection;
            }
            if ($data["StatusCode"] <> 200) {
                return $this->collection;
            }
            if (!isset($data["Data"])) {
                return $this->collection;
            }
            foreach ($data["Data"] as $tripPoint) {
                $this->collection[] = $this->deserialize($class, $tripPoint);
            }
        }
    }

    private function getCollectionLineStops($class, array $params)
    {
        //print_r($params);die;
        $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESSOURCE_LINESTOPS);
        $getParams = [
            "LogicalIds" => $params["logicalId"]
        ];

        if (isset($params["transportModes"]) && $params["transportModes"] !== "") {
            $getParams["TransportModes"] = $params["transportModes"];
        }

        $response = $dataProvider->getCollection($getParams);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            if (!isset($data["StatusCode"])) {
                return $this->collection;
            }
            if ($data["StatusCode"] <> 200) {
                return $this->collection;
            }
            if (!isset($data["Data"])) {
                return $this->collection;
            }
            foreach ($data["Data"] as $lineStop) {
                $this->collection[] = $this->deserialize($class, $lineStop);
            }
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
            case PTTripPoint::class:
                return $this->deserializeTripPoint($data);
                break;
            case PTLineStop::class:
                return $this->deserializeLineStop($data);
                break;
            default:
                break;
        }
    }


    private function deserializeLineStop($data)
    {
        $lineStop = new PTLineStop(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)

        if (isset($data["Direction"])) {
            $lineStop->setDirection($data["Direction"]);
        }

        if (isset($data["Line"]) && isset($data["Line"]["Id"])) {
            $line = $this->deserializeLine($data["Line"]);

            $lineStop->setLine($line);
        }

        if (isset($data["LineId"])) {
            $lineStop->setLineId($data["LineId"]);
        }


        if (isset($data["Stop"]) && isset($data["Stop"]["Id"])) {
            $stop = new PTStop($data["Stop"]["Id"]);

            if (isset($data["Stop"]["Name"])) {
                $stop->setName($data["Stop"]["Name"]);
            }
            if (isset($data["Stop"]["Latitude"])) {
                $stop->setLatitude($data["Stop"]["Latitude"]);
            }
            if (isset($data["Stop"]["Longitude"])) {
                $stop->setLongitude($data["Stop"]["Longitude"]);
            }
            if (isset($data["Stop"]["PointType"])) {
                $stop->setPointType($data["Stop"]["PointType"]);
            }
            if (isset($data["Stop"]["AccessibilityStatus"])) {
                $access = $this->deserializeAccessibilityStatus($data["Stop"]["AccessibilityStatus"]);

                $stop->setAccessibilityStatus($access);
            }
            if (isset($data["Stop"]["IsDisrupted"])) {
                $stop->setIsDisrupted($data["Stop"]["IsDisrupted"]);
            }

            $lineStop->setStop($stop);
        }

        if (isset($data["StopId"])) {
            $lineStop->setStopId($data["StopId"]);
        }




        return $lineStop;
    }


    private function deserializeLine($data)
    {
        $line = new PTLine($data["Id"]);


        if (isset($data["Name"])) {
            $line->setName($data["Name"]);
        }
        if (isset($data["Number"])) {
            $line->setNumber($data["Number"]);
        }
        if (isset($data["LineDirections"][0]["Name"])) {
            $line->setDirection($data["LineDirections"][0]["Name"]);
        }
        if (isset($data["Network"])) {
            if (isset($data["Network"]["Id"])) {
                $ptcompany = new PTCompany($data["Network"]["Id"]);
                $ptcompany->setName($data["Network"]["Name"]);
            }
            $line->setPTCompany($ptcompany);
        }
        if (isset($data["Color"])) {
            $line->setColor($data["Color"]);
        }
        if (isset($data["TransportMode"])) {
            $line->setTransportMode($data["TransportMode"]);
        }

        return $line;
    }

    private function deserializeAccessibilityStatus($data)
    {
        $access = new PTAccessibilityStatus(1);// we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)

        if (isset($data["BlindAccess"])) {
            $access->setBlindAccess($data["BlindAccess"]);
        }
        if (isset($data["DeafAccess"])) {
            $access->setDeafAccess($data["DeafAccess"]);
        }
        if (isset($data["MentalIllnessAccess"])) {
            $access->setMentalIllnessAccess($data["MentalIllnessAccess"]);
        }
        if (isset($data["WheelChairAccess"])) {
            $access->setWheelChairAccess($data["WheelChairAccess"]);
        }

        return $access;
    }

    private function deserializeTripPoint($data)
    {
        $tripPoint = new PTTripPoint();

        $tripPoint->setId($data["Id"]);
        $tripPoint->setLatitude($data["Latitude"]);
        $tripPoint->setLongitude($data["Longitude"]);
        $tripPoint->setLocalityId($data["LocalityId"]);
        $tripPoint->setName($data["Name"]);
        $tripPoint->setPointType($data["PointType"]);
        $tripPoint->setPostalCode($data["PostalCode"]);
        $tripPoint->setTransportMode($data["TransportMode"]);
        if (isset($data["Locality"])) {
            $locality = $this->deserializeLocality($data["Locality"]);
            $tripPoint->setLocality($locality);
        }

        return $tripPoint;
    }

    private function deserializeLocality($data)
    {
        $locality = new PTLocality();

        $locality->setId($data["Id"]);
        $locality->setInseeCode($data["InseeCode"]);
        $locality->setLatitude($data["Latitude"]);
        $locality->setLongitude($data["Longitude"]);
        $locality->setName($data["Name"]);

        return $locality;
    }

    private function deserializeJourney($data)
    {
        $journey = new PTJourney(count($this->collection)+1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if (isset($data["Distance"])) {
            $journey->setDistance($data["Distance"]);
        }
        if (isset($data["Duration"])) {
            $interval = new \DateInterval($data["Duration"]);
            $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
            $journey->setDuration($duration);
        }
        if (isset($data["InterchangeNumber"])) {
            $journey->setChangeNumber($data["InterchangeNumber"]);
        }
        if (isset($data["DepartureTime"])) {
            $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["DepartureTime"]));
            if (isset($data["Departure"]["Site"])) {
                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::CW_COUNTRY);
                $departureAddress->setAddressLocality(self::CW_NC);
                $departureAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Departure"]["Site"]["CityName"]) && !is_null($data["Departure"]["Site"]["CityName"])) {
                    $departureAddress->setAddressLocality($data["Departure"]["Site"]["CityName"]);
                }
                if (isset($data["Departure"]["Site"]["Name"]) && !is_null($data["Departure"]["Site"]["Name"])) {
                    $departureAddress->setStreetAddress($data["Departure"]["Site"]["Name"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Lat"]) && !is_null($data["Departure"]["Site"]["Position"]["Lat"])) {
                    $departureAddress->setLatitude($data["Departure"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Long"]) && !is_null($data["Departure"]["Site"]["Position"]["Long"])) {
                    $departureAddress->setLongitude($data["Departure"]["Site"]["Position"]["Long"]);
                }
                $departure->setAddress($departureAddress);
            }
            $journey->setPTDeparture($departure);
        }
        if (isset($data["ArrivalTime"])) {
            $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["ArrivalTime"]));
            if (isset($data["Arrival"]["Site"])) {
                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                $arrivalAddress->setAddressLocality(self::CW_NC);
                $arrivalAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Arrival"]["Site"]["CityName"]) && !is_null($data["Arrival"]["Site"]["CityName"])) {
                    $arrivalAddress->setAddressLocality($data["Arrival"]["Site"]["CityName"]);
                }
                if (isset($data["Arrival"]["Site"]["Name"]) && !is_null($data["Arrival"]["Site"]["Name"])) {
                    $arrivalAddress->setStreetAddress($data["Arrival"]["Site"]["Name"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Lat"]) && !is_null($data["Arrival"]["Site"]["Position"]["Lat"])) {
                    $arrivalAddress->setLatitude($data["Arrival"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Long"]) && !is_null($data["Arrival"]["Site"]["Position"]["Long"])) {
                    $arrivalAddress->setLongitude($data["Arrival"]["Site"]["Position"]["Long"]);
                }
                $arrival->setAddress($arrivalAddress);
            }
            $journey->setPTArrival($arrival);
        }
        if (isset($data["sections"]["Section"])) {
            //$legs = [];
            $nblegs = 0;
            foreach ($data["sections"]["Section"] as $section) {
                $nblegs++;
                //$legs[] = self::deserializeSection($section, count($legs)+1);
                $journey->addPTLeg($this->deserializeSection($section, $nblegs));
            }
            //$journey->setPTLegs($legs);
        }
        if (isset($data["CarbonFootprint"]["TripCO2"])) {
            $journey->setCo2($data["CarbonFootprint"]["TripCO2"]);
        }
        return $journey;
    }

    private function deserializeSection($data, $num)
    {
        $leg = new PTLeg($num);
        if (isset($data["Leg"]) && !is_null($data["Leg"])) {
            $travelMode = null;
            if ($data["Leg"]["TransportMode"] == self::CW_PT_MODE_WALK) {
                // walk mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_WALK);
                $leg->setTravelMode($travelMode);
            } elseif ($data["Leg"]["TransportMode"] == self::CW_PT_MODE_BIKE) {
                // bike mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BIKE);
                $leg->setTravelMode($travelMode);
            } elseif ($data["Leg"]["TransportMode"] == self::CW_PT_MODE_CAR) {
                // car mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_CAR);
                $leg->setTravelMode($travelMode);
            }

            if (is_null($travelMode)) {
                throw new MassException(MassException::UNKNOWN_TRANSPORT_MODE." ".$data["PTRide"]["TransportMode"]);
            }

            if (isset($data["Leg"]["Duration"]) && !is_null($data["Leg"]["Duration"])) {
                $interval = new \DateInterval($data["Leg"]["Duration"]);
                $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
                $leg->setDuration($duration);
            }
            if (isset($data["Leg"]["Departure"])) {
                $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["Leg"]["Departure"]["Time"])) {
                    $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Leg"]["Departure"]["Time"]));
                }
                if (isset($data["Leg"]["Departure"]["Site"])) {
                    $departureAddress = new Address();
                    $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $departureAddress->setAddressCountry(self::CW_COUNTRY);
                    $departureAddress->setAddressLocality(self::CW_NC);
                    $departureAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["Leg"]["Departure"]["Site"]["CityName"]) && !is_null($data["Leg"]["Departure"]["Site"]["CityName"])) {
                        $departureAddress->setAddressLocality($data["Leg"]["Departure"]["Site"]["CityName"]);
                    }
                    if (isset($data["Leg"]["Departure"]["Site"]["Name"]) && !is_null($data["Leg"]["Departure"]["Site"]["Name"])) {
                        $departure->setName($data["Leg"]["Departure"]["Site"]["Name"]);
                    }
                    if (isset($data["Leg"]["Departure"]["Site"]["Position"]["Lat"]) && !is_null($data["Leg"]["Departure"]["Site"]["Position"]["Lat"])) {
                        $departureAddress->setLatitude($data["Leg"]["Departure"]["Site"]["Position"]["Lat"]);
                    }
                    if (isset($data["Leg"]["Departure"]["Site"]["Position"]["Long"]) && !is_null($data["Leg"]["Departure"]["Site"]["Position"]["Long"])) {
                        $departureAddress->setLongitude($data["Leg"]["Departure"]["Site"]["Position"]["Long"]);
                    }
                    $departure->setAddress($departureAddress);
                }
                $leg->setPTDeparture($departure);
            }
            if (isset($data["Leg"]["Arrival"])) {
                $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["Leg"]["Arrival"]["Time"])) {
                    $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Leg"]["Arrival"]["Time"]));
                }
                if (isset($data["Leg"]["Arrival"]["Site"])) {
                    $arrivalAddress = new Address();
                    $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                    $arrivalAddress->setAddressLocality(self::CW_NC);
                    $arrivalAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["Leg"]["Arrival"]["Site"]["CityName"]) && !is_null($data["Leg"]["Arrival"]["Site"]["CityName"])) {
                        $arrivalAddress->setAddressLocality($data["Leg"]["Arrival"]["Site"]["CityName"]);
                    }
                    if (isset($data["Leg"]["Arrival"]["Site"]["Name"]) && !is_null($data["Leg"]["Arrival"]["Site"]["Name"])) {
                        $arrival->setName($data["Leg"]["Arrival"]["Site"]["Name"]);
                    }
                    if (isset($data["Leg"]["Arrival"]["Site"]["Position"]["Lat"]) && !is_null($data["Leg"]["Arrival"]["Site"]["Position"]["Lat"])) {
                        $arrivalAddress->setLatitude($data["Leg"]["Arrival"]["Site"]["Position"]["Lat"]);
                    }
                    if (isset($data["Leg"]["Arrival"]["Site"]["Position"]["Long"]) && !is_null($data["Leg"]["Arrival"]["Site"]["Position"]["Long"])) {
                        $arrivalAddress->setLongitude($data["Leg"]["Arrival"]["Site"]["Position"]["Long"]);
                    }
                    $arrival->setAddress($arrivalAddress);
                }
                $leg->setPTArrival($arrival);
            }
            if (isset($data["Leg"]["pathLinks"]["PathLink"])) {
                //$ptsteps = [];
                $nbsteps = 0;
                foreach ($data["Leg"]["pathLinks"]["PathLink"] as $pathLink) {
                    $nbsteps++;
                    //$ptsteps[] = self::deserializePTStep($pathLink, count($ptsteps)+1);
                    $leg->addPTStep($this->deserializePTStep($pathLink, $nbsteps));
                }
                //$leg->setPTSteps($ptsteps);
            }
        }
        if (isset($data["PTRide"]) && !is_null($data["PTRide"])) {
            $travelMode=null;
            if ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_BUS) {
                // bus mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_BUS);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TRAMWAY) {
                // tramway mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAMWAY);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_COACH) {
                // coach mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_COACH);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TRAIN) {
                // train local mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TRAIN_LOCAL) {
                // train local mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_LOCAL);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TRAIN_HIGH_SPEED) {
                // train high speed mode
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TRAIN_HIGH_SPEED);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_ON_DEMAND) {
                // transport on demand
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_ON_DEMAND);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_METRO) {
                // Metro
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_METRO);
                $leg->setTravelMode($travelMode);
            } elseif ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TROLLEY_BUS) {
                // Trolley bus
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_TROLLEY_BUS);
                $leg->setTravelMode($travelMode);
            } elseif (is_null($travelMode)) {
                // Unknown
                $travelMode = new TravelMode(TravelMode::TRAVEL_MODE_UNKNOWN);
                $leg->setTravelMode($travelMode);
            }
            if (isset($data["PTRide"]["Departure"])) {
                $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["PTRide"]["Departure"]["Time"])) {
                    $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["PTRide"]["Departure"]["Time"]));
                }
                if (isset($data["PTRide"]["Departure"]["StopPlace"])) {
                    $departureAddress = new Address();
                    $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $departureAddress->setAddressCountry(self::CW_COUNTRY);
                    $departureAddress->setAddressLocality(self::CW_NC);
                    $departureAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["CityName"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["CityName"])) {
                        $departureAddress->setAddressLocality($data["PTRide"]["Departure"]["StopPlace"]["CityName"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Name"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Name"])) {
                        $departure->setName($data["PTRide"]["Departure"]["StopPlace"]["Name"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"])) {
                        $departureAddress->setLatitude($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"])) {
                        $departureAddress->setLongitude($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"]);
                    }
                    $departure->setAddress($departureAddress);
                }
                $leg->setPTDeparture($departure);
            }
            if (isset($data["PTRide"]["Arrival"])) {
                $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["PTRide"]["Arrival"]["Time"])) {
                    $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["PTRide"]["Arrival"]["Time"]));
                }
                if (isset($data["PTRide"]["Arrival"]["StopPlace"])) {
                    $arrivalAddress = new Address();
                    $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                    $arrivalAddress->setAddressLocality(self::CW_NC);
                    $arrivalAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["CityName"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["CityName"])) {
                        $arrivalAddress->setAddressLocality($data["PTRide"]["Arrival"]["StopPlace"]["CityName"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Name"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Name"])) {
                        $arrival->setName($data["PTRide"]["Arrival"]["StopPlace"]["Name"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"])) {
                        $arrivalAddress->setLatitude($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"])) {
                        $arrivalAddress->setLongitude($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"]);
                    }
                    $arrival->setAddress($arrivalAddress);
                }
                $leg->setPTArrival($arrival);
            }
            if (isset($data["PTRide"]["Distance"]) && !is_null($data["PTRide"]["Distance"])) {
                $leg->setDistance($data["PTRide"]["Distance"]);
            }
            if (isset($data["PTRide"]["Duration"]) && !is_null($data["PTRide"]["Duration"])) {
                $interval = new \DateInterval($data["PTRide"]["Duration"]);
                $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
                $leg->setDuration($duration);
            }
            if (isset($data["PTRide"]["Line"])) {
                $ptline = new PTLine(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $ptline->setTravelMode($leg->getTravelMode());
                if (isset($data["PTRide"]["Line"]["Name"])) {
                    $ptline->setName($data["PTRide"]["Line"]["Name"]);
                }
                if (isset($data["PTRide"]["Line"]["Number"])) {
                    $ptline->setNumber($data["PTRide"]["Line"]["Number"]);
                }
                if (isset($data["PTRide"]["Line"]["Direction"]["Name"])) {
                    $leg->setDirection($data["PTRide"]["Line"]["Direction"]["Name"]);
                }

                if (isset($data["PTRide"]["Operator"])) {
                    $ptcompany = new PTCompany($data["PTRide"]["Operator"]["id"]); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    if (isset($data["PTRide"]["PTNetwork"]["Name"])) {
                        $ptcompany->setName($data["PTRide"]["Operator"]["Name"]);
                    }
                    $ptline->setPTCompany($ptcompany);
                }
                $leg->setPTLine($ptline);
            }
            if (isset($data["PTRide"]["Direction"]["Name"])) {
                $leg->setDirection($data["PTRide"]["Direction"]["Name"]);
            }
            if (isset($data["PTRide"]["steps"]["Step"])) {
                //$ptsteps = [];
                $nbsteps = 0;
                foreach ($data["PTRide"]["steps"]["Step"] as $step) {
                    //$ptsteps[] = self::deserializePTStep($step, count($ptsteps)+1);
                    $nbsteps++;
                    $leg->addPTStep($this->deserializePTStep($step, $nbsteps));
                }
                //$leg->setPTSteps($ptsteps);
            }
        }
        return $leg;
    }

    private function deserializePTStep($data, $num)
    {
        $ptstep = new PTStep($num);
        if (isset($data["Departure"])) {
            $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            if (isset($data["Departure"]["Time"])) {
                $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Departure"]["Time"]));
            }
            if (isset($data["Departure"]["Site"])) {
                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::CW_COUNTRY);
                $departureAddress->setAddressLocality(self::CW_NC);
                $departureAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Departure"]["Site"]["CityName"]) && !is_null($data["Departure"]["Site"]["CityName"])) {
                    $departureAddress->setAddressLocality($data["Departure"]["Site"]["CityName"]);
                }
                if (isset($data["Departure"]["Site"]["Name"]) && !is_null($data["Departure"]["Site"]["Name"])) {
                    $departureAddress->setStreetAddress($data["Departure"]["Site"]["Name"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Lat"]) && !is_null($data["Departure"]["Site"]["Position"]["Lat"])) {
                    $departureAddress->setLatitude($data["Departure"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Long"]) && !is_null($data["Departure"]["Site"]["Position"]["Long"])) {
                    $departureAddress->setLongitude($data["Departure"]["Site"]["Position"]["Long"]);
                }
                $departure->setAddress($departureAddress);
            } elseif (isset($data["Departure"]["StopPlace"])) {
                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::CW_COUNTRY);
                $departureAddress->setAddressLocality(self::CW_NC);
                $departureAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Departure"]["StopPlace"]["CityName"]) && !is_null($data["Departure"]["StopPlace"]["CityName"])) {
                    $departureAddress->setAddressLocality($data["Departure"]["StopPlace"]["CityName"]);
                }
                if (isset($data["Departure"]["StopPlace"]["Name"]) && !is_null($data["Departure"]["StopPlace"]["Name"])) {
                    $departureAddress->setStreetAddress($data["Departure"]["StopPlace"]["Name"]);
                }
                if (isset($data["Departure"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["Departure"]["StopPlace"]["Position"]["Lat"])) {
                    $departureAddress->setLatitude($data["Departure"]["StopPlace"]["Position"]["Lat"]);
                }
                if (isset($data["Departure"]["StopPlace"]["Position"]["Long"]) && !is_null($data["Departure"]["StopPlace"]["Position"]["Long"])) {
                    $departureAddress->setLongitude($data["Departure"]["StopPlace"]["Position"]["Long"]);
                }
                $departure->setAddress($departureAddress);
            }
            $ptstep->setPTDeparture($departure);
        }
        if (isset($data["Arrival"])) {
            $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            if (isset($data["Arrival"]["Time"])) {
                $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Arrival"]["Time"]));
            }
            if (isset($data["Arrival"]["Site"])) {
                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                $arrivalAddress->setAddressLocality(self::CW_NC);
                $arrivalAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Arrival"]["Site"]["CityName"]) && !is_null($data["Arrival"]["Site"]["CityName"])) {
                    $arrivalAddress->setAddressLocality($data["Arrival"]["Site"]["CityName"]);
                }
                if (isset($data["Arrival"]["Site"]["Name"]) && !is_null($data["Arrival"]["Site"]["Name"])) {
                    $arrivalAddress->setStreetAddress($data["Arrival"]["Site"]["Name"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Lat"]) && !is_null($data["Arrival"]["Site"]["Position"]["Lat"])) {
                    $arrivalAddress->setLatitude($data["Arrival"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Long"]) && !is_null($data["Arrival"]["Site"]["Position"]["Long"])) {
                    $arrivalAddress->setLongitude($data["Arrival"]["Site"]["Position"]["Long"]);
                }
                $arrival->setAddress($arrivalAddress);
            } elseif (isset($data["Arrival"]["StopPlace"])) {
                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                $arrivalAddress->setAddressLocality(self::CW_NC);
                $arrivalAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Arrival"]["StopPlace"]["CityName"]) && !is_null($data["Arrival"]["StopPlace"]["CityName"])) {
                    $arrivalAddress->setAddressLocality($data["Arrival"]["StopPlace"]["CityName"]);
                }
                if (isset($data["Arrival"]["StopPlace"]["Name"]) && !is_null($data["Arrival"]["StopPlace"]["Name"])) {
                    $arrivalAddress->setStreetAddress($data["Arrival"]["StopPlace"]["Name"]);
                }
                if (isset($data["Arrival"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["Arrival"]["StopPlace"]["Position"]["Lat"])) {
                    $arrivalAddress->setLatitude($data["Arrival"]["StopPlace"]["Position"]["Lat"]);
                }
                if (isset($data["Arrival"]["StopPlace"]["Position"]["Long"]) && !is_null($data["Arrival"]["StopPlace"]["Position"]["Long"])) {
                    $arrivalAddress->setLongitude($data["Arrival"]["StopPlace"]["Position"]["Long"]);
                }
                $arrival->setAddress($arrivalAddress);
            }
            $ptstep->setPTArrival($arrival);
        }
        if (isset($data["Distance"]) && !is_null($data["Distance"])) {
            $ptstep->setDistance($data["Distance"]);
        }
        if (isset($data["Duration"]) && !is_null($data["Duration"])) {
            $interval = new \DateInterval($data["Duration"]);
            $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
            $ptstep->setDuration($duration);
        }
        if (isset($data["MagneticDirection"]) && !is_null($data["MagneticDirection"])) {
            $ptstep->setMagneticDirection($data["MagneticDirection"]);
        }
        if (isset($data["RelativeDirection"]) && !is_null($data["RelativeDirection"])) {
            $ptstep->setRelativeDirection($data["RelativeDirection"]);
        }
        if (isset($data["Geometry"])) {
            $ptstep->setGeometry($data["Geometry"]);
        }
        return $ptstep;
    }
}
