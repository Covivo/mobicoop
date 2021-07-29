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

namespace App\Image\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Event\Entity\Event;
use App\Community\Entity\Community;
use App\Gamification\Entity\Badge;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Entity\RelayPointType;
use App\MassCommunication\Entity\Campaign;
use App\Editorial\Entity\Editorial;
use App\Image\Controller\CreateImageAction;
use App\Image\Controller\CreateImageAdminCampaignController;
use App\Image\Controller\ImportImageCommunityController;
use App\Image\Controller\ImportImageEventController;
use App\Image\Controller\ImportImageUserController;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\User\Entity\User;
use App\Image\Admin\Controller\PostImageAction;

/**
 * An uploaded image (for a user, an event, a community and so on).
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"App\Image\EntityListener\ImageListener"})
  * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('image_list',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "controller"=CreateImageAction::class,
 *              "deserialize"=false,
 *              "security_post_denormalize"="is_granted('image_create',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          },
 *          "regenVersions"={
 *              "method"="GET",
 *              "path"="/images/regenversions",
 *              "security"="is_granted('images_regenversions',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/images",
 *              "method"="POST",
 *              "controller"=PostImageAction::class,
 *              "deserialize"=false,
 *              "defaults"={"_api_receive"=false},
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security_post_denormalize"="is_granted('admin_image_post',object)",
 *             "swagger_context" = {
 *                  "tags"={"Administration"}
 *             }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('image_read',object)",
 *             "swagger_context" = {
 *                  "tags"={"Pictures"}
 *             }
 *          },
 *          "delete"={
 *             "security"="is_granted('image_delete',object)",
 *          }
 *      }
 * )
 * @Vich\Uploadable
 */
class Image
{
    /**
     * @var int The id of this image.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     */
    private $name;

    /**
     * @var string The html title of the image.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     */
    private $title;
    
    /**
     * @var string The html alt of the image.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("read")
     */
    private $alt;
    
    /**
     * @var int The left coordinate of the crop, in percentage of the full width.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropX1;

    /**
     * @var int The top coordinate of the crop, in percent of the full height.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropY1;
    
    /**
     * @var int The right coordinate of the crop, in percentage of the full width.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropX2;
    
    /**
     * @var int The bottom coordinate of the crop, in percent of the full height.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropY2;
    
    /**
     * @var string The final file name of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","results","write","readUser","readRelayPoint","readEditorial"})
     */
    private $fileName;
    
    /**
     * @var string The original file name of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $originalName;
    
    /**
     * @var array The original dimensions of the image.
     */
    private $dimensions;
    
    /**
    * @var int The width of the image in pixels.
    *
    * @ORM\Column(type="integer")
    * @Groups({"read","write"})
    */
    private $width;
    
    /**
     * @var int The height of the image in pixels.
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $height;
    
    /**
     * @var int The size in bytes of the image.
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $size;
    
    /**
     * @var string The mime type of the image.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $mimeType;
    
    /**
     * @var int The position of the image if mulitple images are related to the same entity.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","aWrite"})
     */
    private $position;
    
    /**
     * @var \DateTimeInterface Creation date of the image.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the image.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;
    
    /**
     * @var Event|null The event associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\Event\Entity\Event", inversedBy="images", cascade="persist")
     */
    private $event;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="event", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $eventFile;
    
    /**
     * @var int|null The event id associated with the image.
     * @Groups({"read","write"})
     */
    private $eventId;

    /**
     * @var Community|null The community associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="images", cascade="persist")
     */
    private $community;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="community", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $communityFile;
    
    /**
     * @var int|null The community id associated with the image.
     * @Groups({"read","write"})
     */
    private $communityId;
    
    /**
     * @var File|null
     * @Vich\UploadableField(mapping="user", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $userFile;
    
    /**
     * @var User|null The user associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="images", cascade="persist")
     */
    private $user;

    /**
     * @var int|null The user id associated with the image.
     * @Groups({"write","results"})
     */
    private $userId;

    /**
     * @var RelayPoint|null The relay point associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\RelayPoint\Entity\RelayPoint", inversedBy="images", cascade="persist")
     */
    private $relayPoint;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="relayPoint", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $relayPointFile;
    
    /**
     * @var int|null The relay point id associated with the image.
     * @Groups({"read","write"})
     */
    private $relayPointId;

    /**
     * @var RelayPointType|null The relay point type associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\RelayPoint\Entity\RelayPointType", inversedBy="images", cascade="persist")
     */
    private $relayPointType;

    /**
     * @var Badge|null The Badge for which this image is used as icon
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="icon", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $badge;

    /**
     * @var int|null The badge id associated with the image (icon).
     * @Groups({"write","read"})
     */
    private $badgeId;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeFile;

    /**
     * @var Badge|null The Badge for which this image is used as reward image
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="image", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $badgeImage;

    /**
     * @var int|null The badge id associated with the image.
     * @Groups({"write","read"})
     */
    private $badgeImageId;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeImageFile;

    /**
     * @var Badge|null The Badge for which this image is used as reward image light
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="imageLight", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $badgeImageLight;

    /**
     * @var int|null The badge id associated with the image light.
     * @Groups({"write","read"})
     */
    private $badgeImageLightId;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeImageLightFile;
    
    /**
     * @var File|null
     * @Vich\UploadableField(mapping="relayPointType", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $relayPointTypeFile;
    
    /**
     * @var int|null The relay point type id associated with the image.
     * @Groups({"read","write"})
     */
    private $relayPointTypeId;

    /**
     * @var Campaign|null The campaign associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\Campaign", inversedBy="images", cascade="persist")
     */
    private $campaign;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="campaign", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $campaignFile;
    
    /**
     * @var int|null The campaign id associated with the image.
     * @Groups({"read","write"})
     */
    private $campaignId;

    /**
     * @var Editorial|null The editorial associated with the image.
     *
     * @ORM\ManyToOne(targetEntity="\App\Editorial\Entity\Editorial", inversedBy="images", cascade="persist")
     */
    private $editorial;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="editorial", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $editorialFile;
    
    /**
     * @var int|null The editorial id associated with the image.
     * @Groups({"read","write"})
     */
    private $editorialId;

    /**
     * @var array|null The versions of with the image.
     * @Groups({"read","readCommunity","readRelayPoint","readCommunityUser","readEvent","readUser","results","communities","listCommunities"})
     */
    private $versions;

    /**
     * @var string|null The default image
     * @Groups({"aRead"})
     */
    private $image;

    /**
     * @var string|null The default avatar
     * @Groups({"aRead","readPublicProfile"})
     */
    private $avatar;
        
    /**
     * @var string|null The full url of the image. Used in specific situation (need a Listener)
     * @Groups({"readEditorial"})
     */
    private $url;

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
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(?string $title)
    {
        $this->title = $title;
    }
    
    public function getAlt(): ?string
    {
        return $this->alt;
    }
    
    public function setAlt(?string $alt)
    {
        $this->alt = $alt;
    }
    
    public function getCropX1(): ?int
    {
        return $this->cropX1;
    }
    
    public function setCropX1(?int $cropX1): self
    {
        $this->cropX1 = $cropX1;
        
        return $this;
    }
    
    public function getCropY1(): ?int
    {
        return $this->cropY1;
    }
    
    public function setCropY1(?int $cropY1): self
    {
        $this->cropY1 = $cropY1;
        
        return $this;
    }
    
    public function getCropX2(): ?int
    {
        return $this->cropX2;
    }
    
    public function setCropX2(?int $cropX2): self
    {
        $this->cropX2 = $cropX2;
        
        return $this;
    }
    
    public function getCropY2(): ?int
    {
        return $this->cropY2;
    }
    
    public function setCropY2(?int $cropY2): self
    {
        $this->cropY2 = $cropY2;
        
        return $this;
    }
    
    public function getFileName(): ?string
    {
        return $this->fileName;
    }
    
    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }
    
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }
    
    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;
    }
    
    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }
    
    public function setDimensions(?array $dimensions)
    {
        $this->dimensions = $dimensions;
        $this->setWidth($this->getDimensions()[0]);
        $this->setHeight($this->getDimensions()[1]);
    }
    
    public function getWidth(): ?int
    {
        return $this->width;
    }
    
    public function setWidth(?int $width): self
    {
        $this->width = $width;
        
        return $this;
    }
    
    public function getHeight(): ?int
    {
        return $this->height;
    }
    
    public function setHeight(?int $height): self
    {
        $this->height = $height;
        
        return $this;
    }
    
    public function getSize(): ?int
    {
        return $this->size;
    }
    
    public function setSize(?int $size): self
    {
        $this->size = $size;
        
        return $this;
    }
    
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
    
    public function setMimeType(?string $mimeType)
    {
        $this->mimeType = $mimeType;
    }
    
    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(?int $position): self
    {
        $this->position = $position;
        
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
    
    public function getEvent(): ?Event
    {
        return $this->event;
    }
    
    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        
        return $this;
    }
    
    public function getEventFile(): ?File
    {
        return $this->eventFile;
    }
    
    public function setEventFile(?File $eventFile)
    {
        $this->eventFile = $eventFile;
    }
    
    public function getEventId(): ?int
    {
        return $this->eventId;
    }
    
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
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
    
    public function getCommunityFile(): ?File
    {
        return $this->communityFile;
    }
    
    public function setCommunityFile(?File $communityFile)
    {
        $this->communityFile = $communityFile;
    }
    
    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }
    
    public function setCommunityId($communityId)
    {
        $this->communityId = $communityId;
    }
    
    public function getUserFile(): ?File
    {
        return $this->userFile;
    }
    
    public function setUserFile(?File $userFile)
    {
        $this->userFile = $userFile;
    }
    
    public function getUserId(): ?int
    {
        return $this->userId;
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
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getRelayPoint(): ?RelayPoint
    {
        return $this->relayPoint;
    }
    
    public function setRelayPoint(?RelayPoint $relayPoint): self
    {
        $this->relayPoint = $relayPoint;
        
        return $this;
    }
    
    public function getRelayPointFile(): ?File
    {
        return $this->relayPointFile;
    }
    
    public function setRelayPointFile(?File $relayPointFile)
    {
        $this->relayPointFile = $relayPointFile;
    }
    
    public function getRelayPointId(): ?int
    {
        return $this->relayPointId;
    }
    
    public function setRelayPointId($relayPointId)
    {
        $this->relayPointId = $relayPointId;
    }

    public function getRelayPointType(): ?RelayPointType
    {
        return $this->relayPointType;
    }
    
    public function setRelayPointType(?RelayPointType $relayPointType): self
    {
        $this->relayPointType = $relayPointType;
        
        return $this;
    }

    public function getBadgeId(): ?int
    {
        return $this->badgeId;
    }
    
    public function setBadgeId(?int $badgeId): self
    {
        $this->badgeId = $badgeId;
        
        return $this;
    }
    
    public function getBadge(): ?Badge
    {
        return $this->badge;
    }
    
    public function setBadge(?Badge $badge): self
    {
        $this->badge = $badge;
        
        return $this;
    }

    public function getBadgeFile(): ?File
    {
        return $this->badgeFile;
    }
    
    public function setBadgeFile(?File $badgeFile)
    {
        $this->badgeFile = $badgeFile;
    }

    public function getBadgeImageId(): ?int
    {
        return $this->badgeImageId;
    }
    
    public function setBadgeImageId(?int $badgeImageId): self
    {
        $this->badgeImageId = $badgeImageId;
        
        return $this;
    }

    public function getBadgeImage(): ?Badge
    {
        return $this->badgeImage;
    }
    
    public function setBadgeImage(?Badge $badgeImage): self
    {
        $this->badgeImage = $badgeImage;
        
        return $this;
    }

    public function getBadgeImageFile(): ?File
    {
        return $this->badgeImageFile;
    }
    
    public function setBadgeImageFile(?File $badgeImageFile)
    {
        $this->badgeImageFile = $badgeImageFile;
    }

    public function getBadgeImageLight(): ?Badge
    {
        return $this->badgeImageLight;
    }
    
    public function setBadgeImageLight(?Badge $badgeImageLight): self
    {
        $this->badgeImageLight = $badgeImageLight;
        
        return $this;
    }

    public function getBadgeImageLightId(): ?int
    {
        return $this->badgeImageLightId;
    }
    
    public function setBadgeImageLightId(?int $badgeImageLightId): self
    {
        $this->badgeImageLightId = $badgeImageLightId;
        
        return $this;
    }

    public function getBadgeImageLightFile(): ?File
    {
        return $this->badgeImageLightFile;
    }
    
    public function setBadgeImageLightFile(?File $badgeImageLightFile)
    {
        $this->badgeImageLightFile = $badgeImageLightFile;
    }

    public function getRelayPointTypeFile(): ?File
    {
        return $this->relayPointTypeFile;
    }
    
    public function setRelayPointTypeFile(?File $relayPointTypeFile)
    {
        $this->relayPointTypeFile = $relayPointTypeFile;
    }
    
    public function getRelayPointTypeId(): ?int
    {
        return $this->relayPointTypeId;
    }
    
    public function setRelayPointTypeId($relayPointTypeId)
    {
        $this->relayPointTypeId = $relayPointTypeId;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }
    
    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;
        
        return $this;
    }
    
    public function getCampaignFile(): ?File
    {
        return $this->campaignFile;
    }
    
    public function setCampaignFile(?File $campaignFile)
    {
        $this->campaignFile = $campaignFile;
    }
    
    public function getCampaignId(): ?int
    {
        return $this->campaignId;
    }
    
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function getEditorial(): ?Editorial
    {
        return $this->editorial;
    }
    
    public function setEditorial(?Editorial $editorial): self
    {
        $this->editorial = $editorial;
        
        return $this;
    }
    
    public function getEditorialFile(): ?File
    {
        return $this->editorialFile;
    }
    
    public function setEditorialFile(?File $editorialFile)
    {
        $this->editorialFile = $editorialFile;
    }
    
    public function getEditorialId(): ?int
    {
        return $this->editorialId;
    }
    
    public function setEditorialId($editorialId)
    {
        $this->editorialId = $editorialId;
    }
    
    public function getVersions(): ?array
    {
        return $this->versions;
    }
    
    public function setVersions(?array $versions)
    {
        $this->versions = $versions;
    }

    public function getImage(): ?string
    {
        if (isset($this->getVersions()['square_800'])) {
            return $this->getVersions()['square_800'];
        }
        return null;
    }

    public function getAvatar(): ?string
    {
        if (isset($this->getVersions()['square_250'])) {
            return $this->getVersions()['square_250'];
        }
        return null;
    }
    
    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function preventSerialization()
    {
        $this->setEventFile(null);
        $this->setUserFile(null);
        $this->setCommunityFile(null);
        $this->setRelayPointFile(null);
        $this->setRelayPointTypeFile(null);
        $this->setCampaignFile(null);
        $this->setBadgeFile(null);
        $this->setBadgeImageFile(null);
        $this->setBadgeImageLightFile(null);
        $this->setEditorialFile(null);
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
