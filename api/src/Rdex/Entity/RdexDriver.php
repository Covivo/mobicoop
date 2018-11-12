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
 * An RDEX Driver.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/drivers/{uuid}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDriver
{
    /**
     * @ApiProperty(identifier=true)
     * 
     * @var string The uuid of the driver.
     * 
     * @Groups("rdex")
     */
    private $uuid;
    
    /**
     * @var string The pseudonym of the driver.
     *
     * @Groups("rdex")
     */
    private $alias;
    
    /**
     * @var string The image of the driver.
     *
     * @Groups("rdex")
     */
    private $image;
    
    /**
     * @var string The gender of the driver.
     *
     * @Groups("rdex")
     */
    private $gender;
    
    /**
     * @var int The number of available seats.
     *
     * @Groups("rdex")
     */
    private $seats;
    
    /**
     * @var bool The state of the driver.
     *
     * @Groups("rdex")
     */
    private $state;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
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
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }
    
    /**
     * @return number
     */
    public function getSeats()
    {
        return $this->seats;
    }

    /**
     * @return boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
    
    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @param number $seats
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;
    }

    /**
     * @param boolean $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    
}