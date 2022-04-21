<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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
 */

namespace App\Geography\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Geography : a geographic point.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readGeography"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="GET",
 *              "swagger_context" = {
 *                  "tags"={"Geography"},
 *                  "parameters"={
 *                     {
 *                         "name" = "search",
 *                         "in" = "query",
 *                         "required" = "true",
 *                         "type" = "string",
 *                         "description" = "The search query"
 *                     }
 *                  }
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          }
 *      }
 * )
 */
class Point
{
    /**
     * @var null|string the country
     * @Groups("readGeography")
     */
    private $country;

    /**
     * @var null|string the country code
     * @Groups("readGeography")
     */
    private $countryCode;

    /**
     * @var null|float the distance from the centroid, if relevant
     * @Groups("readGeography")
     */
    private $distance;

    /**
     * @var null|string the house number
     * @Groups("readGeography")
     */
    private $houseNumber;

    /**
     * @var string The id of the point
     *
     * @ApiProperty(identifier=true)
     * @Groups("readGeography")
     */
    private $id;

    /**
     * @var float the latitude
     * @Groups("readGeography")
     */
    private $lat;

    /**
     * @var null|string the locality
     * @Groups("readGeography")
     */
    private $locality;

    /**
     * @var null|string the locality code
     * @Groups("readGeography")
     */
    private $localityCode;

    /**
     * @var float the longitude
     * @Groups("readGeography")
     */
    private $lon;

    /**
     * @var null|string the macro region
     * @Groups("readGeography")
     */
    private $macroRegion;

    /**
     * @var null|string the name of the point
     * @Groups("readGeography")
     */
    private $name;

    /**
     * @var null|int the population
     * @Groups("readGeography")
     */
    private $population;

    /**
     * @var null|string the postal code
     * @Groups("readGeography")
     */
    private $postalCode;

    /**
     * @var string the name of the point provider
     * @Groups("readGeography")
     */
    private $provider;

    /**
     * @var null|string the region
     * @Groups("readGeography")
     */
    private $region;

    /**
     * @var null|string the region code
     * @Groups("readGeography")
     */
    private $regionCode;

    /**
     * @var null|string the street name
     * @Groups("readGeography")
     */
    private $streetName;

    /**
     * @var string The type of the point:
     *             - housenumber : a full address (house number + street + locality + country)
     *             - street : a street of a locality, without the house number
     *             - locality
     *             - venue
     *             - other : unknown type of point
     * @Groups("readGeography")
     */
    private $type;

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(?float $distance)
    {
        $this->distance = $distance;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function setLat(float $lat)
    {
        $this->lat = $lat;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality)
    {
        $this->locality = $locality;
    }

    public function getLocalityCode(): ?string
    {
        return $this->localityCode;
    }

    public function setLocalityCode(?string $localityCode)
    {
        $this->localityCode = $localityCode;
    }

    public function getLon(): float
    {
        return $this->lon;
    }

    public function setLon(float $lon)
    {
        $this->lon = $lon;
    }

    public function getMacroRegion(): ?string
    {
        return $this->macroRegion;
    }

    public function setMacroRegion(?string $macroRegion)
    {
        $this->macroRegion = $macroRegion;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population)
    {
        $this->population = $population;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider)
    {
        $this->provider = $provider;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region)
    {
        $this->region = $region;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode)
    {
        $this->regionCode = $regionCode;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName)
    {
        $this->streetName = $streetName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }
}
