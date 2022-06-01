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
 */

namespace App\PublicTransport\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport Stop.
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/stops",
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
 *                          "name" = "id",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "int",
 *                          "description" = "The id of the stop"
 *                      },
 *                  },
 *              }
 *          }
 *     },
 *      itemOperations={
 *          "get"={
 *              "path"="/stop/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTStop
{
    /**
     * @ApiProperty(identifier=true)
     *
     * @var int id of this stop
     * @Groups("pt")
     */
    private $id;

    /**
     * @var string name of this stop
     * @Groups("pt")
     */
    private $name;

    /**
     * @var null|float the latitude of the stop
     * @Groups("pt")
     */
    private $latitude;

    /**
     * @var null|float the longitude of the stop
     * @Groups("pt")
     */
    private $longitude;

    /**
     * @var null|PTAccessibilityStatus the accessibility status of the stop
     * @Groups("pt")
     */
    private $accessibilityStatus;

    /**
     * @var null|string is this stop disrupted ?
     * @Groups("pt")
     */
    private $isDisrupted;

    /**
     * @var null|int the type of the stop
     * @Groups("pt")
     */
    private $pointType;

    public function __construct($id)
    {
        $this->id = $id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAccessibilityStatus(): ?PTAccessibilityStatus
    {
        return $this->accessibilityStatus;
    }

    public function setAccessibilityStatus(?PTAccessibilityStatus $accessibilityStatus): self
    {
        $this->accessibilityStatus = $accessibilityStatus;

        return $this;
    }

    public function getIsDisrupted(): ?string
    {
        return $this->isDisrupted;
    }

    public function setIsDisrupted(?string $isDisrupted): self
    {
        $this->isDisrupted = $isDisrupted;

        return $this;
    }

    public function getPointType(): ?int
    {
        return $this->pointType;
    }

    public function setPointType(?int $pointType): self
    {
        $this->pointType = $pointType;

        return $this;
    }
}
