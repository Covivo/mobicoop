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
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Doctrine\Common\Collections\ArrayCollection;
use App\User\Entity\User;
use App\Community\Entity\Community;
use App\Geography\Entity\Address;
use App\RelayPoint\Entity\RelayPointType;
use App\Image\Entity\Image;
use App\Import\Entity\RelayPointImport;
use App\Solidary\Entity\Structure;
use App\RelayPoint\Filter\TerritoryFilter;

/**
 * A relay point.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readRelayPoint"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeRelayPoint"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security_post_denormalize"="is_granted('relay_point_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "public"={
 *              "method"="GET",
 *              "security_post_denormalize"="is_granted('relay_point_list',object)",
 *              "path"="/relay_points/public",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('relay_point_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/relaypoints",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_relay_point_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/relaypoints",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('relay_point_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "put"={
 *              "security"="is_granted('relay_point_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('relay_point_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Geography"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/relaypoints/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_relay_point_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/relaypoints/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/relaypoints/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"official"})
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "relayPointTypeName"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial","status":"exact","relayPointType.id":"exact"})
 * @ApiFilter(RangeFilter::class, properties={"address.longitude","address.latitude"})
 * @ApiFilter(TerritoryFilter::class, properties={"territory"})
 */
class RelayPoint
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @var int The id of this relay point.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readRelayPoint"})
     */
    private $id;
            
    /**
     * @var string The name of the relay point.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $name;

    /**
     * @var boolean|null The relay point is private to a community or a solidary structure.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $private;
    
    /**
     * @var string|null The short description of the relay point.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $description;
    
    /**
     * @var string|null The full description of the relay point.
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $fullDescription;

    /**
     * @var int The status of the relay point (active/inactive/pending).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $status;

    /**
     * @var int|null The number of places.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $places;

    /**
     * @var int|null The number of places for disabled people.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $placesDisabled;

    /**
    * @var boolean|null The relay point is free.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
    */
    private $free;

    /**
    * @var boolean|null The relay point is secured.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
    */
    private $secured;

    /**
    * @var boolean|null The relay point is official.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
    */
    private $official;

    /**
    * @var boolean|null The relay point appears in the autocompletion.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
    */
    private $suggested;

    /**
     * @var string|null The permalink of the relay point.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     */
    private $permalink;

    /**
    * @var \DateTimeInterface Creation date of the relay point.
    *
    * @ORM\Column(type="datetime")
    * @Groups("readRelayPoint")
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the relay point.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("readRelayPoint")
     */
    private $updatedDate;

    /**
     * @var Address The address of the relay point.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", inversedBy="relayPoint", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"aRead","aWrite","readRelayPoint","writeRelayPoint"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var User The creator of the relay point.
     *
     * @Assert\NotBlank(groups={"writeRelayPoint"})
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readRelayPoint","writeRelayPoint"})
     */
    private $user;

    /**
     * @var Community|null The community of the relay point.
     *
     * @ORM\ManyToOne(targetEntity="App\Community\Entity\Community")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"readRelayPoint","writeRelayPoint"})
     */
    private $community;

    /**
     * @var Structure|null The solidary structure of the relay point.
     *
     * @ORM\ManyToOne(targetEntity="App\Solidary\Entity\Structure")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"readRelayPoint","writeRelayPoint"})
     */
    private $structure;

    /**
     * @ORM\ManyToOne(targetEntity="\App\RelayPoint\Entity\RelayPointType")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"readRelayPoint","writeRelayPoint"})
     */
    private $relayPointType;

    /**
     * @var RelayPointImport|null Relay point imported in the platform.
     *
     * @ORM\OneToMany(targetEntity="\App\Import\Entity\RelayPointImport", mappedBy="relay", cascade={"remove"})
     */
    private $relayPointImport;

    /**
     * @var ArrayCollection|null The images of the relay point.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="relayPoint", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"readRelayPoint","writeRelayPoint"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var int|null The relay point type id
     * @Groups({"aRead","aWrite"})
     */
    private $relayPointTypeId;

    /**
     * @var string The relay point type name
     * @Groups({"aRead","aWrite"})
     */
    private $relayPointTypeName;

    /**
     * @var string|null The relay point type avatar
     * @Groups({"aRead"})
     */
    private $relayPointTypeAvatar;

    /**
     * @var int|null The community id
     * @Groups({"aRead","aWrite"})
     */
    private $communityId;

    /**
     * @var string The community name
     * @Groups({"aRead","aWrite"})
     */
    private $communityName;

    /**
     * @var int|null The structure id
     * @Groups({"aRead","aWrite"})
     */
    private $structureId;

    /**
     * @var string The structure name
     * @Groups({"aRead","aWrite"})
     */
    private $structureName;

    /**
     * @var string The creator
     * @Groups({"aRead","aWrite"})
     */
    private $creator;

    /**
     * @var int The creator id
     * @Groups({"aRead","aWrite"})
     */
    private $creatorId;

    /**
     * @var string|null The creator avatar
     * @Groups({"aRead"})
     */
    private $creatorAvatar;

    /**
     * @var string|null The relay point main image
     * @Groups("aRead")
     */
    private $image;

    /**
     * @var string|null The relay point avatar
     * @Groups("aRead")
     */
    private $avatar;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
        
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }
    
    public function setPrivate(?bool $isPrivate): self
    {
        $this->private = $isPrivate;
        
        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }
    
    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }
    
    public function setFullDescription(?string $fullDescription)
    {
        $this->fullDescription = $fullDescription;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getPlaces()
    {
        return $this->places;
    }
    
    public function setPlaces(?int $places)
    {
        $this->places = $places;
    }

    public function getPlacesDisabled()
    {
        return $this->placesDisabled;
    }
    
    public function setPlacesDisabled(?int $placesDisabled)
    {
        $this->placesDisabled = $placesDisabled;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }
    
    public function setFree(?bool $isFree): self
    {
        $this->free = $isFree;
        
        return $this;
    }

    public function isSecured(): ?bool
    {
        return $this->secured;
    }
    
    public function setSecured(?bool $isSecured): self
    {
        $this->secured = $isSecured;
        
        return $this;
    }

    public function isOfficial(): bool
    {
        return $this->official ? true : false;
    }
    
    public function setOfficial(?bool $isOfficial): self
    {
        $this->official = $isOfficial ? true : false;
        
        return $this;
    }

    public function isSuggested(): ?bool
    {
        return $this->suggested;
    }
    
    public function setSuggested(?bool $isSuggested): self
    {
        $this->suggested = $isSuggested;
        
        return $this;
    }

    public function getPermalink(): ?string
    {
        return $this->permalink;
    }
    
    public function setPermalink(?string $permalink)
    {
        $this->permalink = $permalink;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        $address->setRelayPoint($this);

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }
    
    public function setCommunity(?Community $community): self
    {
        $this->community = $community;
        
        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }
    
    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;
        
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
            $image->setRelayPoint($this);
        }
        
        return $this;
    }
    
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getRelayPoint() === $this) {
                $image->setRelayPoint(null);
            }
        }
        
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

    public function getRelayPointType(): ?RelayPointType
    {
        return $this->relayPointType;
    }

    public function setRelayPointType(?RelayPointType $relayPointType): self
    {
        $this->relayPointType = $relayPointType;

        return $this;
    }

    public function getRelayPointImport(): ?RelayPointImport
    {
        return $this->relayPointImport;
    }

    public function setRelayPointImport(?RelayPointImport $relayPointImport): self
    {
        $this->relayPointImport = $relayPointImport;

        return $this;
    }

    public function getRelayPointTypeId(): ?int
    {
        if (is_null($this->relayPointTypeId)) {
            return $this->getRelayPointType() ? $this->getRelayPointType()->getId() : null;
        }
        return $this->relayPointTypeId;
    }

    public function setRelayPointTypeId(?int $relayPointTypeId)
    {
        $this->relayPointTypeId = $relayPointTypeId;
    }

    public function getRelayPointTypeName(): string
    {
        if ($this->getRelayPointType()) {
            return $this->getRelayPointType()->getName();
        }
        return '';
    }

    public function getRelayPointTypeAvatar(): ?string
    {
        if ($this->getRelayPointType() && $this->getRelayPointType()->getIcon()) {
            return $this->getRelayPointType()->getIcon()->getUrl();
        }
        return null;
    }

    public function getCommunityId(): ?int
    {
        if (is_null($this->communityId)) {
            return $this->getCommunity() ? $this->getCommunity()->getId() : null;
        }
        return $this->communityId;
    }

    public function setCommunityId(?int $communityId)
    {
        $this->communityId = $communityId;
    }

    public function getCommunityName(): string
    {
        if ($this->getCommunity()) {
            return $this->getCommunity()->getName();
        }
        return '';
    }

    public function getStructureId(): ?int
    {
        if (is_null($this->structureId)) {
            return $this->getStructure() ? $this->getStructure()->getId() : null;
        }
        return $this->structureId;
    }

    public function setStructureId(?int $structureId)
    {
        $this->structureId = $structureId;
    }

    public function getStructureName(): string
    {
        if ($this->getStructure()) {
            return $this->getStructure()->getName();
        }
        return '';
    }

    public function getCreator(): string
    {
        return ucfirst(strtolower($this->getUser()->getGivenName())) . " " . $this->getUser()->getShortFamilyName();
    }

    public function getCreatorId(): int
    {
        if (is_null($this->creatorId)) {
            return $this->getUser()->getId();
        }
        return $this->creatorId;
    }

    public function setCreatorId(?int $creatorId)
    {
        $this->creatorId = $creatorId;
    }

    public function getCreatorAvatar(): ?string
    {
        if (count($this->getUser()->getAvatars())>0) {
            return $this->getUser()->getAvatars()[0];
        }
        return null;
    }

    public function getImage(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_800'])) {
            return $this->getImages()[0]->getVersions()['square_800'];
        }
        return null;
    }

    public function getAvatar(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_250'])) {
            return $this->getImages()[0]->getVersions()['square_250'];
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
