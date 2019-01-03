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
 * A public transport journey.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTJourney
{
    /**
     * @var int The id of this journey.
     */
    private $id;
    
    /**
     * @var int The total distance of this journey.
     */
    private $distance;
    
    /**
     * @var int The total duration of this journey.
     */
    private $duration;

    /**
     * @var int The number of changes of this journey.
     */
    private $changeNumber;
    
    /**
     * @var float The estimated price of this journey.
     */
    private $price;
   
    /**
     * @var int The estimated CO2 emission of this journey.
     */
    private $co2;
    
    /**
     * @var PTDeparture The departure of this journey.
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this journey.
     */
    private $ptarrival;
    
    /**
     * @var PTLeg[] The legs of this journey.
     */
    private $ptlegs;

    public function __construct()
    {
        $this->ptlegs = new ArrayCollection();
    }
    
    public function getDistance()
    {
        return $this->distance;
    }

    public function getDuration()
    {
        return $this->duration;
    }
    
    public function getChangeNumber()
    {
        return $this->changeNumber;
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
    
    public function setChangeNumber($changeNumber)
    {
        $this->changeNumber = $changeNumber;
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
    
    public function getPTLegs()
    {
        return $this->ptlegs;
    }

    public function setPTLegs($ptlegs)
    {
        $this->ptlegs = $ptlegs;
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
