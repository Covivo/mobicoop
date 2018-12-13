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

namespace App\Carpool\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : a route path between 2 waypoints.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class Route
{
    /**
     * @var int The id of this route.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var string Path detail.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"read"})
     */
    private $detail;

    /**
     * @var int Encoding format (1 = json; 2 = xml)
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read"})
     */
    private $encodeFormat;

    /**
     * @var Waypoint The starting point of the path.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Waypoint", inversedBy="routeOrigin")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $waypointOrigin;

    /**
     * @var Waypoint The destination point of the route.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Waypoint", inversedBy="routeDestination")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $waypointDestination;
    
    /**
     * @var int|null Distance of the route in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distance;
    
    /**
     * @var int|null Estimated duration of the route in seconds (based on real distance).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var TravelMode The travel mode of the path.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\TravelMode")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $travelMode;
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getWaypointOrigin(): ?Waypoint
    {
        return $this->waypointOrigin;
    }

    public function setWaypointOrigin(?Waypoint $waypointOrigin): self
    {
        $this->waypointOrigin = $waypointOrigin;

        return $this;
    }

    public function getWaypointDestination(): ?Waypoint
    {
        return $this->waypointDestination;
    }

    public function setWaypointDestination(?Waypoint $waypointDestination): self
    {
        $this->waypointDestination = $waypointDestination;

        return $this;
    }
    
    public function getDistance(): ?int
    {
        return $this->distance;
    }
    
    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;
        
        return $this;
    }
    
    public function getDuration(): ?int
    {
        return $this->duration;
    }
    
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        
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
