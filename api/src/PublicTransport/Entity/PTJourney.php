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

namespace App\PublicTransport\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport journey.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "provider",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The public transport data provider"
 *                      },
 *                      {
 *                          "name" = "apikey",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The api key for the data provider"
 *                      },
 *                      {
 *                          "name" = "origin_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "origin_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "destination_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "destination_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "date",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "format" = "date-time",
 *                          "description" = "The date of the trip (on RFC3339 format)"
 *                      },
 *                      {
 *                          "name" = "dateType",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The date type of the trip",
 *                          "enum" = {
 *                              "departure",
 *                              "arrival"
 *                          }
 *                      },
 *                  },
 *              }
 *          }
 *      },
 *      itemOperations={"get"}
 * )
 */
class PTJourney
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var int The total distance of this journey.
     *
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var int The total duration of this journey.
     *
     * @Groups("pt")
     */
    private $duration;
    
    /**
     * @var float The estimated price of this journey.
     *
     * @Groups("pt")
     */
    private $price;
   
    /**
     * @var int The estimated CO2 emission of this journey.
     *
     * @Groups("pt")
     */
    private $co2;
    
    /**
     * @var PTDeparture The departure of this journey.
     *
     * @Groups("pt")
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this journey.
     *
     * @Groups("pt")
     */
    private $ptarrival;
    
    /**
     * @var PTSection[] The sections of this journey.
     *
     * @Groups("pt")
     */
    private $ptsections;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->ptsections = new ArrayCollection();
    }
    
    public function getDistance()
    {
        return $this->distance;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCo2()
    {
        return $this->co2;
    }
    
    public function getPTDeparture()
    {
        return $this->ptdeparture;
    }
    
    public function getPTArrival()
    {
        return $this->ptarrival;
    }
    
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
    
    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setCo2($co2)
    {
        $this->co2 = $co2;
    }
    
    public function setPTDeparture($ptdeparture)
    {
        $this->ptdeparture = $ptdeparture;
    }
    
    public function setPTArrival($ptarrival)
    {
        $this->ptarrival = $ptarrival;
    }
    
    public function getPTSections()
    {
        return $this->ptsections;
    }

    public function setPTSections($ptsections)
    {
        $this->ptsections = $ptsections;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
