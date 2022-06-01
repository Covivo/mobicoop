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
 */

namespace App\RdexPlus\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * RDEX+ : A Waypoint
 * Documentation : https://rdex.fabmob.io/.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Waypoint
{
    /**
     * @var float Waypoint's longitude
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $longitude;

    /**
     * @var float Waypoint's latitude
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $latitude;

    /**
     * @var string Waypoint's address
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $address;

    /**
     * @var string Waypoint's city
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $city;

    /**
     * @var string Waypoint's postal code
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $postalCode;

    /**
     * @var string Waypoint's country
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $country;

    /**
     * @var string Waypoint's name
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $poiName;

    /**
     * @var int Distance in meters of the step from the previous point until this waypoint
     *
     * @Groups({"rdexPlusRead"})
     */
    private $stepDistance;

    /**
     * @var int Duration in seconds of the step from the previous point until this waypoint
     *
     * @Groups({"rdexPlusRead"})
     */
    private $stepDuration;

    /**
     * @var string Waypoint's name
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $type;

    /**
     * @var bool if this waypoint is mandatory
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $mandatory;

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

    public function getStepDistance(): ?int
    {
        return $this->stepDistance;
    }

    public function setStepDistance(?int $stepDistance): self
    {
        $this->stepDistance = $stepDistance;

        return $this;
    }

    public function getStepDuration(): ?int
    {
        return $this->stepDuration;
    }

    public function setStepDuration(?int $stepDuration): self
    {
        $this->stepDuration = $stepDuration;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isMandatory(): ?bool
    {
        return $this->mandatory;
    }

    public function setMandatory(?bool $mandatory): self
    {
        $this->mandatory = $mandatory;

        return $this;
    }
}
