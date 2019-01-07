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

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity;

use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * A departure.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTDeparture
{
    
    /**
     * @var int The id of this departure.
     */
    private $id;
    
    /**
     * @var string The name of this departure.
     */
    private $name;
    
    /**
     * @var \DateTime The date and time of this departure.
     */
    private $date;
   
    /**
     * @var Address The address of this departure.
     */
    private $address;
        
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

    public function getDate()
    {
        return $this->date;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }
}
