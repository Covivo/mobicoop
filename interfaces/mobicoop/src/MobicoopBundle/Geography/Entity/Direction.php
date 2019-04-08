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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Entity;

/**
 * Direction entity
 * This entity describes the route to follow between 2 or more addresses using an individual transport mode.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class Direction
{
    /**
     * @var int The id of this direction.
     */
    private $id;
    
    /**
     * @var int The total distance of the direction in meter.
     */
    private $distance;
    
    /**
     * @var int The total duration of the direction in seconds.
     */
    private $duration;
    
    /**
     * @var int The total ascend of the direction in meter.
     */
    private $ascend;
    
    /**
     * @var int The total descend of the direction in meter.
     */
    private $descend;

    /**
     * @var float The minimum longitude of the bounding box of the direction.
     */
    private $bboxMinLon;

    /**
     * @var float The minimum latitude of the bounding box of the direction.
     */
    private $bboxMinLat;
    
    /**
     * @var float The maximum longitude of the bounding box of the direction.
     */
    private $bboxMaxLon;
    
    /**
     * @var float The maximum latitude of the bounding box of the direction.
     */
    private $bboxMaxLat;
    
    /**
     * @var string The textual encoded detail of the direction.
     */
    private $detail;
    
    /**
     * @var string The encoding format of the detail.
     */
    private $format;

    /**
     * @var Address[]|null The decoded points (from detail) of the direction.
     */
    private $points;
        
    public function getDistance(): int
    {
        return $this->distance;
    }
    
    public function setDistance(int $distance): self
    {
        $this->distance = $distance;
        
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }
    
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        
        return $this;
    }
    
    public function getAscend(): ?int
    {
        return $this->ascend;
    }
    
    public function setAscend(?int $ascend): self
    {
        $this->ascend = $ascend;
        
        return $this;
    }
    
    public function getDescend(): ?int
    {
        return $this->descend;
    }
    
    public function setDescend(?int $descend): self
    {
        $this->descend = $descend;
        
        return $this;
    }
    
    public function getBboxMinLon(): ?float
    {
        return $this->bboxMinLon;
    }
    
    public function setBboxMinLon(?float $bboxMinLon): self
    {
        $this->bboxMinLon = $bboxMinLon;
        
        return $this;
    }
    
    public function getBboxMinLat(): ?float
    {
        return $this->bboxMinLat;
    }
    
    public function setBboxMinLat(?float $bboxMinLat)
    {
        $this->bboxMinLat = $bboxMinLat;
        
        return $this;
    }
    
    public function getBboxMaxLon(): ?float
    {
        return $this->bboxMaxLon;
    }
    
    public function setBboxMaxLon(?float $bboxMaxLon): self
    {
        $this->bboxMaxLon = $bboxMaxLon;
        
        return $this;
    }
    
    public function getBboxMaxLat(): ?float
    {
        return $this->bboxMaxLat;
    }
    
    public function setBboxMaxLat(?float $bboxMaxLat): self
    {
        $this->bboxMaxLat = $bboxMaxLat;
        
        return $this;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }
    
    public function setDetail(string $detail): self
    {
        $this->detail = $detail;
        
        return $this;
    }
    
    public function getFormat(): string
    {
        return $this->format;
    }
    
    public function setFormat(string $format): self
    {
        $this->format = $format;
        
        return $this;
    }

    public function getPoints(): ?array
    {
        return $this->points;
    }
    
    public function setPoints(array $points): self
    {
        $this->points = $points;
        
        return $this;
    }
}
