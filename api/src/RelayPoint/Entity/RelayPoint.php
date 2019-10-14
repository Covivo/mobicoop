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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Doctrine\Common\Collections\ArrayCollection;
use App\User\Entity\User;
use App\Community\Entity\Community;
use App\Geography\Entity\Address;
use App\RelayPoint\Entity\RelayPointType;
use App\Image\Entity\Image;
use App\RelayPoint\Controller\RelayPointUpdate;

/**
 * A relay point.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "method"="PUT",
 *              "path"="/relay_points/{id}",
 *              "controller"=RelayPointUpdate::class,
 *          },
 *          "delete"
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial","status":"exact"})
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
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string The name of the relay point.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var boolean|null The relay point is private to a community.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $private;
    
    /**
     * @var string The short description of the relay point.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $description;
    
    /**
     * @var string The full description of the relay point.
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $fullDescription;

    /**
     * @var int The status of the relay point (active/inactive/pending).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var int|null The status of the relay point (active/inactive/pending).
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"read","write"})
     */
    private $places;

    /**
     * @var int|null The status of the relay point (active/inactive/pending).
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"read","write"})
     */
    private $placesDisabled;

    /**
    * @var boolean|null The relay point is free.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"read","write"})
    */
    private $free;

    /**
    * @var boolean|null The relay point is secured.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"read","write"})
    */
    private $secured;

    /**
    * @var boolean|null The relay point is official.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"read","write"})
    */
    private $official;

    /**
    * @var boolean|null The relay point appears in the autocompletion.
    *
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups({"read","write"})
    */
    private $suggested;

    /**
     * @var string|null The permalink of the relay point.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $permalink;

    /**
    * @var \DateTimeInterface Creation date of the relay point.
    *
    * @ORM\Column(type="datetime")
    * @Groups("read")
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the relay point.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("read")
     */
    private $updatedDate;

    /**
     * @var Address The address of the relay point.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var Address The new address of the relay point.
     * Used for administration purpose.
     *
     * @Groups({"read","write"})
     */
    private $newAddress;

    /**
     * @var User The creator of the relay point.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $user;

    /**
     * @var Community|null The community of the relay point.
     *
     * @ORM\ManyToOne(targetEntity="App\Community\Entity\Community")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"read","write"})
     */
    private $community;

    /**
     * @var ArrayCollection|null The relay point types.
     *
     * @ORM\ManyToMany(targetEntity="\App\RelayPoint\Entity\RelayPointType")
     * @Groups({"read","write"})
     */
    private $relayPointTypes;

    /**
     * @var ArrayCollection|null The images of the relay point.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="relayPoint", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    public function __construct()
    {
        $this->relayPointTypes = new ArrayCollection();
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
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    
    public function getFullDescription(): string
    {
        return $this->fullDescription;
    }
    
    public function setFullDescription(string $fullDescription)
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

    public function isOfficial(): ?bool
    {
        return $this->official;
    }
    
    public function setOfficial(?bool $isOfficial): self
    {
        $this->official = $isOfficial;
        
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

        return $this;
    }

    public function getNewAddress(): ?Address
    {
        return $this->newAddress;
    }
    
    public function setNewAddress(?Address $newAddress): self
    {
        $this->newAddress = $newAddress;
        
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

    public function getRelayPointTypes()
    {
        return $this->relayPointTypes->getValues();
    }
    
    public function addRelayPointType(RelayPointType $relayPointType): self
    {
        if (!$this->relayPointTypes->contains($relayPointType)) {
            $this->relayPointTypes[] = $relayPointType;
        }
        
        return $this;
    }
    
    public function removeRelayPointType(RelayPointType $relayPointType): self
    {
        if ($this->relayPointTypes->contains($relayPointType)) {
            $this->relayPointTypes->removeElement($relayPointType);
        }
        
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
