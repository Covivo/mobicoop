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

namespace Mobicoop\Bundle\MobicoopBundle\Entity;

/**
 * A public transport step (by walk or public transport).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTStep
{
    /**
     * @var int The id of this step.
     */
    private $id;
    
    /**
     * @var int The distance of this step.
     */
    private $distance;
    
    /**
     * @var int The duration of this step.
     */
    private $duration;
    
    /**
     * @var int The position of this step.
     */
    private $pos;
    
    /**
     * @var bool The step is the last step of the section.
     */
    private $last;
    
    /**
     * @var string The magnetic direction of this section.
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this section.
     */
    private $relativeDirection;
   
    /**
     * @var PTLeg The parent section of this step.
     */
    private $ptleg;
    
    /**
     * @var PTDeparture The departure of this step.
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this step.
     */
    private $ptarrival;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPos($id);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getDistance()
    {
        return $this->distance;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getPos()
    {
        return $this->pos;
    }

    public function isLast()
    {
        return $this->last;
    }

    public function getPTLeg()
    {
        return $this->ptleg;
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

    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    public function setLast($last)
    {
        $this->last = $last;
    }

    public function setPTLeg($ptleg)
    {
        $this->ptleg = $ptleg;
    }

    public function setPTDeparture($ptdeparture)
    {
        $this->ptdeparture = $ptdeparture;
    }

    public function setPTArrival($ptarrival)
    {
        $this->ptarrival = $ptarrival;
    }

    public function getMagneticDirection()
    {
        return $this->magneticDirection;
    }
    
    public function getRelativeDirection()
    {
        return $this->relativeDirection;
    }
    
    public function setMagneticDirection($magneticDirection)
    {
        $this->magneticDirection = $magneticDirection;
    }
    
    public function setRelativeDirection($relativeDirection)
    {
        $this->relativeDirection = $relativeDirection;
    }
}
