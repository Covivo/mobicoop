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

namespace App\Carpool\Interoperability\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An Interoperability Waypoint
* @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Waypoint
{
   
    /**
     * @var string|null Waypoint's latitude
     * @Groups({"adWrite"})
     */
    private $latitude;
    
    /**
     * @var string|null Waypoint's longitude
     * @Groups({"adWrite"})
     */
    private $longitude;

    /**
     * @var string|null Waypoint's street number
     * @Groups({"adWrite"})
     */
    private $streetNumber;

    /**
     * @var string|null Waypoint's street
     * @Groups({"adWrite"})
     */
    private $street;

    /**
     * @var string|null Waypoint's postal code
     * @Groups({"adWrite"})
     */
    private $postalCode;

    /**
     * @var string|null Waypoint's address locality
     * @Groups({"adWrite"})
     */
    private $addressLocality;

    /**
     * @var string|null Waypoint's country
     * @Groups({"adWrite"})
     */
    private $country;

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

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }
    
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCoder): self
    {
        $this->postalCoder = $postalCoder;

        return $this;
    }

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function setAddressLocality(?string $addressLocality): self
    {
        $this->addressLocality = $addressLocality;

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
}
