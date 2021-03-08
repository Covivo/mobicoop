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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;

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
 *          "get",
 *          "post",
 *          "owned"={
 *              "method"="GET",
 *              "path"="/campaigns/owned",
 *              "normalization_context"={"groups"={"read_campaign"}, "enable_max_depth"="true"},
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/campaigns",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_campaign_list',object)"
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/campaigns",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_create',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "normalization_context"={"groups"={"update_campaign"}, "enable_max_depth"="true"},
 *              "denormalization_context"={"groups"={"update_campaign"}},
 *          },
 *          "delete",
 *          "send"={
 *              "method"="GET",
 *              "controller"=CampaignSend::class,
 *              "path"="/campaigns/send/{id}"
 *          },
 *          "send-test"={
 *              "method"="GET",
 *              "controller"=CampaignSendTest::class,
 *              "path"="/campaigns/send-test/{id}"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_campaign_read',object)"
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_update',object)"
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/campaigns/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_campaign_delete',object)"
 *          },
 *          "ADMIN_send"={
 *              "path"="/admin/campaigns/send/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_campaign_send',object)"
 *          },
 *          "ADMIN_test"={
 *              "path"="/admin/campaigns/test/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_campaign_test',object)"
 *          },
 *      }
 * )
 * @ApiFilter(DateFilter::class, properties={"createdDate": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(DateFilter::class, properties={"lastActivityDate": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(OrderFilter::class, properties={"subject", "user", "status"}, arguments={"orderParameterName"="order"})
 */
class Campaign
{
    const STATUS_PENDING = 0;
    const STATUS_CREATED = 1;
    const STATUS_SENT = 2;
    const STATUS_ARCHIVED = 3;

    const SOURCE_USER = 1;
    const SOURCE_COMMUNITY = 2;

    const FILTER_TYPE_SELECTION = 1;
    const FILTER_TYPE_ALL = 2;
    const FILTER_TYPE_FILTER = 3;

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
     * @Groups({"read_campaign"})
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null The deliveries related to this campaign.
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
     * @var int Status to send all campaign
     * null -> send to chosen deleveries
     * 0 -> send to all user
     * id community -> send to all user in the given community ID
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"read_campaign","write_campaign","update_campaign"})
     */
    private $sendAll;

    /**
     * @var int|null The source for the deliveries.
     *
     * @Groups({"aread","aWrite"})
     */
    private $source;

    /**
     * @var int|null The source id for the deliveries.
     *
     * @Groups({"aread","aWrite"})
     */
    private $sourceId;

    /**
     * @var int|null The filter type for the deliveries selection.
     *
     * @Groups({"aread","aWrite"})
     */
    private $filterType;

    /**
     * @var array|null The filter array for the deliveries selection.
     *
     * @Groups({"aread","aWrite"})
     */
    private $filters;

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

    public function getSendAll(): ?int
    {
        return $this->sendAll;
    }

    public function setSendAll($sendAll): self
    {
        $this->sendAll = $sendAll;

        return $this;
    }

    public function getSource(): int
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

    public function getFilterType(): int
    {
        return $this->filterType;
    }

    public function setFilterType(int $filterType): self
    {
        $this->filterType = $filterType;

        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters): self
    {
        $this->filters = $filters;

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
