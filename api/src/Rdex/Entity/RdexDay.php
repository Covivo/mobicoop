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
 * An RDEX Day.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/days/{id}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDay
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var bool The state for monday.
     *
     * @Groups("rdex")
     */
    private $monday;
    
    /**
     * @var bool The state for tuesday.
     *
     * @Groups("rdex")
     */
    private $tuesday;
    
    /**
     * @var bool The state for wednesday.
     *
     * @Groups("rdex")
     */
    private $wednesday;
    
    /**
     * @var bool The state for thursday.
     *
     * @Groups("rdex")
     */
    private $thursday;
    
    /**
     * @var bool The state for friday.
     *
     * @Groups("rdex")
     */
    private $friday;
    
    /**
     * @var bool The state for saturday.
     *
     * @Groups("rdex")
     */
    private $saturday;
    
    /**
     * @var bool The state for sunday.
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
     * @return boolean
     */
    public function getMonday()
    {
        return $this->monday;
    }

    /**
     * @return boolean
     */
    public function getTuesday()
    {
        return $this->tuesday;
    }

    /**
     * @return boolean
     */
    public function getWednesday()
    {
        return $this->wednesday;
    }

    /**
     * @return boolean
     */
    public function getThursday()
    {
        return $this->thursday;
    }

    /**
     * @return boolean
     */
    public function getFriday()
    {
        return $this->friday;
    }

    /**
     * @return boolean
     */
    public function getSaturday()
    {
        return $this->saturday;
    }

    /**
     * @return boolean
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
     * @param boolean $monday
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;
    }

    /**
     * @param boolean $tuesday
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;
    }

    /**
     * @param boolean $wednesday
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;
    }

    /**
     * @param boolean $thursday
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;
    }

    /**
     * @param boolean $friday
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;
    }

    /**
     * @param boolean $saturday
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;
    }

    /**
     * @param boolean $sunday
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;
    }
}