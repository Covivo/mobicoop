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
 * A public transport line.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLine
{
    /**
     * @var int The id of this line.
     */
    private $id;
    
    /**
     * @var string The name of this line.
     */
    private $name;
    
    /**
     * @var string The number of this line.
     */
    private $number;
    
    /**
     * @var string The origin of this line.
     */
    private $origin;
    
    /**
     * @var string The destination of this line.
     */
    private $destination;
    
    /**
     * @var PTCompany The company that manage this line.
     */
    private $ptcompany;
        
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getNumber()
    {
        return $this->number;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function getPTCompany()
    {
        return $this->ptcompany;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function setPTCompany($ptcompany)
    {
        $this->ptcompany = $ptcompany;
    }
}
