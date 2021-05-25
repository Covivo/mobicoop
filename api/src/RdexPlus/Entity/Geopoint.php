<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RdexPlus\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RDEX+ : A Geopoint
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Geopoint
{
    
    /**
     * @var float Geopoint's longitude
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $longitude;

    /**
     * @var float Geopoint's latitude
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $latitude;

    /**
     * @var string Geopoint's address
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $address;

    /**
     * @var string Geopoint's city
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $city;

    /**
     * @var string Geopoint's postal code
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $postalCode;
    
    /**
     * @var string Geopoint's country
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $country;

    /**
     * @var string Geopoint's name
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $poiName;

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }
    
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }
    
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
    
    public function getPoiName(): ?string
    {
        return $this->poiName;
    }

    public function setPoiName(?string $poiName): self
    {
        $this->poiName = $poiName;

        return $this;
    }
}
