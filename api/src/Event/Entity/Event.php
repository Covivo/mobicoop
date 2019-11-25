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

use App\Carpool\Entity\Proposal;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Geography\Entity\Address;
use App\Image\Entity\Image;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event : a social occasion or activity.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "fromDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(DateFilter::class, properties={"toDate"})
 */
class Event
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
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
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The ending date of the event.
     *
     * @Assert\NotBlank
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $url;
    
    /**
    * @var \DateTimeInterface Creation date of the event.
    *
    * @ORM\Column(type="datetime")
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the event.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;
    
    /**
     * @var User The creator of the event.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("write")
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Event Event related for the proposal
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Proposal", mappedBy="event")
     * @Groups({"read","write"})
     * @ApiSubresource(maxDepth=1)
     * @MaxDepth(1)
     */
    private $proposals;
    
    /**
     * @var Address The address of the event.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", inversedBy="event", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;
    
    /**
     * @var ArrayCollection The images of the event.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="event", cascade="remove", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("read")
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;
    
    public function __construct($id=null)
    {
        $this->id = $id;
        $this->images = new ArrayCollection();
        $this->proposals = new ArrayCollection();
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
        $address->setEvent($this);

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

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setEvent($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getEvent() === $this) {
                $proposal->setEvent(null);
            }
        }

        return $this;
    }
}
