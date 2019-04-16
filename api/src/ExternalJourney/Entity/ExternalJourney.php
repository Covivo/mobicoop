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

namespace App\ExternalJourney\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * An external journey.
 *
 * @ApiResource(
 *     collectionOperations={"get"={
 *      "swagger_context"={
 *           "parameters"={
 *              {
 *                  "name" = "provider_name",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "name of the provider"
 *              },
 *              {
 *                  "name" = "driver",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "1 if you want to display drivers journeys, 0 instead"
 *              },
 *              {
 *                  "name" = "passenger",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "1 if you want to display passengers journeys, 0 instead"
 *              },
 *              {
 *                  "name" = "from_latitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Latitude of the origin point"
 *              },
 *              {
 *                  "name" = "from_longitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Longitude of the origin point"
 *              },
 *              {
 *                  "name" = "to_latitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Latitude of the destination point"
 *              },
 *              {
 *                  "name" = "to_longitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Longitude of the destination point"
 *              }
 *           }
 *      }
 *    }},
 *      itemOperations={}
 * )
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 */
class ExternalJourney
{
    /**
    * @var int $id of
    */
    private $id;

    private $providerName;
    private $driver;
    private $passenger;
    private $fromLatitude;
    private $fromLongitude;
    private $toLatitude;
    private $toLongitude;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getProviderName()
    {
        return $this->provider_name;
    }

    public function setProviderName($providerName)
    {
        return $this->providerName = $providerName;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setDriver($driver)
    {
        return $this->driver= $driver;
    }

    public function getPassenger()
    {
        return $this->passenger;
    }

    public function setPassenger($passenger)
    {
        return $this->passenger= $passenger;
    }

    public function getFromLatitude()
    {
        return $this->fromLatitude;
    }

    public function setFromLatitude($fromLatitude)
    {
        return $this->fromLatitude= $fromLatitude;
    }

    public function getFromLongitude()
    {
        return $this->fromLongitude;
    }

    public function setFromLongitude($fromLongitude)
    {
        return $this->fromLongitude= $fromLongitude;
    }

    public function getToLatitude()
    {
        return $this->toLatitude;
    }

    public function setToLatitude($toLatitude)
    {
        return $this->toLatitude= $toLatitude;
    }

    public function getToLongitude()
    {
        return $this->toLongitude;
    }

    public function setToLongitude($toLongitude)
    {
        return $this->toLongitude= $toLongitude;
    }
}
