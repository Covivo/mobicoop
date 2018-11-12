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

/**
 * An RDEX Trip Date.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/trip_dates/{id}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexTripDate
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var \DateTime The min date.
     *
     * @Groups("rdex")
     */
    private $mindate;
    
    /**
     * @var \DateTime The max date.
     *
     * @Groups("rdex")
     */
    private $maxdate;
    
    /**
     * @var RdexDayTime The day time for monday.
     *
     * @Groups("rdex")
     */
    private $monday;
    
    /**
     * @var RdexDayTime The day time for tuesday.
     *
     * @Groups("rdex")
     */
    private $tuesday;
    
    /**
     * @var RdexDayTime The day time for wednesday.
     *
     * @Groups("rdex")
     */
    private $wednesday;
    
    /**
     * @var RdexDayTime The day time for thursday.
     *
     * @Groups("rdex")
     */
    private $thursday;
    
    /**
     * @var RdexDayTime The day time for friday.
     *
     * @Groups("rdex")
     */
    private $friday;
    
    /**
     * @var RdexDayTime The day time for saturday.
     *
     * @Groups("rdex")
     */
    private $saturday;
    
    /**
     * @var RdexDayTime The day time for sunday.
     *
     * @Groups("rdex")
     */
    private $sunday;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getMindate()
    {
        return $this->mindate;
    }

    /**
     * @return \DateTime
     */
    public function getMaxdate()
    {
        return $this->maxdate;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getMonday()
    {
        return $this->monday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getTuesday()
    {
        return $this->tuesday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getWednesday()
    {
        return $this->wednesday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getThursday()
    {
        return $this->thursday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getFriday()
    {
        return $this->friday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getSaturday()
    {
        return $this->saturday;
    }

    /**
     * @return \App\Rdex\Entity\RdexDayTime
     */
    public function getSunday()
    {
        return $this->sunday;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param \DateTime $mindate
     */
    public function setMindate($mindate)
    {
        $this->mindate = $mindate;
    }

    /**
     * @param \DateTime $maxdate
     */
    public function setMaxdate($maxdate)
    {
        $this->maxdate = $maxdate;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $monday
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $tuesday
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $wednesday
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $thursday
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $friday
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $saturday
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;
    }

    /**
     * @param \App\Rdex\Entity\RdexDayTime $sunday
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;
    }
}