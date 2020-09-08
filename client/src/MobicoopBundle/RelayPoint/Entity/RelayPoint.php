<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Structure;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPointType;

/**
 * A relay point.
 */
class RelayPoint implements ResourceInterface, \JsonSerializable
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @var int The id of this relay point.
     */
    private $id;

    /**
     * @var string|null The iri of this relay point.
     *
     * @Groups({"post","put"})
     */
    private $iri;
            
    /**
     * @var string The name of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $name;

    /**
     * @var boolean|null The relay point is private to a community or a solidary structure.
     *
     * @Groups({"post","put"})
     */
    private $private;
    
    /**
     * @var string The short description of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $description;
    
    /**
     * @var string The full description of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $fullDescription;

    /**
     * @var int The status of the relay point (active/inactive/pending).
     *
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var int|null The number of places.
     *
     * @Groups({"post","put"})
     */
    private $places;

    /**
     * @var int|null The number of places for disabled people.
     *
     * @Groups({"post","put"})
     */
    private $placesDisabled;

    /**
    * @var boolean|null The relay point is free.
    *
    * @Groups({"post","put"})
    */
    private $free;

    /**
    * @var boolean|null The relay point is secured.
    *
    * @Groups({"post","put"})
    */
    private $secured;

    /**
    * @var boolean|null The relay point is official.
    *
    * @Groups({"post","put"})
    */
    private $official;

    /**
    * @var boolean|null The relay point appears in the autocompletion.
    *
    * @Groups({"post","put"})
    */
    private $suggested;

    /**
     * @var string|null The permalink of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $permalink;

    /**
    * @var \DateTimeInterface Creation date of the relay point.
    *
    * @Groups("post")
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the relay point.
     *
     * @Groups("post")
     */
    private $updatedDate;

    /**
     * @var Address The address of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $address;

    /**
     * @var User The creator of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $user;

    /**
     * @var Community|null The community of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $community;

    /**
     * @var Structure|null The solidary structure of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $structure;

    /**
     * @var RelayPointType|null The relay point types.
     *
     * @Groups({"post","put"})
     */
    private $relayPointType;

    /**
     * @var ArrayCollection|null The images of the relay point.
     *
     * @Groups({"post","put"})
     */
    private $images;

    /**
     * Undocumented variable
     *
     * @var string|null
     */
    private $lat;

    /**
     * Undocumented variable
     *
     * @var string|null
     */
    private $lon;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/relay_points/".$id);
        }
        $this->images = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
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

    public function getRelayPointType(): ?RelayPointType
    {
        return $this->relayPointType;
    }
    
    public function setRelayPointType(?RelayPointType $relayPointType): self
    {
        $this->relayPointType = $relayPointType;
        
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

    public function getLat(): ?string
    {
        return $this->address->getLatitude();
    }
    
    public function setLat(?string $lat)
    {
        $this->lat = $lat;
    }

    public function getLon(): ?string
    {
        return $this->address->getLongitude();
    }
    
    public function setLon(?string $lon)
    {
        $this->lon = $lon;
    }

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'iri'               => $this->getIri(),
            'name'              => $this->getName(),
            'private'           => $this->isPrivate(),
            'description'       => $this->getDescription(),
            'fullDescription'   => $this->getFullDescription(),
            'status'            => $this->getStatus(),
            'places'            => $this->getPlaces(),
            'placesDisabled'    => $this->getPlacesDisabled(),
            'free'              => $this->isFree(),
            'secured'           => $this->isSecured(),
            'official'          => $this->isOfficial(),
            'suggested'         => $this->isSuggested(),
            'permalink'         => $this->getPermalink(),
            'images'            => $this->getImages(),
            'address'           => $this->getAddress(),
            'relayPointType'   => $this->getRelayPointType(),
            'images'            => $this->getImages(),
        ];
    }
}
