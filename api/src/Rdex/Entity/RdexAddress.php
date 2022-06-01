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
 */

namespace App\Rdex\Entity;

/**
 * An RDEX Address.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexAddress implements \JsonSerializable
{
    /**
     * @var string the address
     */
    private $address;

    /**
     * @var string the city
     */
    private $city;

    /**
     * @var string the postal code
     */
    private $postalcode;

    /**
     * @var string the country
     */
    private $country;

    /**
     * @var float the latitude
     */
    private $latitude;

    /**
     * @var float the longitude
     */
    private $longitude;

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalcode(): string
    {
        return $this->postalcode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLatitude(): number
    {
        return $this->latitude;
    }

    public function getLongitude(): number
    {
        return $this->longitude;
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

    public function jsonSerialize(): mixed
    {
        return
        [
            'address' => $this->getAddress(),
            'city' => $this->getCity(),
            'postalcode' => $this->getPostalcode(),
            'country' => $this->getCountry(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }
}
