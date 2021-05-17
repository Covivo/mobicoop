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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Geography\Entity\Address;
use App\Image\Entity\Image;
use App\User\Entity\User;
use App\Event\Controller\ValidateCreateEventController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Event\Filter\TerritoryFilter;
use App\App\Entity\App;

/**
 * An event : a social occasion or activity.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readEvent"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security_post_denormalize"="is_granted('event_list',object)"
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('event_create',object)"
 *          },
 *          "created"={
 *              "method"="GET",
 *              "path"="/events/created",
 *              "normalization_context"={"groups"={"readEvent"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "userId",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The id of the user for which we want the events"
 *                      }
 *                  }
 *              },
 *              "security_post_denormalize"="is_granted('event_read',object)"
 *          },
 *          "valide_create_event"={
 *              "method"="POST",
 *              "path"="/events/{id}/valide_create_event",
*               "requirements"={"id"="\d+"},
 *              "controller"=ValidateCreateEventController::class,
 *              "security"="is_granted('event_create',object)"
 *          },
 *          "ads"={
 *              "method"="GET",
 *              "path"="/events/{id}/ads",
 *              "normalization_context"={"groups"={"readEvent"}},
 *              "security_post_denormalize"="is_granted('event_list_ads',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('event_read',object)"
 *          },
 *          "put"={
 *              "security"="is_granted('event_update',object)"
 *          },
 *          "delete"={
 *              "security"="is_granted('event_delete',object)"
 *          }
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "fromDate", "name", "toDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(DateFilter::class, properties={"toDate","fromDate"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 * @ApiFilter(TerritoryFilter::class, properties={"territory"})
 * @ApiFilter(BooleanFilter::class, properties={"private"})
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
     * @Groups("readEvent")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the event.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readEvent","write"})
     */
    private $name;

    /**
     * @var string Urlkey of the event
     *
     * @Groups({"readEvent"})
     */
    private $urlKey;

    /**
     * @var int The status of the event (active/inactive).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"readEvent","write"})
     */
    private $status;

    /**
     * @var boolean Private event. Should be filtered when event list is publicly displayed.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"readEvent","write"})
     */
    private $private;
    
    /**
     * @var string The short description of the event.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readEvent","write"})
     */
    private $description;
    
    /**
     * @var string The full description of the event.
     *
     * @ORM\Column(type="text")
     * @Groups({"readEvent","write"})
     */
    private $fullDescription;
    
    /**
     * @var \DateTimeInterface The starting date of the event.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     * @Groups({"readEvent","write"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The ending date of the event.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     * @Groups({"readEvent","write"})
     */
    private $toDate;
    
    /**
     * @var boolean Use the time for the starting and ending date of the event.
     *
     * @ORM\Column(type="boolean")
     * @Groups({"readEvent","write"})
     */
    private $useTime;
    
    /**
     * @var string The information url for the event.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
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
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"readEvent","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var App The app creator of the event.
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="App\App\Entity\App")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"readEvent","write"})
     * @MaxDepth(1)
     */
    private $app;

    /**
     * @var Event Event related for the proposal
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Proposal", mappedBy="event")
     * @Groups({"readEvent","write"})
     * @ApiSubresource(maxDepth=1)
     * @MaxDepth(1)
     */
    private $proposals;
    
    /**
     * @var Address The address of the event.
     *
     * @ApiProperty(push=true)
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", inversedBy="event", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"readEvent","write"})
     * @MaxDepth(1)
     */
    private $address;
    
    /**
     * @var ArrayCollection The images of the event.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="event", cascade="remove", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("readEvent")
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var string $defaultAvatar Url of the default Avatar for an event
     * @Groups("readEvent")
     */
    private $defaultAvatar;

    /**
     * @var int The id of this external event.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalId;
    
    /**
     * @var string The source of the external event.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalSource;

    /**
     * @var string The url of the image of the external event.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalImageUrl;
    
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
    
    public function getUrlKey(): ?string
    {
        return $this->urlKey;
    }
    
    public function setUrlKey(?string $urlKey)
    {
        $this->urlKey = $urlKey;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
    
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function isPrivate(): bool
    {
        return $this->private ? true : false;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

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
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }

    public function getApp(): ?App
    {
        return $this->app;
    }
    
    public function setApp(?App $app): self
    {
        $this->app = $app;
        
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

    public function setDefaultAvatar(?string $defaultAvatar): self
    {
        $this->defaultAvatar = $defaultAvatar;

        return $this;
    }

    public function getDefaultAvatar(): ?string
    {
        return $this->defaultAvatar;
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

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }
    
    public function setExternalId(?int $externalId)
    {
        $this->externalId = $externalId;
    }

    public function getExternalSource(): ?string
    {
        return $this->externalSource;
    }
    
    public function setExternalSource(?string $externalSource)
    {
        $this->externalSource = $externalSource;
    }

    public function getExternalImageUrl(): ?string
    {
        return $this->externalImageUrl;
    }
    
    public function setExternalImageUrl(?string $externalImageUrl)
    {
        $this->externalImageUrl = $externalImageUrl;
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
