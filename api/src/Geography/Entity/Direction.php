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
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

/**
 * A direction : the route to follow between 2 or more addresses using an individual transport mode.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "search"={
 *              "method"="GET",
 *              "path"="/directions/search",
 *              "normalization_context"={"groups"={"read"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "points",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "array",
 *                          "format" = "float",
 *                          "description" = "The points in the form points[x][longitude]&points[x][latitude]"
 *                      }
 *                  }
 *              }
 *          }
 *      },
 *      itemOperations={"get"}
 * )
 *
 */
class Direction
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var int The total distance of the direction in meter.
     * @ORM\Column(type="integer")
     * @Groups({"read","results","write","mass","thread"})
     */
    private $distance;
    
    /**
     * @var int The total duration of the direction in milliseconds.
     * @ORM\Column(type="integer")
     * @Groups({"read","results","write","mass","thread"})
     */
    private $duration;
    
    /**
     * @var int The total ascend of the direction in meter.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","mass"})
     */
    private $ascend;
    
    /**
     * @var int The total descend of the direction in meter.
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","mass"})
     */
    private $descend;

    /**
     * @var float The minimum longitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $bboxMinLon;

    /**
     * @var float The minimum latitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $bboxMinLat;
    
    /**
     * @var float The maximum longitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $bboxMaxLon;
    
    /**
     * @var float The maximum latitude of the bounding box of the direction.
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $bboxMaxLat;

    /**
     * @var string The geoJson bounding box of the direction.
     * @ORM\Column(type="polygon", nullable=true)
     * @Groups({"read","write"})
     */
    private $geoJsonBbox;
    
    /**
     * @var int|null The initial bearing of the direction in degrees.
     * @ORM\Column(type="integer",nullable=true)
     * @Groups({"read","write"})
     */
    private $bearing;

    /**
     * @var string The textual encoded detail of the direction.
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $detail;

    /**
     * @var string The geoJson linestring detail of the direction.
     * @ORM\Column(type="linestring", nullable=true)
     * Note : the detail should be a MULTIPOINT, but we can't use it as it's not supported by the version of doctrine2 spatial package for mysql 5.7 (?)
     * Todo : try to create a multipoint custom type for doctrine 2 spatial ?
     * @Groups({"read","write"})
     */
    private $geoJsonDetail;

    /**
     * @var string The textual encoded snapped waypoints of the direction.
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $snapped;
    
    /**
     * @var string The encoding format of the detail.
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $format;

    /**
     * @var ArrayCollection The geographical zones crossed by the direction.
     *
     * @ORM\OneToMany(targetEntity="\App\Geography\Entity\Zone", mappedBy="direction", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $zones;

    /**
     * @var int|null The CO2 emission for this direction.
     * @Groups({"read","mass"})
     */
    private $co2;

    /**
     * @var Address[]|null The decoded points (from detail) of the direction as Address objects.
     * Can be used to draw the path on a map.
     * @Groups("read")
     */
    private $points;

    /**
     * @var array|Address[]|null The decoded points (from detail) of the direction as latitude/longitude array.
     * Can be used to draw the path on a map.
     * @Groups("read")
     */
    private $directPoints;

    /**
     * @var Address[]|null The decoded snapped waypoints of the direction.
     * The snapped waypoints are the mandatory waypoints of the direction.
     * These points can slightly differ from the original waypoints as they are given by the router.
     * /!\ different than Waypoint entity /!\
     */
    private $snappedWaypoints;

    /**
     * @var int[]|null The duration from the start to the each snapped waypoint.
     */
    private $durations;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;
    
    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->zones = new ArrayCollection();
        $this->territories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
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

    public function getGeoJsonBbox()
    {
        return $this->geoJsonBbox;
    }
    
    public function setGeoJsonBbox($geoJsonBbox): self
    {
        $this->geoJsonBbox = $geoJsonBbox;
        
        return $this;
    }

    public function getBearing(): ?int
    {
        return $this->bearing;
    }
    
    public function setBearing(?int $bearing): self
    {
        $this->bearing = $bearing;
        
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

    public function getGeoJsonDetail()
    {
        return $this->geoJsonDetail;
    }
    
    public function setGeoJsonDetail($geoJsonDetail): self
    {
        $this->geoJsonDetail = $geoJsonDetail;
        
        return $this;
    }

    public function getSnapped(): string
    {
        return $this->snapped;
    }
    
    public function setSnapped(string $snapped): self
    {
        $this->snapped = $snapped;
        
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
    
    public function getZones()
    {
        return $this->zones->getValues();
    }
    
    public function addZone(Zone $zone): self
    {
        if (!$this->zones->contains($zone)) {
            $this->zones[] = $zone;
            $zone->setDirection($this);
        }
        
        return $this;
    }
    
    public function removeZone(Zone $zone): self
    {
        if ($this->zones->contains($zone)) {
            $this->zones->removeElement($zone);
            // set the owning side to null (unless already changed)
            if ($zone->getDirection() === $this) {
                $zone->setDirection(null);
            }
        }
        
        return $this;
    }

    public function getCo2(): ?int
    {
        return $this->co2;
    }

    public function setCo2(?int $co2): self
    {
        $this->co2 = $co2;

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

    public function getDirectPoints(): ?array
    {
        return $this->directPoints;
    }
    
    public function setDirectPoints(array $directPoints): self
    {
        $this->directPoints = $directPoints;
        
        return $this;
    }

    public function getSnappedWaypoints(): ?array
    {
        return $this->snappedWaypoints;
    }
    
    public function setSnappedWaypoints(array $snappedWaypoints): self
    {
        $this->snappedWaypoints = $snappedWaypoints;
        
        return $this;
    }

    public function getDurations(): ?array
    {
        return $this->durations;
    }
    
    public function setDurations(array $durations): self
    {
        $this->durations = $durations;
        
        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
    
    /**
     * GeoJson representation of the bounding box.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAutoGeoJsonBbox()
    {
        if (!is_null($this->getGeoJsonBbox())) {
            return;
        }
        if (!is_null($this->getBboxMinLon()) && !is_null($this->getBboxMinLat()) && !is_null($this->getBboxMaxLon()) && !is_null($this->getBboxMaxLat())) {
            $this->setGeoJsonBbox(new Polygon([[
                [$this->getBboxMinLon(),$this->getBboxMinLat()],
                [$this->getBboxMinLon(),$this->getBboxMaxLat()],
                [$this->getBboxMaxLon(),$this->getBboxMinLat()],
                [$this->getBboxMaxLon(),$this->getBboxMaxLat()],
                [$this->getBboxMinLon(),$this->getBboxMinLat()]
            ]]));
        }
    }

    /**
     * GeoJson representation of the detail.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAutoGeoJsonDetail()
    {
        if (!is_null($this->getGeoJsonDetail())) {
            return;
        }
        if (!is_null($this->getPoints())) {
            $arrayPoints = [];
            foreach ($this->getPoints() as $address) {
                $arrayPoints[] = new Point($address->getLongitude(), $address->getLatitude());
            }
            $this->setGeoJsonDetail(new LineString($arrayPoints));
        }
    }
}
