<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Editorial\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * An editorial
 */
class Editorial implements ResourceInterface, \JsonSerializable
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    /**
     * @var int The id of this EDITORIAL.
     */
    private $id;
    
    /**
     * @var string The title of the editorial.
     */
    private $title;

    /**
     * @var string The text of the editorial.
     */
    private $text;

    /**
     * @var string Label of the button of the editorial content
     */
    private $label;

    /**
     * @var string The url linked to the editorial content
     */
    private $link;

    /**
     * @var Image[]|null The images of the event.
     */
    private $images;

    /**
    * @var \DateTimeInterface Creation date of the editorial.
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the editorial.
     */
    private $updatedDate;
    
    
    public function __construct($id=null)
    {
        $this->id = $id;
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
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getText(): string
    {
        return $this->text;
    }
    
    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
    
    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function getLink(): string
    {
        return $this->link;
    }
    
    public function setLink(string $link)
    {
        $this->link = $link;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }
    
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setEditorial($this);
        }
        
        return $this;
    }
    
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getEditorial() === $this) {
                $image->setEditorial(null);
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

    public function jsonSerialize()
    {
        return
            [
                'id'     => $this->getId(),
                'title'  => $this->getTitle(),
                'text'   => $this->getText(),
                'label'  => $this->getLabel(),
                'link'   => $this->getLink(),
                'images' => $this->getImages(),
            ];
    }
}
