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
use App\Action\Entity\Log;
use App\Solidary\Entity\Structure;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A geographical territory, represented by a geojson multipolygon.
 *
 * @ORM\Entity
 * @ORM\Table(indexes={
 *  @ORM\Index(name="IDX_LATITUDE", columns={"min_latitude", "max_latitude"}),
 *  @ORM\Index(name="IDX_LONGITUDE", columns={"min_longitude", "max_longitude"})
 * })
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
 *          "ADMIN_get"={
 *              "path"="/admin/territories",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('territory_list',object)"
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
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/territories/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('territory_read',object)"
 *          }
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"id":"exact","name": "partial"})
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
     * @Groups({"aRead","read"})
     */
    private $id;

    /**
     * @var string The name of the territory.
     *
     * @ORM\Column(type="string", length=100)
     * @Groups({"aRead","read","write"})
     */
    private $name;

    /**
     * @var string The geoJson details of the territory.
     * /!\ ORM is disabled for performance reasons but TerritoryEventListener avoid the field to be removed on further migrations !
     *
     * ORM\Column(type="multipolygon")
     * @Groups({"read","write"})
     */
    private $geoJsonDetail;

    /**
     * @var int|null The administrative level of this territory.
     * Source for levels : https://en.wikipedia.org/wiki/List_of_administrative_divisions_by_country
     *
     * @ORM\Column(type="integer", nullable=true))
     * @Groups({"read","write"})
     */
    private $adminLevel;

    /**
     * @var float|null The minimal latitude of the territory.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $minLatitude;

    /**
     * @var float|null The maximal latitude of the territory.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $maxLatitude;

    /**
     * @var float|null The minimal longitude of the territory.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $minLongitude;

    /**
     * @var float|null The maximal longitude of the territory.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $maxLongitude;

    /**
     * @var ArrayCollection The logs linked with the Territory.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="territory")
     */
    private $logs;

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
     * @var ArrayCollection|null The parent territories of this Territory
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     * @ORM\JoinTable(name="territory_parent",
     *      joinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    private $parents;


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

    public function getAdminLevel(): ?int
    {
        return $this->adminLevel;
    }

    public function setAdminLevel(?int $adminLevel)
    {
        $this->adminLevel = $adminLevel;
    }

    public function getMinLatitude()
    {
        return $this->minLatitude;
    }

    public function setMinLatitude($latitude)
    {
        $this->minLatitude = $latitude;
    }

    public function getMaxLatitude()
    {
        return $this->maxLatitude;
    }

    public function setMaxLatitude($latitude)
    {
        $this->maxLatitude = $latitude;
    }

    public function getMinLongitude()
    {
        return $this->minLongitude;
    }

    public function setMinLongitude($longitude)
    {
        $this->minLongitude = $longitude;
    }

    public function getMaxLongitude()
    {
        return $this->maxLongitude;
    }

    public function setMaxLongitude($longitude)
    {
        $this->maxLongitude = $longitude;
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

    public function getLogs()
    {
        return $this->logs->getValues();
    }
    
    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setTerritory($this);
        }
        
        return $this;
    }
    
    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getTerritory() === $this) {
                $log->setTerritory(null);
            }
        }
        
        return $this;
    }

    public function getParents()
    {
        return $this->parents->getValues();
    }

    public function addParent(Territory $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
        }
        
        return $this;
    }
    
    public function removeParent(Territory $parent): self
    {
        if ($this->parents->contains($parent)) {
            $this->parents->removeElement($parent);
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
}
