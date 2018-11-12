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
 * An RDEX Address.
 *
 * @ApiResource(
 *      routePrefix="/rdex",
 *      attributes={
 *          "normalization_context"={"groups"={"rdex"}, "enable_max_depth"="true"}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/addresses/{id}"}}
 * )
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexAddress
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The address.
     *
     * @Groups("rdex")
     */
    private $address;

    /**
     * @var string The city.
     *
     * @Groups("rdex")
     */
    private $city;
    
    /**
     * @var string The postal code.
     *
     * @Groups("rdex")
     */
    private $postalcode;
    
    /**
     * @var string The country.
     *
     * @Groups("rdex")
     */
    private $country;
    
    /**
     * @var float The latitude.
     *
     * @Groups("rdex")
     */
    private $latitude;
    
    /**
     * @var float The longitude.
     *
     * @Groups("rdex")
     */
    private $longitude;
    
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return number
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return number
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $postalcode
     */
    public function setPostalcode($postalcode)
    {
        $this->postalcode = $postalcode;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @param number $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @param number $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
}