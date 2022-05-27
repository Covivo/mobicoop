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
 */

namespace App\Image\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Community\Entity\Community;
use App\Editorial\Entity\Editorial;
use App\Event\Entity\Event;
use App\Gamification\Entity\Badge;
use App\Image\Admin\Controller\PostImageAction;
use App\Image\Controller\CreateImageAction;
use App\Image\Controller\ImageRemoveFileless;
use App\MassCommunication\Entity\Campaign;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Entity\RelayPointType;
use App\User\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

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
 *                  "tags"={"Maintenance"}
 *             }
 *          },
 *          "removeFileless"={
 *              "method"="POST",
 *              "deserialize"=false,
 *              "serialize"=false,
 *              "write"=false,
 *              "controller"=ImageRemoveFileless::class,
 *              "path"="/images/removefileless",
 *              "security_post_denormalize"="is_granted('maintenance',object)",
 *             "swagger_context" = {
 *                  "tags"={"Maintenance"}
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
     * @var int the id of this image
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string the name of the image
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     */
    private $name;

    /**
     * @var string the html title of the image
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","communities","listCommunities","readRelayPoint","readEditorial"})
     */
    private $title;

    /**
     * @var string the html alt of the image
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("read")
     */
    private $alt;

    /**
     * @var int the left coordinate of the crop, in percentage of the full width
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropX1;

    /**
     * @var int the top coordinate of the crop, in percent of the full height
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropY1;

    /**
     * @var int the right coordinate of the crop, in percentage of the full width
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropX2;

    /**
     * @var int the bottom coordinate of the crop, in percent of the full height
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $cropY2;

    /**
     * @var string the final file name of the image
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","results","write","readUser","readRelayPoint","readEditorial"})
     */
    private $fileName;

    /**
     * @var string the original file name of the image
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $originalName;

    /**
     * @var array the original dimensions of the image
     */
    private $dimensions;

    /**
     * @var int the width of the image in pixels
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $width;

    /**
     * @var int the height of the image in pixels
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $height;

    /**
     * @var int the size in bytes of the image
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $size;

    /**
     * @var string the mime type of the image
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $mimeType;

    /**
     * @var int the position of the image if mulitple images are related to the same entity
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","aWrite"})
     */
    private $position;

    /**
     * @var \DateTimeInterface creation date of the image
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the image
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var null|Event the event associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\Event\Entity\Event", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $event;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="event", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $eventFile;

    /**
     * @var null|int the event id associated with the image
     * @Groups({"read","write"})
     */
    private $eventId;

    /**
     * @var null|Community the community associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $community;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="community", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $communityFile;

    /**
     * @var null|int the community id associated with the image
     * @Groups({"read","write"})
     */
    private $communityId;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="user", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $userFile;

    /**
     * @var null|User the user associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @var null|int the user id associated with the image
     * @Groups({"write","results"})
     */
    private $userId;

    /**
     * @var null|RelayPoint the relay point associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\RelayPoint\Entity\RelayPoint", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $relayPoint;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="relayPoint", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $relayPointFile;

    /**
     * @var null|int the relay point id associated with the image
     * @Groups({"read","write"})
     */
    private $relayPointId;

    /**
     * @var null|RelayPointType the relay point type associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\RelayPoint\Entity\RelayPointType", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $relayPointType;

    /**
     * @var null|Badge The Badge for which this image is used as icon
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="icon", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $badgeIcon;

    /**
     * @var null|int the badge id associated with the image (icon)
     * @Groups({"write","read"})
     */
    private $badgeIconId;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeIconFile;

    /**
     * @var null|Badge The Badge for which this image is used as icon
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="decoratedIcon", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $badgeDecoratedIcon;

    /**
     * @var null|int the badge id associated with the image (decorated icon)
     * @Groups({"write","read"})
     */
    private $badgeDecoratedIconId;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeDecoratedIconFile;

    /**
     * @var null|Badge The Badge for which this image is used as reward image
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="image", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $badgeImage;

    /**
     * @var null|int the badge id associated with the image
     * @Groups({"write","read"})
     */
    private $badgeImageId;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeImageFile;

    /**
     * @var null|Badge The Badge for which this image is used as reward image light
     *
     * @ORM\OneToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="imageLight", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $badgeImageLight;

    /**
     * @var null|int the badge id associated with the image light
     * @Groups({"write","read"})
     */
    private $badgeImageLightId;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="badge", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $badgeImageLightFile;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="relayPointType", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $relayPointTypeFile;

    /**
     * @var null|int the relay point type id associated with the image
     * @Groups({"read","write"})
     */
    private $relayPointTypeId;

    /**
     * @var null|Campaign the campaign associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\Campaign", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="campaign", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $campaignFile;

    /**
     * @var null|int the campaign id associated with the image
     * @Groups({"read","write"})
     */
    private $campaignId;

    /**
     * @var null|Editorial the editorial associated with the image
     *
     * @ORM\ManyToOne(targetEntity="\App\Editorial\Entity\Editorial", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $editorial;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="editorial", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType", dimensions="dimensions")
     */
    private $editorialFile;

    /**
     * @var null|int the editorial id associated with the image
     * @Groups({"read","write"})
     */
    private $editorialId;

    /**
     * @var null|array the versions of with the image
     * @Groups({"read","readCommunity","readRelayPoint","readCommunityUser","readEvent","readUser","results","communities","listCommunities"})
     */
    private $versions;

    /**
     * @var null|string The default image
     * @Groups({"aRead"})
     */
    private $image;

    /**
     * @var null|string The default avatar
     * @Groups({"aRead","readPublicProfile"})
     */
    private $avatar;

    /**
     * @var null|string The full url of the image. Used in specific situation (need a Listener)
     * @Groups({"readEditorial"})
     */
    private $url;

    public function __construct($id = null)
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

    public function getBadgeIconId(): ?int
    {
        return $this->badgeIconId;
    }

    public function setBadgeIconId(?int $badgeIconId): self
    {
        $this->badgeIconId = $badgeIconId;

        return $this;
    }

    public function getBadgeIcon(): ?Badge
    {
        return $this->badgeIcon;
    }

    public function setBadgeIcon(?Badge $badgeIcon): self
    {
        $this->badgeIcon = $badgeIcon;

        return $this;
    }

    public function getBadgeIconFile(): ?File
    {
        return $this->badgeIconFile;
    }

    public function setBadgeIconFile(?File $badgeIconFile)
    {
        $this->badgeIconFile = $badgeIconFile;
    }

    public function getBadgeDecoratedIconId(): ?int
    {
        return $this->badgeDecoratedIconId;
    }

    public function setBadgeDecoratedIconId(?int $badgeDecoratedIconId): self
    {
        $this->badgeDecoratedIconId = $badgeDecoratedIconId;

        return $this;
    }

    public function getBadgeDecoratedIcon(): ?Badge
    {
        return $this->badgeDecoratedIcon;
    }

    public function setBadgeDecoratedIcon(?Badge $badgeDecoratedIcon): self
    {
        $this->badgeDecoratedIcon = $badgeDecoratedIcon;

        return $this;
    }

    public function getBadgeDecoratedIconFile(): ?File
    {
        return $this->badgeDecoratedIconFile;
    }

    public function setBadgeDecoratedIconFile(?File $badgeDecoratedIconFile)
    {
        $this->badgeDecoratedIconFile = $badgeDecoratedIconFile;
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
        $this->setBadgeIconFile(null);
        $this->setBadgeDecoratedIconFile(null);
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
