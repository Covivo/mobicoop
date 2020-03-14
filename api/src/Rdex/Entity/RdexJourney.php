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

namespace App\Rdex\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Rdex\Controller\JourneyCollectionController;

/**
 * An RDEX Journey.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "formats"={"xml", "jsonld", "json"},
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/journeys",
 *              "controller"=JourneyCollectionController::class,
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "timestamp",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The timestamp"
 *                      },
 *                      {
 *                          "name" = "apikey",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The api key"
 *                      },
 *                      {
 *                          "name" = "p[driver]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Search for drivers"
 *                      },
 *                      {
 *                          "name" = "p[passenger]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Search for passengers"
 *                      },
 *                      {
 *                          "name" = "p[from][longitude]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Origin longitude"
 *                      },
 *                      {
 *                          "name" = "p[from][latitude]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Origin latitude"
 *                      },
 *                      {
 *                          "name" = "p[to][longitude]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Destination longitude"
 *                      },
 *                      {
 *                          "name" = "p[to][latitude]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "Destination latitude"
 *                      },
 *                      {
 *                          "name" = "signature",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The signature"
 *                      },
 *                  },
 *              },
 *          }
 *      },
 *      itemOperations={}
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexJourney implements \JsonSerializable
{
    const FREQUENCY_PUNCTUAL = "punctual";
    const FREQUENCY_REGULAR = "regular";

    const TYPE_ONE_WAY = "one-way";
    const TYPE_ROUND_TRIP = "round-trip";
    
    /**
     * @ApiProperty(identifier=true)
     *
     * @var string The uuid of the journey.
     *
     * @Groups("rdex")
     */
    private $uuid;
    
    /**
     * @var string The name of the operator.
     *
     * @Groups("rdex")
     */
    private $operator;
    
    /**
     * @var string The url of the site.
     *
     * @Groups("rdex")
     */
    private $origin;
    
    /**
     * @var string The url of the ad.
     *
     * @Groups("rdex")
     */
    private $url;
    
    /**
     * @var RdexDriver The driver.
     *
     * @Groups("rdex")
     */
    private $driver;
    
    /**
     * @var RdexPassenger The passenger.
     *
     * @Groups("rdex")
     */
    private $passenger;
    
    /**
     * @var RdexAddress The origin of the ride.
     *
     * @Groups("rdex")
     */
    private $from;
    
    /**
     * @var RdexAddress The destination of the ride.
     *
     * @Groups("rdex")
     */
    private $to;
    
    /**
     * @var int The distance of the ride.
     *
     * @Groups("rdex")
     */
    private $distance;
    
    /**
     * @var int The duration of the ride.
     *
     * @Groups("rdex")
     */
    private $duration;
    
    /**
     * @var string The route.
     *
     * @Groups("rdex")
     */
    private $route;
    
    /**
     * @var int The number of waypoints.
     *
     * @Groups("rdex")
     */
    private $number_of_waypoints;
    
    /**
     * @var RdexWaypoint[] The waypoints.
     *
     * @Groups("rdex")
     */
    private $waypoints;
    
    /**
     * @var RdexCost The cost of the ride.
     *
     * @Groups("rdex")
     */
    private $cost;
    
    /**
     * @var string A comment about the ride.
     *
     * @Groups("rdex")
     */
    private $details;
    
    /**
     * @var RdexVehicle The vehicle.
     *
     * @Groups("rdex")
     */
    private $vehicle;
    
    /**
     * @var string The frequency of the ride.
     *
     * @Groups("rdex")
     */
    private $frequency;
    
    /**
     * @var string The type of the ride.
     *
     * @Groups("rdex")
     */
    private $type;
    
    /**
     * @var bool The ride is a realtime ride.
     *
     * @Groups("rdex")
     */
    private $real_time;
    
    /**
     * @var bool The ride is stopped.
     *
     * @Groups("rdex")
     */
    private $stopped;
    
    /**
     * @var RdexDay The days of the ride.
     *
     * @Groups("rdex")
     */
    private $days;
    
    /**
     * @var RdexTripDate The date details of the outward trip.
     *
     * @Groups("rdex")
     */
    private $outward;
    
    /**
     * @var RdexTripDate The date details of the return trip.
     *
     * @Groups("rdex")
     */
    private $return;
    
    public function __construct($uuid)
    {
        $this->uuid = $uuid;
        $this->waypoints = new ArrayCollection();
    }
    
    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return \App\Rdex\Entity\RdexDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return \App\Rdex\Entity\RdexPassenger
     */
    public function getPassenger()
    {
        return $this->passenger;
    }

    /**
     * @return \App\Rdex\Entity\RdexAddress
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \App\Rdex\Entity\RdexAddress
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return number
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return number
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return number
     */
    public function getNumberOfWaypoints()
    {
        return $this->number_of_waypoints;
    }

    /**
     * @return multitype:\App\Rdex\Entity\RdexWaypoint
     */
    public function getWaypoints()
    {
        return $this->waypoints;
    }

    /**
     * @return \App\Rdex\Entity\RdexCost
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return \App\Rdex\Entity\RdexVehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isRealTime()
    {
        return $this->real_time;
    }

    /**
     * @return boolean
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * @return \App\Rdex\Entity\RdexDay
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return \App\Rdex\Entity\RdexTripDate
     */
    public function getOutward()
    {
        return $this->outward;
    }

    /**
     * @return \App\Rdex\Entity\RdexTripDate
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param \App\Rdex\Entity\RdexDriver $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param \App\Rdex\Entity\RdexPassenger $passenger
     */
    public function setPassenger($passenger)
    {
        $this->passenger = $passenger;
    }

    /**
     * @param \App\Rdex\Entity\RdexAddress $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param \App\Rdex\Entity\RdexAddress $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @param number $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * @param number $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @param number $number_of_waypoints
     */
    public function setNumberOfWaypoints($number_of_waypoints)
    {
        $this->number_of_waypoints = $number_of_waypoints;
    }

    /**
     * @param multitype:\App\Rdex\Entity\Waypoint  $waypoints
     */
    public function setWaypoints($waypoints)
    {
        $this->waypoints = $waypoints;
    }

    /**
     * @param \App\Rdex\Entity\RdexCost $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @param string $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @param \App\Rdex\Entity\RdexVehicle $vehicle
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @param string $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param boolean $real_time
     */
    public function setRealTime($real_time)
    {
        $this->real_time = $real_time;
    }

    /**
     * @param boolean $stopped
     */
    public function setStopped($stopped)
    {
        $this->stopped = $stopped;
    }

    /**
     * @param \App\Rdex\Entity\RdexDay $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }

    /**
     * @param \App\Rdex\Entity\RdexTripDate $outward
     */
    public function setOutward($outward)
    {
        $this->outward = $outward;
    }

    /**
     * @param \App\Rdex\Entity\RdexTripDate $return
     */
    public function setReturn($return)
    {
        $this->return = $return;
    }
    
    public function jsonSerialize()
    {
        return
        [
            'uuid'      => $this->getUuid(),
            'operator'  => $this->getOperator(),
            'origin'    => $this->getOrigin(),
            'url'       => $this->getUrl(),
            'driver'    => $this->getDriver(),
            'passenger' => $this->getPassenger(),
            'from'      => $this->getFrom(),
            'to'        => $this->getTo(),
            'distance'  => $this->getDistance(),
            'duration'  => $this->getDuration(),
            'route'     => $this->getRoute(),
            'number_of_waypoints'   => $this->getNumberOfWaypoints(),
            'waypoints' => $this-> getWaypoints(),
            'cost'      => $this->getCost(),
            'details'   => $this->getDetails() ,
            'vehicle'   => $this->getVehicle(),
            'frequency' => $this->getFrequency(),
            'type'      => $this->getType(),
            'real_time' => $this->isRealTime(),
            'stopped'   => $this->isStopped(),
            'days'      => $this->getDays(),
            'outward'   => $this->getOutward(),
            'return'    => $this->getReturn()
        ];
    }
}
