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

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel path between 2 points.
 */
Class Path 
{
    /**
     * @var int The id of this path.
     */
    private $id;

    /**
     * @var int Position number of the current part in the whole path.
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var string Path detail.
     * @Assert\NotBlank
     */
    private $detail;

    /**
     * @var int Encoding format (1 = json; 2 = xml)
     * @Assert\NotBlank
     */
    private $encodeFormat;

    /**
     * @var Point The starting point of the path.
     * @Assert\NotBlank
     */
    private $point1;

    /**
     * @var Point The destination point of the path.
     * @Assert\NotBlank
     */
    private $point2;

    /**
     * @var TravelMode The travel mode of the path.
     */
    private $travelMode;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getEncodeFormat(): ?int
    {
        return $this->encodeFormat;
    }

    public function setEncodeFormat(int $encodeFormat): self
    {
        $this->encodeFormat = $encodeFormat;

        return $this;
    }

    public function getPoint1(): ?Point
    {
        return $this->point1;
    }

    public function setPoint1(?Point $point1): self
    {
        $this->point1 = $point1;

        return $this;
    }

    public function getPoint2(): ?Point
    {
        return $this->point2;
    }

    public function setPoint2(?Point $point2): self
    {
        $this->point2 = $point2;

        return $this;
    }

    public function getTravelMode(): ?TravelMode
    {
        return $this->travelMode;
    }

    public function setTravelMode(?TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;

        return $this;
    }
}