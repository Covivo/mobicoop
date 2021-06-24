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
use App\Carpool\Entity\Criteria;
use App\Geography\Service\GeoTools;
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
 * @ORM\Table(indexes={@ORM\Index(name="IDX_DIRTER", columns={"distance", "duration", "bbox_min_lon", "bbox_min_lat", "bbox_max_lon", "bbox_max_lat"})})
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
 *                  "tags"={"Geography"},
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
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          }
 *      }
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
     * @var string|null The textual encoded detail of the direction.
     * @Groups({"read","write"})
     */
    private $detail;

    /**
     * @var string The geoJson linestring detail of the direction.
     * Note : the detail should be a MULTIPOINT, but we can't use it as it's not supported by the version of doctrine2 spatial package for mysql 5.7 (?)
     * Todo : try to create a multipoint custom type for doctrine 2 spatial ?
     * @Groups({"read","write"})
     */
    private $geoJsonDetail;

    /**
     * @var string The simplified geoJson linestring detail of the direction.
     * Created using the Ramer-Douglas-Peucker algorithm.
     * @ORM\Column(type="linestring", nullable=true)
     * @Groups({"read","write"})
     */
    private $geoJsonSimplified;

    /**
     * @var string The textual encoded snapped waypoints of the direction.
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
     * @var int|null The CO2 emission for this direction.
     * @Groups({"read","mass"})
     */
    private $co2;

    /**
     * @var array|null The decoded points (from detail) of the direction as Address objects.
     * Can be used to draw the path on a map.
     * @Groups("read")
     */
    private $points;

    /**
     * @var array|null The decoded points (from detail) of the direction as latitude/longitude array.
     * Can be used to draw the path on a map.
     * @Groups("read")
     */
    private $directPoints;

    /**
     * @var array|null The decoded snapped waypoints of the direction.
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

    /**
     * @var ArrayCollection|null The territories of this direction.
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     */
    private $territories;

    /**
     * @var boolean Save the geoJson with the direction.
     * Used to avoid slow insert/updates for realtime operations.
     */
    private $saveGeoJson;

    /**
     * @var boolean Set the possibility to update the detail directly, instead of using an external system.
     * Used for dynamic carpool where we can construct a direction from scratch (adding points on the fly).
     */
    private $detailUpdatable;

    /**
     * @var ArrayCollection The criterias as driver related to the direction.
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Criteria", mappedBy="directionDriver")
     */
    private $criteriaDrivers;

    /**
     * @var ArrayCollection The criterias as passenger related to the direction.
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Criteria", mappedBy="directionPassenger")
     */
    private $criteriaPassengers;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->territories = new ArrayCollection();
        $this->criteriaDrivers = new ArrayCollection();
        $this->criteriaPassengers = new ArrayCollection();
        $this->saveGeoJson = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
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

    public function getDetail(): ?string
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

    public function getGeoJsonSimplified()
    {
        return $this->geoJsonSimplified;
    }
    
    public function setGeoJsonSimplified($geoJsonSimplified): self
    {
        $this->geoJsonSimplified = $geoJsonSimplified;
        
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

    public function hasSaveGeoJson(): ?bool
    {
        return $this->saveGeoJson;
    }

    public function setSaveGeoJson(bool $saveGeoJson): self
    {
        $this->saveGeoJson = $saveGeoJson;

        return $this;
    }

    public function getTerritories()
    {
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories[] = $territory;
        }
        
        return $this;
    }
    
    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }
        return $this;
    }

    public function removeTerritories(): self
    {
        $this->territories->clear();
        return $this;
    }

    public function isDetailUpdatable(): ?bool
    {
        return $this->detailUpdatable;
    }

    public function setDetailUpdatable(bool $detailUpdatable): self
    {
        $this->detailUpdatable = $detailUpdatable;

        return $this;
    }

    public function getCriteriaDrivers(bool $getValues = true)
    {
        if ($getValues) {
            return $this->criteriaDrivers->getValues();
        }
        return $this->criteriaDrivers;
    }

    public function addCriteriaDriver(Criteria $criteriaDriver): self
    {
        if (!$this->criteriaDrivers->contains($criteriaDriver)) {
            $this->criteriaDrivers[] = $criteriaDriver;
        }
        
        return $this;
    }
    
    public function removeCriteriaDriver(Criteria $criteriaDriver): self
    {
        if ($this->criteriaDrivers->contains($criteriaDriver)) {
            $this->criteriaDrivers->removeElement($criteriaDriver);
        }
        return $this;
    }

    public function getCriteriaPassengers(bool $getValues = true)
    {
        if ($getValues) {
            return $this->criteriaPassengers->getValues();
        }
        return $this->criteriaPassengers;
    }

    public function addCriteriaPassenger(Criteria $criteriaPassenger): self
    {
        if (!$this->criteriaPassengers->contains($criteriaPassenger)) {
            $this->criteriaPassengers[] = $criteriaPassenger;
        }
        
        return $this;
    }
    
    public function removeCriteriaPassenger(Criteria $criteriaPassenger): self
    {
        if ($this->criteriaPassengers->contains($criteriaPassenger)) {
            $this->criteriaPassengers->removeElement($criteriaPassenger);
        }
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
                [$this->getBboxMaxLon(),$this->getBboxMaxLat()],
                [$this->getBboxMaxLon(),$this->getBboxMinLat()],
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
        if (!$this->hasSaveGeoJson()) {
            return;
        }
        if (!is_null($this->getGeoJsonDetail()) && !$this->isDetailUpdatable()) {
            //if (!$this->isDetailUpdatable()) {
            return;
        }
        if (!is_null($this->getPoints())) {
            // $arrayPoints = [];
            // foreach ($this->getPoints() as $address) {
            //     $arrayPoints[] = new Point($address->getLongitude(), $address->getLatitude());
            // }
            // $this->setGeoJsonDetail(new LineString($arrayPoints));
            $arrayPoints = [];
            $geoTools = new GeoTools();
            $simplifiedPoints = $geoTools->getSimplifiedPoints($this->getPoints());
            //var_dump($this->getPoints());
            //var_dump($simplifiedPoints);exit;
            foreach ($simplifiedPoints as $point) {
                $arrayPoints[] = new Point($point[0], $point[1]);
            }
            $this->setGeoJsonSimplified(new LineString($arrayPoints));
        }
    }
}
