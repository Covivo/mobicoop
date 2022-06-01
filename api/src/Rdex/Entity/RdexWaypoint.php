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

/**
 * An RDEX Waypoint.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexWaypoint implements \JsonSerializable
{
    public const TYPE_PICK_UP = "pick-up";
    public const TYPE_DROP_OFF = "drop-off";

    /**
     * @var string The address.
     */
    private $address;

    /**
     * @var string The city.
     */
    private $city;

    /**
     * @var string The postal code.
     */
    private $postalcode;

    /**
     * @var string The country.
     */
    private $country;

    /**
     * @var float The latitude.
     */
    private $latitude;

    /**
     * @var float The longitude.
     */
    private $longitude;

    /**
     * @var int The distance of the step.
     */
    private $step_distance;

    /**
     * @var int The duration of the step.
     */
    private $step_duration;

    /**
     * @var string The type of the step.
     */
    private $type;

    /**
     * @var bool The step is mandatory.
     */
    private $mandatory;

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostalcode(): string
    {
        return $this->postalcode;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return number
     */
    public function getLatitude(): number
    {
        return $this->latitude;
    }

    /**
     * @return number
     */
    public function getLongitude(): number
    {
        return $this->longitude;
    }

    /**
     * @return number
     */
    public function getStep_distance(): number
    {
        return $this->step_distance;
    }

    /**
     * @return number
     */
    public function getStep_duration(): number
    {
        return $this->step_duration;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
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

    /**
     * @param number $step_distance
     */
    public function setStep_distance($step_distance)
    {
        $this->step_distance = $step_distance;
    }

    /**
     * @param number $step_duration
     */
    public function setStep_duration($step_duration)
    {
        $this->step_duration = $step_duration;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param boolean $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'address'       => $this->getAddress(),
            'city'          => $this->getCity(),
            'postalcode'    => $this->getPostalcode(),
            'country'       => $this->getCountry(),
            'latitude'      => $this->getLatitude(),
            'longitude'     => $this->getLongitude(),
            'step_distance' => $this->getStep_distance(),
            'step_duration' => $this->getStep_duration(),
            'type'          => $this->getType(),
            'mandatory'     => $this->getMandatory()
        ];
    }
}
