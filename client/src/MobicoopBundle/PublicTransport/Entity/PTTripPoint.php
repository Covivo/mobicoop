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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A public transport trip point.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTTripPoint
{
    /**
     * @var int The id of this Trip Point.
     */
    private $id;

    /**
     * @var float Latitude of this Trip Point.
     */
    private $latitude;

    /**
     * @var float Longitude of this Trip Point.
     * @Groups("pt")
     */
    private $longitude;


    /**
     * @var int LocalityId of this Trip Point.
     */
    private $localityId;

    /**
     * @var string Name of this Trip Point.
     */
    private $name;

    /**
     * @var int Type of this Trip Point.
     */
    private $pointType;

    /**
     * @var string Postal Code of this Trip Point.
     */
    private $postalCode;

    /**
     * @var string Transport mode of this Trip Point.
     */
    private $transportMode;


    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLocalityId(): int
    {
        return $this->localityId;
    }

    public function setLocalityId(int $localityId): self
    {
        $this->localityId = $localityId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPointType(): int
    {
        return $this->pointType;
    }

    public function setPointType(int $pointType): self
    {
        $this->pointType = $pointType;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTransportMode(): string
    {
        return $this->transportMode;
    }

    public function setTransportMode(string $transportMode): self
    {
        $this->transportMode = $transportMode;

        return $this;
    }
}
