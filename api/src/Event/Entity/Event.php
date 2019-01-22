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

namespace App\Event\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Geography\Entity\Address;
use App\Image\Entity\Image;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Event
{
    /**
     * @var int The id of this event.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the event.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var int The status of the event (active/inactive).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;
    
    /**
     * @var string The short description of the event.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $description;
    
    /**
     * @var string The full description of the event.
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $fullDescription;
    
    /**
     * @var \DateTimeInterface The starting date of the event.
     *
     * @Assert\NotBlank
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The ending date of the event.
     *
     * @Assert\NotBlank
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $toDate;
    
    /**
     * @var boolean Use the time for the starting and ending date of the event.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"read","write"})
     */
    private $useTime;
    
    /**
     * @var string The information url for the event.
     *
     * @ORM\Column(type="string", length=255, nullable="true")
     * @Groups({"read","write"})
     */
    private $url;
    
    /**
     * @var User The creator of the event.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $user;
    
    /**
     * @var Address The address of the event.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;
    
    /**
     * @var Image[] The images of the event.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="event", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;
    
    public function __construct($id=null)
    {
        $this->id = $id;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
    
    public function setStatus(int $status)
    {
        $this->status = $status;
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
    
    public function getFromDate(): \DateTimeInterface
    {
        return $this->fromDate;
    }
    
    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;
        
        return $this;
    }
    
    public function getToDate(): \DateTimeInterface
    {
        return $this->toDate;
    }
    
    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;
        
        return $this;
    }
    
    public function getUseTime(): bool
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
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getAddress(): Address
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
    
    
}