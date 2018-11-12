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
 * An RDEX Cost.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/costs/{id}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexCost
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var float The fixed cost.
     *
     * @Groups("rdex")
     */
    private $fixed;
    
    /**
     * @var float The variable cost.
     *
     * @Groups("rdex")
     */
    private $variable;
    
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
     * @return number
     */
    public function getFixed()
    {
        return $this->fixed;
    }

    /**
     * @return number
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param number $fixed
     */
    public function setFixed($fixed)
    {
        $this->fixed = $fixed;
    }

    /**
     * @param number $variable
     */
    public function setVariable($variable)
    {
        $this->variable = $variable;
    }
}