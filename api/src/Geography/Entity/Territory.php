<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Geography\Controller\TerritoryPost;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Solidary\Entity\Structure;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A geographical territory, represented by a geojson multipolygon.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('territory_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "territoriesPoint"={
 *              "method"="GET",
 *              "path"="/territories/point",
 *              "security"="is_granted('territory_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "latitude",
 *                          "type" = "float",
 *                          "required" = true,
 *                          "description" = "the point's latitude"
 *                      },
 *                      {
 *                          "name" = "longitude",
 *                          "type" = "float",
 *                          "required" = true,
 *                          "description" = "the point's longitude"
 *                      }
 *                  }
 *              }
 *           },
 *          "link"={
 *              "method"="GET",
 *              "path"="/territories/link",
 *              "security"="is_granted('territory_link',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/territories",
 *              "security_post_denormalize"="is_granted('territory_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('territory_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "put"={
 *              "security"="is_granted('territory_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('territory_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"name": "partial"})
 * @ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 */
class Territory
{

    /**
     * @var int The id of this territory.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var string The name of the territory.
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var string The geoJson details of the territory.
     *
     * @ORM\Column(type="multipolygon")
     * @Groups({"read","write"})
     */
    private $geoJsonDetail;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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
}
