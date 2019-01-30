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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Event\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;

/**
 * An event.
 */
class Event implements Resource
{
    /**
     * @var int The id of this event.
     */
    private $id;

    /**
     * @var string|null The iri of this event.
     *
     * @Groups({"post","put"})
     */
    private $iri;
    
    /**
     * @var string The name of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $name;
    
    /**
     * @var int The status of the event (active/inactive).
     *
     * @Groups({"post","put"})
     */
    private $status;
    
    /**
     * @var string The short description of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $description;
    
    /**
     * @var string The full description of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $fullDescription;
    
    /**
     * @var \DateTimeInterface The starting date of the event.
     *
     * @Assert\NotBlank(groups={"create","update"})
     * @Assert\DateTime(groups={"create","update"})
     * @Groups({"post","put"})
     */
    private $fromDate;
    
    /**
     * @var \DateTimeInterface The ending date of the event.
     *
     * @Assert\NotBlank
     * @Assert\DateTime()
     * @Groups({"post","put"})
     */
    private $toDate;
    
    /**
     * @var boolean Use the time for the starting and ending date of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $useTime;
    
    /**
     * @var string The information url for the event.
     *
     * @Groups({"post","put"})
     */
    private $url;
    
    /**
     * @var Address The address of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $address;
    
    /**
     * @var User The creator of the event.
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $user;
    
    /**
     * var Image[]|null The images of the event.
     *
     * Groups({"post","put"})
     * @Assert\Valid
     */
    private $images;
    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/events/".$id);
        }
        $this->images = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
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
    
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    public function getStatus(): ?int
    {
        return $this->status;
    }
    
    public function setStatus(int $status)
    {
        $this->status = $status;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    
    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }
    
    public function setFullDescription(string $fullDescription)
    {
        $this->fullDescription = $fullDescription;
    }
    
    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }
    
    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;
        
        return $this;
    }
    
    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }
    
    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;
        
        return $this;
    }
    
    public function getUseTime(): ?bool
    {
        return $this->useTime;
    }
    
    public function setUseTime(bool $useTime): self
    {
        $this->useTime = $useTime;
        
        return $this;
    }
    
    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(?string $url)
    {
        $this->url = $url;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getAddress(): ?Address
    {
        return $this->address;
    }
    
    public function setAddress(Address $address): self
    {
        $this->address = $address;
        
        return $this;
    }
    
    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setEvent($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getEvent() === $this) {
                $image->setEvent(null);
            }
        }

        return $this;
    }
    
    public function removeImages(): self
    {
        foreach ($this->images as $image) {
            $this->removeImage($image);
        }
        return $this;
    }
}
