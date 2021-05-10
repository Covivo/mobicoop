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

namespace App\RelayPoint\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use App\Image\Entity\Image;
use App\Image\Entity\Icon;

/**
 * A relay point type.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"App\RelayPoint\EntityListener\RelayPointTypeListener"})
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readRelayPoint"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeRelayPoint"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security_post_denormalize"="is_granted('relay_point_type_list',object)"
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('relay_point_type_create',object)"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/relaypoint_types",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_relay_point_type_list',object)"
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/relaypoint_types",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_type_create',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('relay_point_type_read',object)"
 *          },
 *          "put"={
 *              "security"="is_granted('relay_point_type_update',object)"
 *          },
 *          "delete"={
 *              "security"="is_granted('relay_point_type_delete',object)"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/relaypoint_types/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_relay_point_type_read',object)"
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/relaypoint_types/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_type_update',object)"
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/relaypoint_types/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_type_delete',object)"
 *          },
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 */
class RelayPointType
{
    /**
     * @var int The id of this relay point type.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","readRelayPoint"})
     */
    private $id;

    /**
     * @var string Name of the type.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $name;

    /**
    * @var ArrayCollection|null The images of the relay point type.
    *
    * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="relayPointType", cascade={"persist","remove"}, orphanRemoval=true)
    * @ORM\OrderBy({"position" = "ASC"})
    * @Groups({"readRelayPoint","writeRelayPoint"})
    * @MaxDepth(1)
    * @ApiSubresource(maxDepth=1)
    */
    private $images;

    /**
     * @var Icon|null The icon related to the relayPointType.
     *
     * @ORM\ManyToOne(targetEntity="\App\Image\Entity\Icon", inversedBy="relayPointTypes")
     * @Groups({"readRelayPoint","writeRelayPoint"})
     * @MaxDepth(1)
     */
    private $icon;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readRelayPoint"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readRelayPoint"})
     */
    private $updatedDate;

    /**
     * @var int|null The relay point type icon id
     * @Groups({"aRead","aWrite"})
     */
    private $iconId;

    /**
     * @var string|null The relay point type icon name
     * @Groups("aRead")
     */
    private $iconName;

    /**
     * @var string|null The relay point type private icon name
     * @Groups("aRead")
     */
    private $iconPrivateName;

    /**
     * @var string|null The relay point type icon url
     * @Groups("aRead")
     */
    private $iconUrl;

    /**
     * @var string|null The relay point type private icon url
     * @Groups("aRead")
     */
    private $iconPrivateUrl;


    public function __construct()
    {
        $this->images = new ArrayCollection();
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
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }
    
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setRelayPointType($this);
        }
        
        return $this;
    }
    
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getRelayPointType() === $this) {
                $image->setRelayPointType(null);
            }
        }
        
        return $this;
    }

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function setIcon(?Icon $icon): self
    {
        $this->icon = $icon;

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

    public function getIconId(): int
    {
        if (is_null($this->iconId)) {
            return $this->getIcon()->getId();
        }
        return $this->iconId;
    }

    public function setIconId(?int $iconId)
    {
        $this->iconId = $iconId;
    }

    public function getIconName(): ?string
    {
        if ($this->getIcon()) {
            return $this->getIcon()->getName();
        }
        return null;
    }

    public function getIconPrivateName(): ?string
    {
        if ($this->getIcon() && $this->getIcon()->getPrivateIconLinked()) {
            return $this->getIcon()->getPrivateIconLinked()->getName();
        }
        return null;
    }

    public function getIconUrl(): ?string
    {
        if ($this->getIcon()) {
            return $this->getIcon()->getUrl();
        }
        return null;
    }

    public function getIconPrivateUrl(): ?string
    {
        if ($this->getIcon() && $this->getIcon()->getPrivateIconLinked()) {
            return $this->getIcon()->getPrivateIconLinked()->getUrl();
        }
        return null;
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
