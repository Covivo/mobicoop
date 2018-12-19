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

namespace App\Geography\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Direction entity
 * This entity describes the route to follow between 2 or more addresses using an individual transport mode.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 *
 */
class Direction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @var int The total distance of the direction in meter.
     * @ORM\Column(type="integer")
     */
    private $distance;
    
    /**
     * @var int The total time of the direction in seconds.
     * @ORM\Column(type="integer")
     */
    private $duration;
    
    /**
     * @var int The total ascend of the direction in meter.
     * @ORM\Column(type="integer")
     */
    private $ascend;
    
    /**
     * @var int The total descend of the direction in meter.
     * @ORM\Column(type="integer")
     */
    private $descend;

    /**
     * @var float The minimum longitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $bbox_minLon;

    /**
     * @var float The minimum latitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $bbox_minLat;
    
    /**
     * @var float The maximum longitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $bbox_maxLon;
    
    /**
     * @var float The maximum latitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $bbox_maxLat;
    
    /**
     * @var string The textual encoded detail of the direction.
     * @ORM\Column(type="text")
     */
    private $detail;
    
    /**
     * @var string The encoding format of the detail.
     * @ORM\Column(type="string", length=45)
     */
    private $format;
    
    /**
     * @var Zone[]|null The geographical zones covered by the direction.
     *
     * @ORM\ManyToMany(targetEntity="App\Geography\Entity\Zone")
     */
    private $zones;
    
    public function __construct()
    {
        $this->travelModes = new ArrayCollection();
    }
    
    /**
     * @return number
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return number
     */
    public function getTime()
    {
        return $this->time;
    }
    
    /**
     * @return number
     */
    public function getAscend()
    {
        return $this->ascend;
    }
    
    /**
     * @return number
     */
    public function getDescend()
    {
        return $this->descend;
    }
    
    /**
     * @return number
     */
    public function getBbox_minLon()
    {
        return $this->bbox_minLon;
    }
    
    /**
     * @return number
     */
    public function getBbox_minLat()
    {
        return $this->bbox_minLat;
    }
    
    /**
     * @return number
     */
    public function getBbox_maxLon()
    {
        return $this->bbox_maxLon;
    }
    
    /**
     * @return number
     */
    public function getBbox_maxLat()
    {
        return $this->bbox_maxLat;
    }

    /**
     * @return multitype:\App\Address\Entity\Address
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return multitype:\App\Address\Entity\Address
     */
    public function getWaypoints()
    {
        return $this->waypoints;
    }
    
    /**
     * @param number $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    /**
     * @param number $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
    
    /**
     * @param number $ascend
     */
    public function setAscend($ascend)
    {
        $this->ascend = $ascend;
    }
    
    /**
     * @param number $descend
     */
    public function setDescend($descend)
    {
        $this->descend = $descend;
    }
    
    /**
     * @param number $bbox_minLon
     */
    public function setBbox_minLon($bbox_minLon)
    {
        $this->bbox_minLon = $bbox_minLon;
    }
    
    /**
     * @param number $bbox_minLat
     */
    public function setBbox_minLat($bbox_minLat)
    {
        $this->bbox_minLat = $bbox_minLat;
    }
    
    /**
     * @param number $bbox_maxLon
     */
    public function setBbox_maxLon($bbox_maxLon)
    {
        $this->bbox_maxLon = $bbox_maxLon;
    }
    
    /**
     * @param number $bbox_maxLat
     */
    public function setBbox_maxLat($bbox_maxLat)
    {
        $this->bbox_maxLat = $bbox_maxLat;
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
    
    public function getFormat(): ?int
    {
        return $this->format;
    }
    
    public function setFormat(int $format): self
    {
        $this->format = $format;
        
        return $this;
    }
    
    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }
    
    public function addZone(Zone $zone): self
    {
        if (!$this->zones->contains($zone)) {
            $this->zones[] = $zone;
        }
        
        return $this;
    }
    
    public function removeZone(Zone $zone): self
    {
        if ($this->zones->contains($zone)) {
            $this->zones->removeElement($zone);
        }
        
        return $this;
    }
}
