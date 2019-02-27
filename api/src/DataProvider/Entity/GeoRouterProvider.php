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
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;

/**
 * GeoRouter data provider : provides route calculation between 2 or more addresses.
 *
 * Implements all the methods needed to retrieve data from the Graphhopper GeoRouter :
 * - get collection and item
 * - deserialize to populate Geography Route entity
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class GeoRouterProvider implements ProviderInterface
{
    private const URI = "http://149.202.205.240:8989/";
    private const COLLECTION_RESOURCE = "route";
    private const GR_MODE_CAR = "CAR";
    private const GR_LOCALE = "fr-FR";
    private const GR_WEIGHTING = "fastest";
    private const GR_INSTRUCTIONS = "false";
    private const GR_POINTS_ENCODED = "true";
    private const GR_ELEVATION = "false";           // NOT SUPPORTED YET
    
    private $collection;
    
    public function __construct()
    {
        $this->collection = [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
        switch ($class) {
            case Direction::class:
                $dataProvider = new DataProvider(self::URI, self::COLLECTION_RESOURCE);
                $getParams = "";
                foreach ($params['points'] as $address) {
                    $getParams .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                }
                $getParams .= "locale=" . self::GR_LOCALE .
                    "&vehicle=" . self::GR_MODE_CAR .
                    "&weighting=" . self::GR_WEIGHTING .
                    "&instructions=" . self::GR_INSTRUCTIONS .
                    "&points_encoded=".self::GR_POINTS_ENCODED .
                    "&elevation=" . self::GR_ELEVATION;
                $response = $dataProvider->getCollection($getParams);
                if ($response->getCode() == 200) {
                    $data = json_decode($response->getValue(), true);
                    foreach ($data["paths"] as $path) {
                        $this->collection[] = self::deserialize($class, $path);
                    }
                    return $this->collection;
                }
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
            case Direction::class:
                return self::deserializePath($data);
                break;
            default:
                break;
        }
    }
    
    private function deserializePath($data)
    {
        $direction = new Direction();
        if (isset($data["distance"])) {
            $direction->setDistance($data["distance"]);
        }
        if (isset($data["time"])) {
            $direction->setDuration($data["time"]);
        }
        if (isset($data["ascend"])) {
            $direction->setAscend($data["ascend"]);
        }
        if (isset($data["descend"])) {
            $direction->setDescend($data["descend"]);
        }
        if (isset($data["bbox"])) {
            if (isset($data["bbox"][0])) {
                $direction->setBboxMinLon($data["bbox"][0]);
            }
            if (isset($data["bbox"][1])) {
                $direction->setBboxMinLat($data["bbox"][1]);
            }
            if (isset($data["bbox"][2])) {
                $direction->setBboxMaxLon($data["bbox"][2]);
            }
            if (isset($data["bbox"][3])) {
                $direction->setBboxMaxLat($data["bbox"][3]);
            }
        }
        /*if (isset($data['points'])) {
            if (isset($data['points_encoded']) && $data['points_encoded'] === false) {
                $direction->setPoints($this->deserializePoints($data['points'], false, filter_var(self::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
            } else {
                $direction->setPoints($this->deserializePoints($data['points'], true, filter_var(self::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
            }
        }
        if (isset($data['snapped_waypoints'])) {
            if (isset($data['points_encoded']) && $data['points_encoded'] === false) {
                $direction->setWaypoints($this->deserializePoints($data['snapped_waypoints'], false, false));
            } else {
                $direction->setWaypoints($this->deserializePoints($data['snapped_waypoints'], true, false));
            }
        }*/
        return $direction;
    }
    
    private function deserializePoints($data, $encoded, $is3D)
    {
        $addresses = [];
        if ($encoded) {
            if ($coordinates = $this->decodePath($data, $is3D)) {
                foreach ($coordinates as $coordinate) {
                    $addresses[] = $this->createAddress($coordinate);
                }
            }
        } elseif (isset($data['coordinates'])) {
            foreach ($data['coordinates'] as $coordinate) {
                $addresses[] = $this->createAddress($coordinate);
            }
        }
        return $addresses;
    }
    
    // Graphhopper path decoding function
    // This function is transposed from the JS function found in the points_encoded doc
    // (see https://github.com/graphhopper/graphhopper/blob/0.11/docs/web/api-doc.md)
    private function decodePath($encoded, $is3D)
    {
        $length = strlen($encoded);
        $index = 0;
        $decoded = [];
        $latitude = 0;
        $longitude = 0;
        $elevation = 0;
        
        while ($index < $length) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = self::charCodeAt($encoded, $index++) - 63;
                $result = $result | ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $deltaLatitude = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $latitude += $deltaLatitude;
            
            $shift = 0;
            $result = 0;
            do {
                $b = self::charCodeAt($encoded, $index++) - 63;
                $result = $result | ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $deltaLongitude = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $longitude += $deltaLongitude;
            
            if ($is3D) {
                $shift = 0;
                $result = 0;
                do {
                    $b = self::charCodeAt($encoded, $index++) - 63;
                    $result = $result | ($b & 0x1f) << $shift;
                    $shift += 5;
                } while ($b >= 0x20);
                $deltaElevation = (($result & 1) ? ~($result >> 1) : ($result >> 1));
                $elevation += $deltaElevation;
                $decoded[] = [
                    $longitude * 1e-5,
                    $latitude * 1e-5,
                    $elevation/100
                ];
            } else {
                $decoded[] = [
                    $longitude * 1e-5,
                    $latitude * 1e-5
                ];
            }
        }
        return $decoded;
    }
    
    private function charCodeAt($str, $i)
    {
        return ord(substr($str, $i, 1));
    }
    
    private function createAddress($coordinate)
    {
        $address = new Address(1);
        if (isset($coordinate[0])) {
            $address->setLongitude($coordinate[0]);
        }
        if (isset($coordinate[1])) {
            $address->setLatitude($coordinate[1]);
        }
        if (isset($coordinate[2])) {
            $address->setElevation($coordinate[2]);
        }
        return $address;
    }
}
