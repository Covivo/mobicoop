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

namespace App\MassCommunication\Entity;

use App\Image\Entity\Image;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Communication\Entity\Medium;
use App\User\Entity\User;
use App\MassCommunication\Controller\CampaignSend;
use App\MassCommunication\Controller\CampaignSendTest;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Action\Entity\Log;

/**
 * A mass communication campaign.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read_campaign"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write_campaign"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "owned"={
 *              "method"="GET",
 *              "path"="/campaigns/owned",
 *              "normalization_context"={"groups"={"read_campaign"}, "enable_max_depth"="true"},
 *              "security_post_denormalize"="is_granted('community_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/campaigns",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_campaign_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/campaigns",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "unsubscribeHook"={
 *              "path"="/campaigns/unsubscribe",
 *              "method"="GET",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "put"={
 *              "normalization_context"={"groups"={"update_campaign"}, "enable_max_depth"="true"},
 *              "denormalization_context"={"groups"={"update_campaign"}},
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "send"={
 *              "method"="GET",
 *              "controller"=CampaignSend::class,
 *              "path"="/campaigns/send/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "send-test"={
 *              "method"="GET",
 *              "controller"=CampaignSendTest::class,
 *              "path"="/campaigns/send-test/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Mass Communication"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_campaign_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"status":"exact","name":"partial","email":"partial"})
 * @ApiFilter(DateFilter::class, properties={"createdDate": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(OrderFilter::class, properties={"subject", "user", "email", "status", "createdDate", "updatedDate"}, arguments={"orderParameterName"="order"})
 */
class Campaign
{
    const STATUS_PENDING = 0;   // when the campaign has not been tested yet
    const STATUS_CREATED = 1;   // when the campaign has been successfully tested
    const STATUS_SENT = 2;      // when the campaign was sent
    const STATUS_ARCHIVED = 3;  // when the campaign is archived (not editable anymore)

    const SOURCE_USER = 1;          // user resource as source
    const SOURCE_COMMUNITY = 2;     // community members as source

    const SOURCES = [
        self::SOURCE_USER,
        self::SOURCE_COMMUNITY
    ];

    const FILTER_TYPE_SELECTION = 1;    // filter using a selection of users
    const FILTER_TYPE_FILTER = 2;       // filter using a resource filter (empty filter to get all users)

    const FILTER_TYPES = [
        self::FILTER_TYPE_SELECTION,
        self::FILTER_TYPE_FILTER
    ];

    /**
     * @var int The id of this campaign.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups({"aRead","read_campaign","update_campaign"})
     */
    private $id;

    /**
     * @var string Name of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","read_campaign","write_campaign","update_campaign"})
     */
    private $name;

    /**
     * @var string Subject of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","read_campaign","write_campaign","update_campaign"})
     */
    private $subject;

    /**
     * @var string Email used to send the campaign.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","read_campaign","write_campaign","update_campaign"})
     */
    private $email;

    /**
     * @var string Name used to send the campaign.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $fromName;

    /**
     * @var string Reply to email.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $replyTo;

    /**
     * @var string Body of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"aRead","aWrite","read_campaign","write_campaign","update_campaign"})
     */
    private $body;

    /**
     * @var int Campaign status.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","read_campaign"})
     */
    private $status;

    /**
     * @var int provider campaign id associated to the campaign.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $providerCampaignId;

    /**
     * @var Medium The medium used for the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Medium")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $medium;

    /**
     * @var CampaignTemplate The template used for the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\CampaignTemplate")
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     * @MaxDepth(1)
     */
    private $campaignTemplate;

    /**
     * @var User The user that creates the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="campaigns")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead","read_campaign"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead","read_campaign"})
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null The deliveries related to this campaign, if the filter type is selection.
     *
     * @ORM\OneToMany(targetEntity="\App\MassCommunication\Entity\Delivery", mappedBy="campaign", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $deliveries;

    /**
     * @var ArrayCollection The images of the campaign.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="campaign", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $images;

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
     * @var string|null The creator email
     * @Groups({"aRead"})
     */
    private $creatorEmail;

    /**
     * @var int|null The source for the deliveries.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aWrite"})
     */
    private $source;

    /**
     * @var int|null The source id for the deliveries.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"aRead","aWrite"})
     */
    private $sourceId;

    /**
     * @var string|null The source name for the deliveries.
     *
     * @Groups({"aRead"})
     */
    private $sourceName;

    /**
     * @var int|null The filter type for the deliveries selection.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aWrite"})
     */
    private $filterType;

    /**
     * @var string|null The filter string for the deliveries selection.
     *
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"aRead","aWrite"})
     */
    private $filters;

    /**
     * @var int|null The number of deliveries.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("aRead")
     */
    private $deliveryCount;

    /**
     * @var ArrayCollection The logs linked with the Campaign.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="campaign", cascade={"remove"})
     */
    private $logs;

    public function __construct()
    {
        if (is_null($this->status)) {
            $this->status = self::STATUS_PENDING;
        }
        $this->deliveries = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProviderCampaignId(): ?int
    {
        return $this->providerCampaignId;
    }

    public function setProviderCampaignId(int $providerCampaignId): self
    {
        $this->providerCampaignId = $providerCampaignId;

        return $this;
    }

    public function getMedium(): Medium
    {
        return $this->medium;
    }

    public function setMedium(?Medium $medium): self
    {
        $this->medium = $medium;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCampaignTemplate(): ?CampaignTemplate
    {
        return $this->campaignTemplate;
    }

    public function setCampaignTemplate(?CampaignTemplate $campaignTemplate): self
    {
        $this->campaignTemplate = $campaignTemplate;

        return $this;
    }

    public function getDeliveries()
    {
        return $this->deliveries->getValues();
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setCampaign($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->contains($delivery)) {
            $this->deliveries->removeElement($delivery);
            // set the owning side to null (unless already changed)
            if ($delivery->getCampaign() === $this) {
                $delivery->setCampaign(null);
            }
        }

        return $this;
    }

    public function removeDeliveries(): self
    {
        $this->deliveries->clear();
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

    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCampaign($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getCampaign() === $this) {
                $image->setCampaign(null);
            }
        }

        return $this;
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

    public function getCreatorEmail(): string
    {
        return $this->getUser()->getEmail();
    }

    public function getSource(): ?int
    {
        return $this->source;
    }

    public function setSource(int $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceId(): ?int
    {
        return $this->sourceId;
    }

    public function setSourceId(?int $sourceId): self
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function getSourceName(): ?string
    {
        return $this->sourceName;
    }

    public function setSourceName(?string $sourceName): self
    {
        $this->sourceName = $sourceName;

        return $this;
    }

    public function getFilterType(): ?int
    {
        return $this->filterType;
    }

    public function setFilterType(int $filterType): self
    {
        $this->filterType = $filterType;

        return $this;
    }

    public function getFilters(): ?string
    {
        return $this->filters;
    }

    public function setFilters(?string $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getDeliveryCount(): ?int
    {
        if ($this->filterType == self::FILTER_TYPE_SELECTION) {
            return count($this->deliveries);
        } else {
            return $this->deliveryCount;
        }
    }

    public function setDeliveryCount(int $deliveryCount): self
    {
        $this->deliveryCount = $deliveryCount;

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
            $log->setCampaign($this);
        }
        
        return $this;
    }
    
    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getCampaign() === $this) {
                $log->setCampaign(null);
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
}
