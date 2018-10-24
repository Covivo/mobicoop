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
use ApiPlatform\Core\Annotation\ApiFilter;
use App\PublicTransport\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport journey.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 * @ApiFilter(SearchFilter::class, properties={"provider","apikey","origin_latitude","origin_longitude","destination_latitude","destination_longitude","date"})
 */
class Journey
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
     * @var Departure The departure of this journey.
     *
     * @Groups("pt")
     */
    private $departure;
    
    /**
     * @var Arrival The arrival of this journey.
     *
     * @Groups("pt")
     */
    private $arrival;
    
    /**
     * @var Section[] The sections of this journey.
     *
     * @Groups("pt")
     */
    private $sections;
    
    private $provider;
    private $apikey;
    private $origin_latitude;
    private $origin_longitude;
    private $destination_latitude;
    private $destination_longitude;
    private $date;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->sections = new ArrayCollection();
    }
    
    public function getDistance ()
    {
        return $this->distance;
    }

    public function getDuration ()
    {
        return $this->duration;
    }

    public function getPrice ()
    {
        return $this->price;
    }

    public function getCo2 ()
    {
        return $this->co2;
    }
    
    public function getDeparture ()
    {
        return $this->departure;
    }
    
    public function getArrival ()
    {
        return $this->arrival;
    }
    
    public function setDistance ($distance)
    {
        $this->distance = $distance;
    }

    public function setDuration ($duration)
    {
        $this->duration = $duration;
    }
    
    public function setPrice ($price)
    {
        $this->price = $price;
    }

    public function setCo2 ($co2)
    {
        $this->co2 = $co2;
    }
    
    public function setDeparture ($departure)
    {
        $this->departure = $departure;
    }
    
    public function setArrival ($arrival)
    {
        $this->arrival = $arrival;
    }
    
    public function getSections ()
    {
        return $this->sections;
    }

    public function getOrigin_latitude ()
    {
        return $this->origin_latitude;
    }

    public function getOrigin_longitude ()
    {
        return $this->origin_longitude;
    }

    public function getDestination_latitude ()
    {
        return $this->destination_latitude;
    }

    public function getDestination_longitude ()
    {
        return $this->destination_longitude;
    }

    public function getDate ()
    {
        return $this->date;
    }

    public function setSections ($sections)
    {
        $this->sections = $sections;
    }

    public function setOrigin_latitude ($origin_latitude)
    {
        $this->origin_latitude = $origin_latitude;
    }
    
    public function setOrigin_longitude ($origin_longitude)
    {
        $this->origin_longitude = $origin_longitude;
    }
    
    public function setDestination_latitude ($destination_latitude)
    {
        $this->destination_latitude = $destination_latitude;
    }
    
    public function setDestination_longitude ($destination_longitude)
    {
        $this->destination_longitude = $destination_longitude;
    }

    public function setDate ($date)
    {
        $this->date = $date;
    }
    
    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        $this->id = $id;
    }
    
    public function getProvider ()
    {
        return $this->provider;
    }

    public function getApikey ()
    {
        return $this->apikey;
    }

    public function setProvider ($provider)
    {
        $this->provider = $provider;
    }

    public function setApikey ($apikey)
    {
        $this->apikey = $apikey;
    }
    
}