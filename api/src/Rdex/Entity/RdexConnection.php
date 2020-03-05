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

namespace App\Rdex\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Rdex\Controller\ConnectionController;

/**
 * An RDEX Connection (conctact a user on a rdex platform)
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "formats"={"xml", "jsonld", "json"},
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "path"="/connections",
 *              "controller"=ConnectionController::class,
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
 *                          "name" = "p[driver][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the driver"
 *                      },
 *                      {
 *                          "name" = "p[driver][state]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "enum" = {"sender","recipient"},
 *                          "description" = "If the driver is the sender or the recipient of this contact"
 *                      },
 *                      {
 *                          "name" = "p[passenger][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the passenger"
 *                      },
 *                      {
 *                          "name" = "p[passenger][state]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "enum" = {"sender","recipient"},
 *                          "description" = "If the passenger is the sender or the recipient of this contact"
 *                      },
 *                      {
 *                          "name" = "p[journeys][uuid]",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "int",
 *                          "description" = "Uuid of the journey"
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
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexConnection implements \JsonSerializable
{
    
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
        ];
    }
}
