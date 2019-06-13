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
use App\Geography\Service\GeoTools;
use Psr\Log\LoggerInterface;

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
    private const COLLECTION_RESOURCE = "route";
    private const GR_MODE_CAR = "CAR";
    private const GR_LOCALE = "fr-FR";
    private const GR_WEIGHTING = "fastest";
    private const GR_INSTRUCTIONS = "false";
    private const GR_POINTS_ENCODED = "true";
    public const GR_ELEVATION = "false";           // NOT SUPPORTED YET

    private const EXT_FILENAME = "multigeo";
    
    private $collection;
    private $uri;
    private $detailDuration;
    private $geoTools;
    private $bearing;           // bearing will be common to all routes, we will calculate it once for all and share the value
    private $logger;
    
    /**
     * Constructor.
     *
     * @param string $uri               The uri of the provider
     * @param boolean $detailDuration   Set to true to get the duration between 2 points
     */
    public function __construct(string $uri=null, bool $detailDuration=false, GeoTools $geoTools, LoggerInterface $logger)
    {
        $this->uri = $uri;
        $this->bearing = 0;
        $this->detailDuration = $detailDuration;
        $this->collection = [];
        $this->geoTools = $geoTools;
        $this->logger = $logger;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
        switch ($class) {
            case Direction::class:
                $dataProvider = new DataProvider($this->uri, self::COLLECTION_RESOURCE);
                if (isset($params['async']) && $params['async'] && isset($params['arrayPoints'])) {
                    // ASYNC 
                    $getParams = [];
                    // we have to send multiple requests to the georouter, we need to know the 'owner' of each request to give him the result
                    // but the owner is not sent with the request, so we need to keep it in a dedicated array => each key of the request will be associated to its owner
                    // so after the requests we will be able to know who is the owner
                    $requestsOwner = [];
                    $i = 0;
                    foreach ($params['arrayPoints'] as $ownerId => $addresses) {
                        foreach ($addresses as $points) {
                            $params = "";
                            foreach ($points as $address) {
                                $params .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                            }
                            $params .= "locale=" . self::GR_LOCALE .
                                "&vehicle=" . self::GR_MODE_CAR .
                                "&weighting=" . self::GR_WEIGHTING .
                                "&instructions=" . self::GR_INSTRUCTIONS .
                                "&points_encoded=".self::GR_POINTS_ENCODED .
                                ($this->detailDuration?'&details=time':'').
                                "&elevation=" . self::GR_ELEVATION;
                            $getParams[$i] = $params;
                            $requestsOwner[$i] = $ownerId;
                            $i++;
                        }
                    }
                    $response = $dataProvider->getAsyncCollection($getParams);
                    foreach ($response->getValue() as $key=>$value) {
                        $data = json_decode($value, true);
                        foreach ($data["paths"] as $path) {
                            $this->collection[$requestsOwner[$key]][] = self::deserialize($class, $path);
                        }
                    }
                    return $this->collection;
                } elseif (
                    isset($params['multipleAsync']) && $params['multipleAsync'] && 
                    isset($params['batchScriptPath']) && 
                    isset($params['batchTemp']) && 
                    isset($params['arrayPoints'])) {
                    // MULTIPLE ASYNC : we will use an external script instead of the usual dataProvider
                    $this->logger->debug('Multiple Async');
                    $urls = [];
                    // we have to send multiple requests to the georouter, we need to know the 'owner' of each request to give him the result
                    // but the owner is not sent with the request, so we need to keep it in a dedicated array => each key of the request will be associated to its owner
                    // so after the requests we will be able to know who is the owner
                    $requestsOwner = [];
                    $i = 0;
                    foreach ($params['arrayPoints'] as $ownerId => $addresses) {
                        foreach ($addresses as $points) {
                            $rparams = $this->uri ."/" . self::COLLECTION_RESOURCE . "/?";
                            foreach ($points as $address) {
                                $rparams .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                            }
                            $rparams .= "locale=" . self::GR_LOCALE .
                                "&vehicle=" . self::GR_MODE_CAR .
                                "&weighting=" . self::GR_WEIGHTING .
                                "&instructions=" . self::GR_INSTRUCTIONS .
                                "&points_encoded=".self::GR_POINTS_ENCODED .
                                ($this->detailDuration?'&details=time':'').
                                "&elevation=" . self::GR_ELEVATION;
                            $urls[$i] = $rparams;
                            $requestsOwner[$i] = $ownerId;
                            $i++;
                        }
                    }

                    // creation of the file that represent all the routes to get
                    $this->logger->debug('Multiple Async | Creation of the exchange file start');
                    $filename = $params['batchTemp'] . self::EXT_FILENAME . (new \DateTime("UTC"))->format("YmdHisu") . ".json";
                    $fp = fopen($filename, 'w');
                    fwrite($fp, json_encode($urls,JSON_FORCE_OBJECT));
                    fclose($fp);
                    $this->logger->debug('Multiple Async | Creation of the exchange file end');

                    // call external script
                    $this->logger->debug('Multiple Async | Call external script start');
                    $return = exec($params['batchScriptPath'].' -f ' . $filename . ' --nb 20 2>&1', $out, $err);
                    $this->logger->debug('Multiple Async | Call external script end');
                    // treat the response
                    $response = \JsonMachine\JsonMachine::fromFile($filename);
                    foreach ($response as $key=>$paths) {
                        $this->logger->debug('Multiple Async | Treating path #'.$key);
                        // we search the first and last elements for the bearing
                        reset($params['arrayPoints'][$key][0]);
                        $first_key = key($params['arrayPoints'][$key][0]);
                        end($params['arrayPoints'][$key][0]);
                        $last_key = key($params['arrayPoints'][$key][0]);
                        foreach ($paths as $path) {
                            // we return an array instead of an object
                            $this->collection[$requestsOwner[$key]][] = [
                                'distance' => isset($path["distance"]) ? $path["distance"] : null,
                                'duration' => isset($path["time"]) ? $path["time"]/1000 : null,
                                'bbox' => isset($path["bbox"]) ? [$path["bbox"][0],$path["bbox"][1],$path["bbox"][2],$path["bbox"][3]] : null,
                                'bearing' => $this->geoTools->getRhumbLineBearing(
                                    $params['arrayPoints'][$key][0][$first_key]->getLatitude(), 
                                    $params['arrayPoints'][$key][0][$first_key]->getLongitude(), 
                                    $params['arrayPoints'][$key][0][$last_key]->getLatitude(), 
                                    $params['arrayPoints'][$key][0][$last_key]->getLongitude())
                            ];
                        }
                    }
                    $this->logger->debug('Multiple Async | Exchange file deletion');
                    unlink($filename);
                    return $this->collection;
                    
                } else {
                    // SYNC
                    $getParams = "";
                    foreach ($params['points'] as $address) {
                        $getParams .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                    }
                    $getParams .= "locale=" . self::GR_LOCALE .
                        "&vehicle=" . self::GR_MODE_CAR .
                        "&weighting=" . self::GR_WEIGHTING .
                        "&instructions=" . self::GR_INSTRUCTIONS .
                        "&points_encoded=".self::GR_POINTS_ENCODED .
                        ($this->detailDuration?'&details=time':'').
                        "&elevation=" . self::GR_ELEVATION;
                    $response = $dataProvider->getCollection($getParams);
                    $this->bearing = $this->geoTools->getRhumbLineBearing($params['points'][0]->getLatitude(), $params['points'][0]->getLongitude(), $params['points'][count($params['points'])-1]->getLatitude(), $params['points'][count($params['points'])-1]->getLongitude());
                }
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
            // time is in milliseconds, we transform in seconds
            $direction->setDuration($data["time"]/1000);
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
        if (isset($data["points"])) {
            // we keep the encoded AND the decoded points (all the points of the path returned by the SIG)
            // the decoded points are not stored in the database
            $direction->setDetail($data["points"]);
            $direction->setPoints($this->deserializePoints($data['points'], true, filter_var(self::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
        }
        if (isset($data['snapped_waypoints'])) {
            // we keep the encoded AND the decoded snapped waypoints (all the waypoints used to define the direction : start point, intermediate points, end point)
            // the decoded snapped waypoints are not stored in the database
            $direction->setSnapped($data["snapped_waypoints"]);
            $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints'], true, false));
        }
        $direction->setFormat('graphhopper');
        $direction->setBearing($this->bearing); // already calculated

        if ($this->detailDuration && isset($data["details"]["time"])) {
            // if we get the detail of duration between points, we can get the duration between the waypoints
            // the duration between points is set like this in the response :
            // [fromPointNumber,toPointNumber,duration], eg : [4,5,20150] means from point 4 to point 5 : 20150 seconds
            // first we have to search for the position of the waypoints in the points
            $waypoints = [];
            $waypointsFound = [];
            $numpoint = 0;
            foreach ($direction->getPoints() as $point) {
                foreach ($direction->getSnappedWaypoints() as $key=>$waypoint) {
                    if (in_array($waypoint, $waypointsFound, true)) {
                        continue;
                    }
                    if ($point->getLongitude() == $waypoint->getLongitude() && $point->getLatitude() == $waypoint->getLatitude()) {
                        // we have found a waypoint in the points
                        $waypoints[$key] = $numpoint;
                        $waypointsFound[] = $waypoint;
                        if (count($waypoints) == count($direction->getSnappedWaypoints())) {
                            break(2);
                        }
                        break;
                    }
                }
                $numpoint++;
            }
            // if we missed some waypoints, we search the closest point with a second loop
            $missed = [];
            if (count($waypoints) < count($direction->getSnappedWaypoints())) {
                // we search the missed waypoints
                foreach ($direction->getSnappedWaypoints() as $key=>$waypoint) {
                    if (!in_array($waypoint, $waypointsFound, true)) {
                        $missed[$key] = [
                            'waypoint' => $waypoint,
                            'nearest' => null,
                            'distance' => 9999999999
                        ];
                    }
                }
                // we search the closest point
                $numpoint = 0;
                foreach ($direction->getPoints() as $point) {
                    foreach ($missed as $key=>$waypoint) {
                        $distance = $this->geoTools->haversineGreatCircleDistance($waypoint['waypoint']->getLatitude(), $waypoint['waypoint']->getLongitude(), $point->getLatitude(), $point->getLongitude());
                        if ($distance<$waypoint['distance']) {
                            $missed[$key]['distance'] = $distance;
                            $missed[$key]['nearest'] = $numpoint;
                        }
                    }
                    $numpoint++;
                }
                // we affected the closest point to the waypoint
                foreach ($missed as $key=>$waypoint) {
                    $waypoints[$key] = $waypoint['nearest'];
                }
            }

            // then we search the duration between the waypoints
            $durations = [];
            $duration = 0;
            foreach ($data["details"]["time"] as $time) {
                list($fromRef, $toRef, $value) = $time;
                $set = false;
                // if fromRef refers to a waypoint, we set it to the current duration
                if (in_array($fromRef, $waypoints)) {
                    $durations[array_search($fromRef, $waypoints)] = [
                        // time is in milliseconds, we transform in seconds
                        "duration" => $duration/1000,
                        "approx_duration" => false,
                        "approx_point" => array_key_exists(array_search($fromRef, $waypoints), $missed)
                    ];
                    $set = true;
                }
                // if toRef refers to a waypoint, we set it to the current duration
                if (in_array($toRef, $waypoints)) {
                    $durations[array_search($toRef, $waypoints)] = [
                        // time is in milliseconds, we transform in seconds
                        "duration" => ($duration+$value)/1000,
                        "approx_duration" => false,
                        "approx_point" => array_key_exists(array_search($toRef, $waypoints), $missed)
                    ];
                    $set = true;
                }
                if (!$set) {
                    // no waypoint found as a fromRef or toRef, we search if it's in between
                    // it's an approximative duration
                    foreach ($waypoints as $key=>$waypoint) {
                        if ($fromRef<$waypoint && $waypoint<$toRef) {
                            $durations[$key] = [
                                // time is in milliseconds, we transform in seconds
                                "duration" => ($duration+($value/2))/1000,
                                "approx_duration" => true,
                                "approx_point" => true
                            ];
                            break;
                        }
                    }
                }
                $duration += $value;
            }
            
            $direction->setDurations($durations);
        }

        // use the following code if the points are not encoded
        /*if (isset($data['points'])) {
            if (isset($data['points_encoded']) && $data['points_encoded'] === false) {
                $direction->setPoints($this->deserializePoints($data['points'], false, filter_var(self::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
            } else {
                $direction->setPoints($this->deserializePoints($data['points'], true, filter_var(self::GR_ELEVATION, FILTER_VALIDATE_BOOLEAN)));
            }
        }
        if (isset($data['snapped_waypoints'])) {
            if (isset($data['points_encoded']) && $data['points_encoded'] === false) {
                $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints'], false, false));
            } else {
                $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints'], true, false));
            }
        }*/

        return $direction;
    }
    
    /**
     * Deserializes geographical points to Addresses.
     *
     * @param string $data      The data to deserialize
     * @param bool $encoded     Data encoded
     * @param bool $is3D        Data has elevation information
     * @return Address[]        The deserialized Addresses
     */
    public static function deserializePoints(string $data, bool $encoded, bool $is3D)
    {
        $addresses = [];
        if ($encoded) {
            if ($coordinates = self::decodePath($data, $is3D)) {
                foreach ($coordinates as $coordinate) {
                    $addresses[] = self::createAddress($coordinate);
                }
            }
        } elseif (isset($data['coordinates'])) {
            foreach ($data['coordinates'] as $coordinate) {
                $addresses[] = self::createAddress($coordinate);
            }
        }
        return $addresses;
    }
    
    // Graphhopper path decoding function
    // This function is transposed from the JS function found in the points_encoded doc
    // (see https://github.com/graphhopper/graphhopper/blob/0.11/docs/web/api-doc.md)
    private static function decodePath($encoded, $is3D)
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
    
    private static function charCodeAt($str, $i)
    {
        return ord(substr($str, $i, 1));
    }
    
    private static function createAddress($coordinate)
    {
        $address = new Address();
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
