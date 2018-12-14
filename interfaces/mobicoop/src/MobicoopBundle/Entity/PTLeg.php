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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * A leg of a journey.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLeg
{
    /**
     * @var int The id of this leg.
     */
    private $id;
    
    /**
     * @var string The indication of this leg.
     */
    private $indication;
    
    /**
     * @var int The distance of this leg.
     */
    private $distance;
    
    /**
     * @var int The duration of this leg.
     */
    private $duration;
    
    /**
     * @var int The position of this leg.
     */
    private $pos;
    
    /**
     * @var bool The leg is the last leg of the journey.
     */
    private $last;
    
    /**
     * @var string The magnetic direction of this leg.
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this leg.
     */
    private $relativeDirection;
    
    /**
     * @var PTJourney The parent journey of this leg.
     */
    private $ptjourney;
    
    /**
     * @var PTDeparture The departure of this leg.
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this leg.
     */
    private $ptarrival;
    
    /**
     * @var PTMode The transport mode of this leg.
     */
    private $ptmode;
    
    /**
     * @var PTLine The public transport line of this leg.
     */
    private $ptline;
    
    /**
     * @var string The direction of the public transport line of this leg.
     */
    private $direction;
    
    /**
     * @var PTStep[] The steps of this leg.
     */
    private $ptsteps;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPos($id);
        $this->ptsteps = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getIndication()
    {
        return $this->indication;
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

    public function getPTJourney()
    {
        return $this->ptjourney;
    }

    public function getPTDeparture()
    {
        return $this->ptdeparture;
    }

    public function getPTArrival()
    {
        return $this->ptarrival;
    }

    public function getPTMode()
    {
        return $this->ptmode;
    }

    public function getPTLine()
    {
        return $this->ptline;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setIndication($indication)
    {
        $this->indication = $indication;
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

    public function setPTJourney($ptjourney)
    {
        $this->ptjourney = $ptjourney;
    }

    public function setPTDeparture($ptdeparture)
    {
        $this->ptdeparture = $ptdeparture;
    }

    public function setPTArrival($ptarrival)
    {
        $this->ptarrival = $ptarrival;
    }

    public function setPTMode($ptmode)
    {
        $this->ptmode = $ptmode;
    }

    public function setPTLine($ptline)
    {
        $this->ptline = $ptline;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
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
    
    public function getPTSteps()
    {
        return $this->ptsteps;
    }

    public function setPTSteps($ptsteps)
    {
        $this->ptsteps = $ptsteps;
    }
}
