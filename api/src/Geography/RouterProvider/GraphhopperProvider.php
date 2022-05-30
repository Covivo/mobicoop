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

namespace App\Geography\RouterProvider;

use App\DataProvider\Entity\Response;
use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Geography\Interfaces\GeorouterInterface;
use App\Geography\Service\GeoTools;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Graphhopper related service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
class GraphhopperProvider implements GeorouterInterface
{
    private const NAME = "GH";
    private const DIRECTION_RESOURCE = "route";
    private const MODE_CAR = "CAR";
    private const PROFILE_NO_TOLL = "car_without_toll";
    private const LOCALE = "fr-FR";
    private const WEIGHTING = "fastest";
    private const INSTRUCTIONS = "false";
    private const POINTS_ENCODED = "true";
    private const ELEVATION = "false";              // NOT SUPPORTED YET
    private const EXT_FILENAME = "gh_multigeo";     // external filename for multiple async treatments

    private $dataProvider;
    private $geoTools;
    private $uri;
    private $detailDuration;
    private $pointsOnly;
    private $avoidMotorway;
    private $avoidToll;
    private $logger;

    private $batchScriptPath;   // batch script path for multiple async treatments
    private $batchScriptArgs;   // batch script args for multiple async treatments
    private $batchTemp;         // batch temp directory

    private $bearing;           // bearing can be common when computing route variants, so it can be calculated once and shared

    private $returnType;        // return type, default : object


    /**
     * Constructor
     *
     * @param string $uri                   The uri of the georouter
     * @param string $batchScriptPath       The path to the external batch script
     * @param string $batchScriptArgs       The args of the external batch script
     * @param string $batchTemp             The temp directory for batch treatments
     * @param LoggerInterface $logger       The logger interface
     * @param boolean $detailDuration       Retrieve the detailed durations
     * @param boolean $pointsOnly           Limit the return to points, not full addresses (used when only latitudes/longitudes are needed)
     * @param boolean $avoidMotorway        Avoid motorways
     * @param boolean $avoidToll            Avoid tolls
     */
    public function __construct(
        string $uri,
        string $batchScriptPath,
        string $batchScriptArgs,
        string $batchTemp,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        bool $detailDuration=false,
        bool $pointsOnly=false,
        bool $avoidMotorway=false,
        bool $avoidToll=false
    ) {
        $this->uri = $uri;
        $this->dataProvider = new DataProvider($this->uri);
        $this->geoTools = new GeoTools($translator);
        $this->directionResource = self::DIRECTION_RESOURCE;
        $this->detailDuration = $detailDuration;
        $this->pointsOnly = $pointsOnly;
        $this->avoidMotorway = $avoidMotorway;
        $this->avoidToll = $avoidToll;
        $this->logger = $logger;
        $this->batchScriptPath = $batchScriptPath;
        $this->batchScriptArgs = $batchScriptArgs;
        $this->batchTemp = $batchTemp;
        $this->setBearing(null);
        $this->returnType = self::RETURN_TYPE_OBJECT;
    }

    /**
     * Get the global bearing
     *
     * @return integer|null
     */
    private function getBearing(): ?int
    {
        return $this->bearing;
    }

    /**
     * Set the global bearing value
     *
     * @param integer|null $bearing
     * @return void
     */
    private function setBearing(?int $bearing): void
    {
        $this->bearing = $bearing;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvoidMotorway(bool $avoidMotorway): void
    {
        $this->avoidMotorway = $avoidMotorway;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvoidToll(bool $avoidToll): void
    {
        $this->avoidToll = $avoidToll;
    }

    /**
     * {@inheritdoc}
     */
    public function setDetailDuration(bool $detailDuration): void
    {
        $this->detailDuration = $detailDuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setPointsOnly(bool $pointsOnly): void
    {
        $this->pointsOnly = $pointsOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function setReturnType(int $returnType): void
    {
        $this->returnType = $returnType;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleDirections(array $multiPoints, int $mode): array
    {
        $routes = [];
        $this->dataProvider->setResource(self::DIRECTION_RESOURCE);
        switch ($mode) {
            case self::MODE_SYNC:
                // unsupported
                break;
            
            case self::MODE_ASYNC:
            {
                $getParams = [];
                // we have to send multiple requests to the georouter, we need to know the 'owner' of each request to give him the result
                // but the owner is not sent with the request, so we need to keep it in a dedicated array => each key of the request will be associated to its owner
                // so after the requests we will be able to know who is the owner
                $requestsOwner = [];
                $i = 0;
                foreach ($multiPoints as $ownerId => $addresses) {
                    foreach ($addresses as $points) {
                        $params = "";
                        foreach ($points as $address) {
                            $params .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                        }
                        if (!$this->avoidToll) {
                            $params .= "locale=" . self::LOCALE .
                            "&vehicle=" . self::MODE_CAR .
                            "&weighting=" . self::WEIGHTING .
                            "&instructions=" . self::INSTRUCTIONS .
                            "&points_encoded=".self::POINTS_ENCODED .
                            ($this->detailDuration?'&details=time':'').
                            "&elevation=" . self::ELEVATION;
                        } else {
                            $params .= "locale=" . self::LOCALE .
                            "&profile=" . self::PROFILE_NO_TOLL . "&ch.disable=true" .
                            "&instructions=" . self::INSTRUCTIONS .
                            "&points_encoded=".self::POINTS_ENCODED .
                            ($this->detailDuration?'&details=time':'').
                            "&elevation=" . self::ELEVATION;
                        }
                        $getParams[$i] = $params;
                        $requestsOwner[$i] = $ownerId;
                        $i++;
                    }
                }
                $response = $this->dataProvider->getAsyncCollection($getParams);
                foreach ($response->getValue() as $key=>$value) {
                    $data = json_decode($value, true);
                    foreach ($data["paths"] as $path) {
                        switch ($this->returnType) {
                            case self::RETURN_TYPE_OBJECT:
                                $routes[$requestsOwner[$key]][] = $this->deserializeDirection($path);
                                break;
                            case self::RETURN_TYPE_ARRAY:
                                // unsupported !
                                break;
                            case self::RETURN_TYPE_RAW:
                                $routes[$requestsOwner[$key]][] = $path;
                                break;
                        }
                    }
                }
                break;
            }
            case self::MODE_MULTIPLE_ASYNC:
            {
                // MULTIPLE ASYNC : we will use an external script instead of the usual dataProvider
                $this->logger->debug('Multiple Async');

                gc_enable();
                $urls = [];
                // we have to send multiple requests to the georouter, we need to know the 'owner' of each request to give him the result
                // but the owner is not sent with the request, so we need to keep it in a dedicated array => each key of the request will be associated to its owner
                // so after the requests we will be able to know who is the owner
                $requestsOwner = [];
                $i = 0;
                $this->print_mem(1);
                foreach ($multiPoints as $ownerId => $directionVariants) {
                    foreach ($directionVariants as $addresses) {
                        $rparams = $this->uri ."/" . self::DIRECTION_RESOURCE . "/?";
                        foreach ($addresses as $address) {
                            $rparams .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                            $address = null;
                            unset($address);
                        }
                        if (!$this->avoidToll) {
                            $rparams .= "locale=" . self::LOCALE .
                            "&vehicle=" . self::MODE_CAR .
                            "&weighting=" . self::WEIGHTING .
                            "&instructions=" . self::INSTRUCTIONS .
                            "&points_encoded=".self::POINTS_ENCODED .
                            ($this->detailDuration?'&details=time':'').
                            "&elevation=" . self::ELEVATION;
                        } else {
                            $rparams .= "locale=" . self::LOCALE .
                            "&profile=" . self::PROFILE_NO_TOLL . "&ch.disable=true" .
                            "&instructions=" . self::INSTRUCTIONS .
                            "&points_encoded=".self::POINTS_ENCODED .
                            ($this->detailDuration?'&details=time':'').
                            "&elevation=" . self::ELEVATION;
                        }
                        $urls[$i] = $rparams;
                        $requestsOwner[$i] = $ownerId;
                        $i++;
                        $addresses = null;
                        unset($addresses);
                    }
                }
                // $this->print_mem(2);

                // creation of the file that represent all the routes to get
                $this->logger->debug('Multiple Async | Creation of the exchange file start for ' . $i . ' routes');
                $filename = $this->batchTemp . self::EXT_FILENAME . (new \DateTime("UTC"))->format("YmdHisu") . ".json";
                $fp = fopen($filename, 'w');
                fwrite($fp, json_encode($urls, JSON_FORCE_OBJECT));
                fclose($fp);
                $urls = null;
                $fp = null;
                unset($urls);
                unset($fp);
                // $this->print_mem(3);

                $this->logger->debug('Multiple Async | Creation of the exchange file end');

                // call external script
                $this->logger->debug('Multiple Async | Call external script start : ' . $this->batchScriptPath . $filename . $this->batchScriptArgs . ' 2>&1 | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                $return = exec($this->batchScriptPath . $filename . $this->batchScriptArgs . ' 2>&1', $out, $err);
                // $filenameReturn = $filename . ".log";
                // $fpr = fopen($filenameReturn, 'w');
                // fwrite($fpr, print_r($out, true));
                // fwrite($fpr, 'status : ' . $err);
                // fclose($fpr);
                $this->logger->debug('Multiple Async | Call external script end | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                // treat the response
                // $this->print_mem(4);

                // note : we use JsonMachine as it's way more efficient than json_decode, but be careful the resulting object is an Iterable, not a Countable => only foreach possible !
                $response = \JsonMachine\JsonMachine::fromFile($filename);

                // $this->print_mem(5);

                switch ($this->returnType) {
                    case self::RETURN_TYPE_OBJECT:
                    {
                        $this->logger->debug('Multiple Async | Start deserialize routes | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        foreach ($response as $key=>$paths) {
                            if (is_array($paths)) {
                                foreach ($paths as $path) {
                                    $routes[$requestsOwner[$key]][] = $this->deserializeDirection($path);
                                }
                            }
                        }
                        $this->logger->debug('Multiple Async | End deserialize routes | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        break;
                    }
                    case self::RETURN_TYPE_ARRAY:
                    {
                        $this->logger->debug('Multiple Async | Start treat array routes | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        foreach ($response as $key=>$paths) {
                            // we search the first and last elements for the bearing
                            reset($multiPoints[$requestsOwner[$key]][0]);
                            $first_key = key($multiPoints[$requestsOwner[$key]][0]);
                            end($multiPoints[$requestsOwner[$key]][0]);
                            $last_key = key($multiPoints[$requestsOwner[$key]][0]);
                            if (is_array($paths)) {
                                foreach ($paths as $path) {
                                    $routes[$requestsOwner[$key]][] = [
                                        'distance' => isset($path["distance"]) ? $path["distance"] : null,
                                        'duration' => isset($path["time"]) ? $path["time"]/1000 : null,
                                        'details' => isset($path["details"]["time"]) ? ['time'=>$path["details"]["time"]] : null,
                                        'points' => isset($path["points"]) ? $path["points"] : null,
                                        'snapped_waypoints' => isset($path["snapped_waypoints"]) ? $path["snapped_waypoints"] : null,
                                        'bbox' => isset($path["bbox"]) ? [$path["bbox"][0],$path["bbox"][1],$path["bbox"][2],$path["bbox"][3]] : null,
                                        'bearing' => $this->geoTools->getRhumbLineBearing(
                                            $multiPoints[$requestsOwner[$key]][0][$first_key]->getLatitude(),
                                            $multiPoints[$requestsOwner[$key]][0][$first_key]->getLongitude(),
                                            $multiPoints[$requestsOwner[$key]][0][$last_key]->getLatitude(),
                                            $multiPoints[$requestsOwner[$key]][0][$last_key]->getLongitude()
                                        )
                                    ];
                                    $path=null;
                                    unset($path);
                                }
                            }
                        }
                        $this->logger->debug('Multiple Async | End treat array routes | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $paths = null;
                        unset($paths);
                        break;
                    }
                    case self::RETURN_TYPE_RAW:
                    {
                        foreach ($response as $key=>$paths) {
                            if (is_array($paths)) {
                                foreach ($paths as $path) {
                                    $routes[$requestsOwner[$key]][] = $path;
                                }
                            }
                        }
                        break;
                    }
                }
                
                // $this->print_mem(6);
                foreach ($requestsOwner as $owner) {
                    $owner = null;
                    unset($owner);
                }
                $requestsOwner = null;
                unset($requestsOwner);
                foreach ($multiPoints as $point) {
                    $point = null;
                    unset($point);
                }
                $multiPoints = null;
                unset($multiPoints);
                $response = null;
                unset($response);
                gc_collect_cycles();
                $this->print_mem(7);
                $this->logger->debug('Multiple Async | Exchange file deletion | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                unlink($filename);
                break;
            }
        }
        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirections(array $points, int $mode): array
    {
        $routes = [];
        $this->dataProvider->setResource(self::DIRECTION_RESOURCE);
        $response = null;
        switch ($mode) {
            case self::MODE_SYNC:
                $getParams = "";
                foreach ($points as $address) {
                    $getParams .= "point=" . $address->getLatitude() . "," . $address->getLongitude() . "&";
                }
                if (!$this->avoidToll) {
                    $getParams .= "locale=" . self::LOCALE .
                    "&vehicle=" . self::MODE_CAR .
                    "&weighting=" . self::WEIGHTING .
                    "&instructions=" . self::INSTRUCTIONS .
                    "&points_encoded=".self::POINTS_ENCODED .
                    ($this->detailDuration?'&details=time':'').
                    "&elevation=" . self::ELEVATION;
                } else {
                    $getParams .= "locale=" . self::LOCALE .
                    "&profile=" . self::PROFILE_NO_TOLL . "&ch.disable=true" .
                    "&instructions=" . self::INSTRUCTIONS .
                    "&points_encoded=".self::POINTS_ENCODED .
                    ($this->detailDuration?'&details=time':'').
                    "&elevation=" . self::ELEVATION;
                }
                $this->bearing = $this->geoTools->getRhumbLineBearing($points[0]->getLatitude(), $points[0]->getLongitude(), $points[count($points)-1]->getLatitude(), $points[count($points)-1]->getLongitude());
                $response = $this->dataProvider->getCollection($getParams);
                if ($response instanceof Response && $response->getCode() == 200) {
                    $data = json_decode($response->getValue(), true);
                    foreach ($data["paths"] as $path) {
                        switch ($this->returnType) {
                            case self::RETURN_TYPE_OBJECT:
                                $routes[] = $this->deserializeDirection($path);
                                break;
                            case self::RETURN_TYPE_ARRAY:
                                // unsupported !
                                break;
                            case self::RETURN_TYPE_RAW:
                                $routes[] = $path;
                                break;
                        }
                    }
                }
                break;
            case self::MODE_ASYNC:
                // unsupported
                break;
            case self::MODE_MULTIPLE_ASYNC:
                // unsupported
                break;
        }
        return $routes;
    }
   
    /**
     * {@inheritdoc}
     */
    public function deserializeDirection(array $data): Direction
    {
        $direction = new Direction();
        if (isset($data["distance"])) {
            $direction->setDistance($data["distance"]);
        }
        if (isset($data["time"])) {
            // time is in milliseconds, we transform in seconds
            $direction->setDuration($data["time"]/1000);
        } elseif (isset($data["duration"])) {
            // maybe we already treated the time to create the duration, can be the case if we returned an array
            $direction->setDuration($data["duration"]);
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
            //$direction->setDetail($data["points"]);
            if (!$this->pointsOnly) {
                $direction->setPoints($this->deserializePoints($data['points']));
            } else {
                $direction->setDirectPoints($this->deserializePoints($data['points']));
            }
        }
        if (isset($data['snapped_waypoints'])) {
            // we keep the encoded AND the decoded snapped waypoints (all the waypoints used to define the direction : start point, intermediate points, end point)
            // the decoded snapped waypoints are not stored in the database
            $direction->setSnapped($data["snapped_waypoints"]);
            $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints']));
        }
        $direction->setFormat(self::NAME);
        if (!is_null($this->getBearing())) {
            $direction->setBearing($this->getBearing()); // already calculated
        } else {
            $direction->setBearing($this->geoTools->getRhumbLineBearing($direction->getPoints()[0]->getLatitude(), $direction->getPoints()[0]->getLongitude(), $direction->getPoints()[count($direction->getPoints())-1]->getLatitude(), $direction->getPoints()[count($direction->getPoints())-1]->getLongitude()));
        }

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
                $direction->setPoints($this->deserializePoints($data['points']));
            } else {
                $direction->setPoints($this->deserializePoints($data['points']));
            }
        }
        if (isset($data['snapped_waypoints'])) {
            if (isset($data['points_encoded']) && $data['points_encoded'] === false) {
                $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints']));
            } else {
                $direction->setSnappedWaypoints($this->deserializePoints($data['snapped_waypoints']));
            }
        }*/

        return $direction;
    }

    /**
     * {@inheritdoc}
     */
    public function deserializePoints(string $data)
    {
        return $this->deserializeGHPoints($data, true, filter_var(self::ELEVATION, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Deserializes geographical points to Addresses.
     *
     * @param mixed $data       The data to deserialize
     * @param bool $encoded     Data encoded
     * @param bool $is3D        Data has elevation information
     * @return Address[]        The deserialized Addresses
     */
    private function deserializeGHPoints($data, bool $encoded, bool $is3D): array
    {
        $addresses = [];
        if ($encoded) {
            if ($coordinates = self::decodePath($data, $is3D)) {
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
    
    private function createAddress($coordinate)
    {
        if (!$this->pointsOnly) {
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
        } else {
            $address = [];
            if (isset($coordinate[0])) {
                $address['lon'] = $coordinate[0];
            }
            if (isset($coordinate[1])) {
                $address['lat'] = $coordinate[1];
            }
            if (isset($coordinate[2])) {
                $address['elv'] = $coordinate[2];
            }
        }
        return $address;
    }

    private function print_mem($id)
    {
        /* Currently used memory */
        $mem_usage = memory_get_usage();
        
        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();
        $this->logger->debug($id . ' The script is now using: ' . round($mem_usage / 1024) . 'KB of memory.');
        $this->logger->debug($id . ' Peak usage: ' . round($mem_peak / 1024) . 'KB of memory.');
    }
}
