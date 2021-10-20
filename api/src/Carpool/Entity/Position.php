<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Geography\Entity\Direction;
use Symfony\Component\Validator\Constraints as Assert;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

/**
 * Dynamic carpooling : last position of a dynamic carpooler.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Position
{
    /**
     * @var int The id of this position.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Proposal The proposal related to the position.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $proposal;
        
    /**
     * @var Waypoint The floating waypoint corresponding to the position.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Waypoint", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $waypoint;

    /**
     * @var Direction|null Direction related to the dynamic carpool - updated at each position update.
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $direction;

    /**
     * @var string History of geographic points as a linestring, used to compute the direction. Updated at each new position. Can be emptied when the carpool is finished.
     * @ORM\Column(type="linestring", nullable=true)
     */
    private $geoJsonPoints;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var array|null The array of points as Address objects. Used to create the geoJsonPoints.
     */
    private $points;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }
    
    public function getWaypoint(): ?Waypoint
    {
        return $this->waypoint;
    }

    public function setwaypoint(?Waypoint $waypoint): self
    {
        $this->waypoint = $waypoint;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }
    
    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }

    public function getGeoJsonPoints()
    {
        return $this->geoJsonPoints;
    }
    
    public function setGeoJsonPoints($geoJsonPoints): self
    {
        $this->geoJsonPoints = $geoJsonPoints;
        
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

    public function getPoints(): ?array
    {
        return $this->points;
    }
    
    public function setPoints(array $points): self
    {
        $this->points = $points;
        
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
        // we also set the updated date, which may be always needed
        $this->setUpdatedDate(new \Datetime());
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
     * GeoJson representation of the points.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAutoGeoJsonPoints()
    {
        if (!is_null($this->getPoints())) {
            $arrayPoints = [];
            foreach ($this->getPoints() as $address) {
                $arrayPoints[] = new Point($address->getLongitude(), $address->getLatitude());
            }
            $this->setGeoJsonPoints(new LineString($arrayPoints));
        }
    }
}
