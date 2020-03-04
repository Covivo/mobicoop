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
 *      collectionOperations={"get","post"},
 *      itemOperations={
 *          "get",
 *          "put",
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
 *      }
 * )
 */
class Campaign
{
    const STATUS_PENDING = 0;
    const STATUS_CREATED = 1;
    const STATUS_SENT = 2;
    const STATUS_ARCHIVED = 3;

    /**
     * @var int The id of this campaign.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("read_campaign")
     */
    private $id;

    /**
     * @var string Name of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $name;

    /**
     * @var string Subject of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $subject;

    /**
     * @var string Email used to send the campaign.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $email;

    /**
     * @var string Name used to send the campaign.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $fromName;

    /**
     * @var string Reply to email.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $replyTo;

    /**
     * @var string Body of the campaign.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"read_campaign","write_campaign"})
     */
    private $body;

    /**
     * @var int Campaign status.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read_campaign"})
     */
    private $status;

    /**
     * @var Medium The medium used for the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Medium")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_campaign","write_campaign"})
     * @MaxDepth(1)
     */
    private $medium;

    /**
     * @var CampaignTemplate The template used for the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\CampaignTemplate")
     * @Groups({"read_campaign","write_campaign"})
     * @MaxDepth(1)
     */
    private $campaignTemplate;

    /**
     * @var User The user that creates the campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="campaigns")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_campaign","write_campaign"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_campaign"})
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
     * @Groups({"read_campaign","write_campaign"})
     */
    private $deliveries;

    /**
     * @var ArrayCollection The images of the campaign.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="campaign", cascade="remove", orphanRemoval=true)
     * @Groups({"read_campaign","write_campaign"})
     */
    private $images;
    
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
