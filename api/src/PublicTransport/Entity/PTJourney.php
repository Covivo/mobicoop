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

namespace App\PublicTransport\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Match\Entity\MassPerson;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport journey.
 *
 * @ORM\Entity
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/journeys",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "origin_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "origin_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the origin point"
 *                      },
 *                      {
 *                          "name" = "destination_latitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "destination_longitude",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the destination point"
 *                      },
 *                      {
 *                          "name" = "date",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "format" = "date-time",
 *                          "description" = "The date of the trip (on RFC3339 format)"
 *                      },
 *                      {
 *                          "name" = "dateType",
 *                          "in" = "query",
 *                          "type" = "string",
 *                          "description" = "The date type of the trip",
 *                          "enum" = {
 *                              "departure",
 *                              "arrival"
 *                          }
 *                      },
 *                      {
 *                          "name" = "modes",
 *                          "in" = "query",
 *                          "type" = "string",
 *                          "description" = "The transport modes accepted for the trip",
 *                          "enum" = {
 *                              "PT",
 *                              "BIKE",
 *                              "CAR",
 *                              "PT+BIKE",
 *                              "PT+CAR"
 *                          }
 *                      }
 *
 *                  },
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "path"="/journeys/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTJourney
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this journey.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var int The total distance of this journey.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var string The total duration of this journey (in seconds).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $duration;

    /**
     * @var int The number of changes of this journey.
     *
     * @Groups("pt")
     */
    private $changeNumber;
    
    /**
     * @var float The estimated price of this journey.
     *
     * @ORM\Column(type="decimal", precision=4, scale=2, nullable=true)
     * @Groups("pt")
     */
    private $price;
   
    /**
     * @var int The estimated CO2 emission of this journey.
     *
     * @ORM\Column(type="integer")
     * @Groups("pt")
     */
    private $co2;
        
    /**
     * @var PTDeparture The departure of this journey.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTDeparture")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this journey.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTArrival")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptarrival;
    
    /**
     * @var ArrayCollection The legs of this journey.
     *
     * @ORM\OneToMany(targetEntity="\App\PublicTransport\Entity\PTLeg", mappedBy="ptjourney", cascade={"persist"})
     * @Groups("pt")
     */
    private $ptlegs;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->ptlegs = new ArrayCollection();
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
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

    public function getChangeNumber(): ?int
    {
        return $this->changeNumber;
    }
    
    public function setChangeNumber(?int $changeNumber): self
    {
        $this->changeNumber = $changeNumber;
        
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    public function setPrice(?float $price): self
    {
        $this->price = $price;
        
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

    public function getPTDeparture(): ?PTDeparture
    {
        return $this->ptdeparture;
    }
    
    public function setPTDeparture(?PTDeparture $ptdeparture): self
    {
        $this->ptdeparture = $ptdeparture;
        
        return $this;
    }
    
    public function getPTArrival(): ?PTArrival
    {
        return $this->ptarrival;
    }
    
    public function setPTArrival(?PTArrival $ptarrival): self
    {
        $this->ptarrival = $ptarrival;
        
        return $this;
    }
    
    public function getPTLegs()
    {
        return $this->ptlegs->getValues();
    }

    public function setPTLegs(?ArrayCollection $ptlegs): self
    {
        $this->ptlegs = $ptlegs;
        
        return $this;
    }
    
    public function addPTLeg(PTLeg $ptleg): self
    {
        if (!$this->ptlegs->contains($ptleg)) {
            $this->ptlegs->add($ptleg);
            $ptleg->setPTJourney($this);
        }
        
        return $this;
    }
    
    public function removePTLeg(PTLeg $ptleg): self
    {
        if ($this->ptlegs->contains($ptleg)) {
            $this->ptlegs->removeElement($ptleg);
            // set the owning side to null (unless already changed)
            if ($ptleg->getPTJourney() === $this) {
                $ptleg->setPTJourney(null);
            }
        }
        
        return $this;
    }
}
