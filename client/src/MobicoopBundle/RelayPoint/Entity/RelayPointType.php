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

use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Icon;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A relay point type.
 */
class RelayPointType implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this relay point type.
     */
    private $id;

    /**
     * @var string|null The iri of this relay point type.
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string Name of the type.
     *
     * @Groups({"post","put"})
     */
    private $name;

    /**
    * @var ArrayCollection|null The images of the relay point type.
    *
    * @Groups({"post","put"})
    */
    private $images;

    /**
     * @var Icon|null The icon related to the relayPointType.
     *
     * @Groups({"post","put"})
     */
    private $icon;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups("post")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @Groups("post")
     */
    private $updatedDate;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/relay_point_types/".$id);
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

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'iri'               => $this->getIri(),
            'name'              => $this->getName(),
            'images'            => $this->getImages(),
            'icon'              => $this->getIcon()
        ];
    }
}
