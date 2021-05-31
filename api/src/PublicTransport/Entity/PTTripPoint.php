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

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Geography\Entity\Address;
use Doctrine\ORM\Mapping as ORM;

/**
 * A public transport Trip Point.
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/trippoints",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "provider",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The public transport data provider"
 *                      },
 *                      {
 *                          "name" = "latitude",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The latitude of the point (can't combine with keywords)"
 *                      },
 *                      {
 *                          "name" = "longitude",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "The longitude of the point (can't combine with keywords)"
 *                      },
 *                      {
 *                          "name" = "keywords",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "float",
 *                          "description" = "Trip points whose name contains these keywords (can't combine with lat/lon)"
 *                      },
 *                      {
 *                          "name" = "perimeter",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "int",
 *                          "description" = "Radius of the perimeter (in meters)"
 *                      },
 *                      {
 *                          "name" = "transportModes",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "List of integer like: 0|1|2|3..."
 *                      },
 *                  },
 *              }
 *          }
 *     },
 *      itemOperations={
 *          "get"={
 *              "path"="/trippoints/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTTripPoint
{
    /**
     * @ApiProperty(identifier=true)
     * @var int The id of this Trip Point.
     * @Groups("pt")
     */
    private $id;

    /**
     * @var float Latitude of this Trip Point.
     * @Groups("pt")
     */
    private $latitude;

    /**
     * @var float Longitude of this Trip Point.
     * @Groups("pt")
     */
    private $longitude;


    /**
     * @var int LocalityId of this Trip Point.
     * @Groups("pt")
     */
    private $localityId;

    /**
     * @var string Name of this Trip Point.
     * @Groups("pt")
     */
    private $name;

    /**
     * @var int Type of this Trip Point.
     * @Groups("pt")
     */
    private $pointType;

    /**
     * @var string Postal Code of this Trip Point.
     * @Groups("pt")
     */
    private $postalCode;

    /**
     * @var string Transport mode of this Trip Point.
     * @Groups("pt")
     */
    private $transportMode;

    /**
     * @var PTLocality Locality this Trip Point.
     * @Groups("pt")
     */
    private $locality;

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

    public function getLocality(): PTLocality
    {
        return $this->locality;
    }

    public function setLocality(PTLocality $locality): self
    {
        $this->locality = $locality;

        return $this;
    }
}
