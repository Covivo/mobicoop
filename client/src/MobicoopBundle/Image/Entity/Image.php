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

namespace Mobicoop\Bundle\MobicoopBundle\Image\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Editorial\Entity\Editorial;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPoint;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPointType;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * An image.
 */
class Image implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this image.
     *
     */
    private $id;
    
    /**
     * @var string|null The iri of this event.
     *
     * @Groups({"post","put"})
     */
    private $iri;
    
    /**
     * @var string The name of the image.
     *
     * @Groups({"post","put"})
     */
    private $name;

    /**
     * @var string The html title of the image.
     *
     * @Groups({"post","put"})
     */
    private $title;
    
    /**
     * @var string The html alt of the image.
     *
     * @Groups({"post","put"})
     */
    private $alt;
    
    /**
     * @var int The left coordinate of the crop, in percentage of the full width.
     *
     * @Groups({"post","put"})
     */
    private $cropX1;

    /**
     * @var int The top coordinate of the crop, in percent of the full height.
     *
     * @Groups({"post","put"})
     */
    private $cropY1;
    
    /**
     * @var int The right coordinate of the crop, in percentage of the full width.
     *
     * @Groups({"post","put"})
     */
    private $cropX2;
    
    /**
     * @var int The bottom coordinate of the crop, in percent of the full height.
     *
     * @Groups({"post","put"})
     */
    private $cropY2;
    
    /**
     * @var string The final file name of the image.
     *
     * @Groups({"post","put"})
     */
    private $fileName;
    
    /**
     * @var string The original file name of the image.
     *
     * @Groups({"post","put"})
     */
    private $originalName;
    
    /**
    * @var int The width of the image in pixels.
    */
    private $width;
    
    /**
     * @var int The height of the image in pixels.
     */
    private $height;
    
    /**
     * @var int The size in bytes of the image.
     */
    private $size;
    
    /**
     * @var string The mime type of the image.
     */
    private $mimeType;
    
    /**
     * @var int The position of the image if mulitple images are related to the same entity.
     *
     * @Groups({"post","put"})
     */
    private $position;
    
    /**
     * @var Event|null The event associated with the image.
     */
    private $event;

    /**
     * @var Community|null The community associated with the image.
     */
    private $community;
    
    /**
    * @var User|null The user associated with the image.
    */
    private $user;

    /**
     * @var RelayPoint |null The relay point associated with the image.
     */
    private $relayPoint;

    /**
     * @var RelayPointType |null The relay point type associated with the image.
     */
    private $relayPointType;
    
    /**
     * @var Editorial |null The Editorial associated with the image.
     */
    private $editorial;

    /**
     * @var array|null The versions of with the image.
     *
     */
    private $versions;
    
    /**
     * @var File|null
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 4000,
     *     minHeight = 200,
     *     maxHeight = 4000
     * )
     * @Groups({"post","put"})
     */
    private $eventFile;

    /**
     * @var File|null
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 4000,
     *     minHeight = 200,
     *     maxHeight = 4000
     * )
     * @Groups({"post","put"})
     */
    private $communityFile;
    
    /**
    * @var File|null
    * @Assert\Image(
    *     minWidth = 200,
    *     maxWidth = 4000,
    *     minHeight = 200,
    *     maxHeight = 4000
    * )
    * @Groups({"post","put"})
    */
    private $userFile;

    /**
     * @var File|null
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 4000,
     *     minHeight = 200,
     *     maxHeight = 4000
     * )
     * @Groups({"post","put"})
     */
    private $relayPointFile;

    /**
     * @var File|null
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 4000,
     *     minHeight = 200,
     *     maxHeight = 4000
     * )
     * @Groups({"post","put"})
     */
    private $relayPointTypeFile;
    
    /**
     * @var int|null The event id associated with the image.
     * @Groups({"post","put"})
     */
    private $eventId;

    /**
     * @var int|null The community id associated with the image.
     * @Groups({"post","put"})
     */
    private $communityId;

    /**
    * @var int|null The user id associated with the image.
    * @Groups({"post","put"})
    */
    private $userId;

    /**
     * @var int|null The relay point id associated with the image.
     * @Groups({"post","put"})
     */
    private $relayPointId;

    /**
     * @var int|null The relay point type id associated with the image.
     * @Groups({"post","put"})
     */
    private $relayPointTypeId;

    /**
     * @var string|null The full url of the image. Used in specific situation (need a Listener)
     */
    private $url;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/images/".$id);
        }
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
        return $this->cropX1;
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
    
    public function getEvent(): ?Event
    {
        return $this->event;
    }
    
    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        
        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
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

    public function getRelayPointType(): ?RelayPointType
    {
        return $this->relayPointType;
    }
    
    public function setRelayPointType(?RelayPointType $relayPointType): self
    {
        $this->relayPointType = $relayPointType;
        
        return $this;
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

    public function getVersions(): ?array
    {
        return $this->versions;
    }
    
    public function setVersions(?array $versions)
    {
        $this->versions = $versions;
    }
    
    public function getEventFile(): ?File
    {
        return $this->eventFile;
    }
    
    public function setEventFile(?File $eventFile)
    {
        $this->eventFile = $eventFile;
    }

    public function getCommunityFile(): ?File
    {
        return $this->communityFile;
    }
    
    public function setCommunityFile(?File $communityFile)
    {
        $this->communityFile = $communityFile;
    }

    public function getRelayPointFile(): ?File
    {
        return $this->relayPointFile;
    }
    
    public function setRelayPointFile(?File $relayPointFile)
    {
        $this->relayPointFile = $relayPointFile;
    }

    public function getRelayPointTypeFile(): ?File
    {
        return $this->relayPointTypeFile;
    }
    
    public function setRelayPointTypeFile(?File $relayPointTypeFile)
    {
        $this->relayPointTypeFile = $relayPointTypeFile;
    }

    public function getUserFile(): ?File
    {
        return $this->userFile;
    }
    
    public function setUserFile(?File $userFile)
    {
        $this->userFile = $userFile;
    }
    
    public function getEventId(): ?int
    {
        return $this->eventId;
    }
    
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }
    
    public function setCommunityId($communityId)
    {
        $this->communityId = $communityId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getRelayPointId(): ?int
    {
        return $this->relayPointId;
    }
    
    public function setRelayPointId($relayPointId)
    {
        $this->relayPointId = $relayPointId;
    }

    public function getRelayPointTypeId(): ?int
    {
        return $this->relayPointTypeId;
    }
    
    public function setRelayPointTypeId($relayPointTypeId)
    {
        $this->relayPointTypeId = $relayPointTypeId;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(?string $url)
    {
        $this->url = $url;
    }

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'iri'               => $this->getIri(),
            'name'              => $this->getName(),
            'fileName'          => $this->getFileName(),
            'versions'          => $this->getVersions(),
            'url'               => $this->getUrl()
        ];
    }
}
