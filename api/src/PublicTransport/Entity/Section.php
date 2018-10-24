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

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A section of a journey.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class Section
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The indication of this section.
     *
     * @Groups("pt")
     */
    private $indication;
    
    /**
     * @var int The distance of this section.
     *
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var int The position of this section.
     *
     * @Groups("pt")
     */
    private $pos;
    
    /**
     * @var bool The section is the last section of the journey.
     *
     * @Groups("pt")
     */
    private $last;
    
    /**
     * @var Journey The parent journey of this section.
     *
     * @Groups("pt")
     */
    private $journey;
    
    /**
     * @var Departure The departure of this section.
     *
     * @Groups("pt")
     */
    private $departure;
    
    /**
     * @var Arrival The arrival of this section.
     *
     * @Groups("pt")
     */
    private $arrival;
    
    /**
     * @var PTMode The transport mode of this section.
     *
     * @Groups("pt")
     */
    private $ptmode;
    
    /**
     * @var PTLine The public transport line of this section.
     *
     * @Groups("pt")
     */
    private $ptline;
    
    /**
     * @var int The direction of the public transport line of this section.
     *
     * @Groups("pt")
     */
    private $direction;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPos($id);
    }
    
    public function getId ()
    {
        return $this->id;
    }
    
    public function setId ($id)
    {
        $this->id = $id;
    }
    
    public function getIndication ()
    {
        return $this->indication;
    }

    public function getDistance ()
    {
        return $this->distance;
    }

    public function getPos ()
    {
        return $this->pos;
    }

    public function isLast ()
    {
        return $this->last;
    }

    public function getJourney ()
    {
        return $this->journey;
    }

    public function getDeparture ()
    {
        return $this->departure;
    }

    public function getArrival ()
    {
        return $this->arrival;
    }

    public function getPtmode ()
    {
        return $this->ptmode;
    }

    public function getPtline ()
    {
        return $this->ptline;
    }

    public function getDirection ()
    {
        return $this->direction;
    }

    public function setIndication ($indication)
    {
        $this->indication = $indication;
    }

    public function setDistance ($distance)
    {
        $this->distance = $distance;
    }

    public function setPos ($pos)
    {
        $this->pos = $pos;
    }

    public function setLast ($last)
    {
        $this->last = $last;
    }

    public function setJourney ($journey)
    {
        $this->journey = $journey;
    }

    public function setDeparture ($departure)
    {
        $this->departure = $departure;
    }

    public function setArrival ($arrival)
    {
        $this->arrival = $arrival;
    }

    public function setPtmode ($ptmode)
    {
        $this->ptmode = $ptmode;
    }

    public function setPtline ($ptline)
    {
        $this->ptline = $ptline;
    }

    public function setDirection ($direction)
    {
        $this->direction = $direction;
    }
    
}