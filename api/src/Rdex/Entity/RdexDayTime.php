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
 * An RDEX Day time.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/day_time/{id}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDayTime
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The min time.
     *
     * @Groups("rdex")
     */
    private $mintime;
    
    /**
     * @var string The max time.
     *
     * @Groups("rdex")
     */
    private $maxtime;
    
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
     * @return string
     */
    public function getMintime()
    {
        return $this->mintime;
    }

    /**
     * @return string
     */
    public function getMaxtime()
    {
        return $this->maxtime;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $mintime
     */
    public function setMintime($mintime)
    {
        $this->mintime = $mintime;
    }

    /**
     * @param string $maxtime
     */
    public function setMaxtime($maxtime)
    {
        $this->maxtime = $maxtime;
    }    
}