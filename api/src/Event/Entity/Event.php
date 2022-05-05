<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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
 */

namespace App\Event\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Action\Entity\Log;
use App\App\Entity\App;
use App\Carpool\Entity\Proposal;
use App\Community\Entity\Community;
use App\Event\Controller\ValidateCreateEventController;
use App\Event\Filter\EventAddressTerritoryFilter;
use App\Event\Filter\TerritoryFilter;
use App\Geography\Entity\Address;
use App\Image\Entity\Image;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event : a social occasion or activity.
 *
 * @ORM\Entity()
 * @ORM\Table(indexes={@ORM\Index(name="FULL_TEXT_NAME", columns={"name"}, flags={"fulltext"})})
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
 *              "security_post_denormalize"="is_granted('event_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('event_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "created"={
 *              "method"="GET",
 *              "path"="/events/created",
 *              "normalization_context"={"groups"={"readEvent"}},
 *              "swagger_context" = {
 *                  "tags"={"Events"},
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
 *              "security"="is_granted('event_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "ads"={
 *              "method"="GET",
 *              "path"="/events/{id}/ads",
 *              "normalization_context"={"groups"={"readEvent"}},
 *              "security_post_denormalize"="is_granted('event_list_ads',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events", "Carpool"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/events",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_event_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/events",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_event_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('event_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "put"={
 *              "security"="is_granted('event_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('event_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Events"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/events/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_event_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/events/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_event_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/events/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_event_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "fromDate", "name", "toDate","createdDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(DateFilter::class, properties={"toDate","fromDate"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 * @ApiFilter(NumericFilter::class, properties={"community.id"})
 * @ApiFilter(TerritoryFilter::class, properties={"territory"})
 * @ApiFilter(BooleanFilter::class, properties={"private"})
 * @ApiFilter(EventAddressTerritoryFilter::class, properties={"eventAddressTerritoryFilter"})
 */
class Event
{
    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    /**
     * @var int the id of this event
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readEvent", "read" })
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string the name of the event
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $name;

    /**
     * @var string Urlkey of the event
     *
     * @Groups({"readEvent"})
     */
    private $urlKey;

    /**
     * @var int the status of the event (active/inactive)
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $status;

    /**
     * @var bool Private event. Should be filtered when event list is publicly displayed.
     *
     * @ORM\Column(type="boolean", options={"default":0})
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $private;

    /**
     * @var string the short description of the event
     *
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $description;

    /**
     * @var string the full description of the event
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $fullDescription;

    /**
     * @var \DateTimeInterface the starting date of the event
     *
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface the ending date of the event
     *
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $toDate;

    /**
     * @var bool use the time for the starting and ending date of the event
     *
     * @ORM\Column(type="boolean")
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $useTime;

    /**
     * @var string the information url for the event
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readEvent","write"})
     */
    private $url;

    /**
     * @var \DateTimeInterface creation date of the event
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the event
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var User the creator of the event
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"readEvent","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var App the app creator of the event
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="App\App\Entity\App")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
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
     * @var Address the address of the event
     *
     * @ApiProperty(push=true)
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", inversedBy="event", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"aRead","aWrite","readEvent","write"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var ArrayCollection the images of the event
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="event", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups("readEvent")
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var string Url of the default Avatar for an event
     * @Groups("readEvent")
     */
    private $defaultAvatar;

    /**
     * @var string the id of this external event
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalId;

    /**
     * @var string the source of the external event
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalSource;

    /**
     * @var string the url of the image of the external event
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readEvent","write"})
     */
    private $externalImageUrl;

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
     * @var null|string The creator avatar
     * @Groups({"aRead"})
     */
    private $creatorAvatar;

    /**
     * @var null|string The event main image
     * @Groups("aRead")
     */
    private $image;

    /**
     * @var null|string The event avatar
     * @Groups("aRead")
     */
    private $avatar;

    /**
     * @var ArrayCollection the logs linked with the Event
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="event")
     */
    private $logs;

    /**
     * @var Community Community linked to the event
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="App\Community\Entity\Community")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"readEvent","write", "aRead", "aWrite"})
     * @MaxDepth(1)
     */
    private $community;

    /**
     * @var null|int The community id
     * @Groups({"aRead","aWrite"})
     */
    private $communityId;

    /**
     * @var string The community name
     * @Groups({"aRead","aWrite"})
     */
    private $communityName;

    public function __construct($id = null)
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

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId)
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

    public function getCreator(): string
    {
        if (!$this->getUser()) {
            return '';
        }

        return ucfirst(strtolower($this->getUser()->getGivenName())).' '.$this->getUser()->getShortFamilyName();
    }

    public function getCreatorId(): ?int
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
        if (!$this->getUser()) {
            return null;
        }
        if (count($this->getUser()->getAvatars()) > 0) {
            return $this->getUser()->getAvatars()[0];
        }

        return null;
    }

    public function getImage(): ?string
    {
        if (count($this->getImages()) > 0 && isset($this->getImages()[0]->getVersions()['square_800'])) {
            return $this->getImages()[0]->getVersions()['square_800'];
        }

        return null;
    }

    public function getAvatar(): ?string
    {
        if (count($this->getImages()) > 0 && isset($this->getImages()[0]->getVersions()['square_250'])) {
            return $this->getImages()[0]->getVersions()['square_250'];
        }

        return null;
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

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
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

    public function getLogs()
    {
        return $this->logs->getValues();
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setEvent($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getEvent() === $this) {
                $log->setEvent(null);
            }
        }

        return $this;
    }
}
